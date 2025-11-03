<?php
session_start();
require_once 'config.php';
$conn = getConnection();

$sid = $_SESSION['id'];

/* ---------------- Enroll in Course ---------------- */
if (isset($_POST['enroll_course'])) {
    $course_id = intval($_POST['course_id']);
    
    // Check if already enrolled
    $check_enrollment = $conn->query("SELECT * FROM enrollments WHERE student_id = $sid AND course_id = $course_id");
    
    if ($check_enrollment->num_rows > 0) {
        echo "<script>showAlert('You are already enrolled in this course.', 'error');</script>";
    } else {
        // Check if course is active
        $course_check = $conn->query("SELECT * FROM course WHERE cid = $course_id AND status = 'active'");
        
        if ($course_check->num_rows > 0) {
            $insert_sql = "INSERT INTO enrollments (student_id, course_id, enrollment_date, status, progress) 
                           VALUES ($sid, $course_id, NOW(), 'enrolled', 0.00)";
            
            if ($conn->query($insert_sql)) {
                echo "<script>showAlert('Successfully enrolled in the course!', 'success');</script>";
            } else {
                echo "<script>showAlert('Error enrolling in course: " . $conn->error . "', 'error');</script>";
            }
        } else {
            echo "<script>showAlert('Course not available for enrollment.', 'error');</script>";
        }
    }
}

/* ---------------- Unenroll from Course ---------------- */
if (isset($_POST['unenroll_course'])) {
    $course_id = intval($_POST['course_id']);
    
    $delete_sql = "DELETE FROM enrollments WHERE student_id = $sid AND course_id = $course_id";
    
    if ($conn->query($delete_sql)) {
        echo "<script>showAlert('Successfully unenrolled from the course.', 'success');</script>";
    } else {
        echo "<script>showAlert('Error unenrolling from course: " . $conn->error . "', 'error');</script>";
    }
}

/* ---------------- Fetch available courses ---------------- */
$available_courses_query = "
    SELECT c.*, td.name as teacher_name, td.specialization
    FROM course c
    LEFT JOIN teacher_details td ON c.cid = td.cid
    WHERE c.status = 'active'
    ORDER BY c.course_name
";
$available_courses = $conn->query($available_courses_query);

/* ---------------- Fetch enrolled courses ---------------- */
$enrolled_courses_query = "
    SELECT c.*, e.enrollment_date, e.status as enrollment_status, e.progress, td.name as teacher_name
    FROM enrollments e
    JOIN course c ON e.course_id = c.cid
    LEFT JOIN teacher_details td ON c.cid = td.cid
    WHERE e.student_id = $sid
    ORDER BY e.enrollment_date DESC
";
$enrolled_courses = $conn->query($enrolled_courses_query);

/* ---------------- Get enrolled course IDs for filtering ---------------- */
$enrolled_course_ids = [];
while ($enrolled = $enrolled_courses->fetch_assoc()) {
    $enrolled_course_ids[] = $enrolled['cid'];
}

// Reset pointer
$enrolled_courses->data_seek(0);
?>

<div class="container">
    <h1>Course Enrollment</h1>
    
    <!-- Enrolled Courses -->
    <div class="enrolled-section">
        <h2>My Enrolled Courses</h2>
        <?php if ($enrolled_courses->num_rows > 0): ?>
            <div class="courses-grid">
                <?php while ($course = $enrolled_courses->fetch_assoc()): ?>
                    <div class="course-card enrolled">
                        <div class="course-image">
                            <?php if (!empty($course['filepath'])): ?>
                                <img src="<?php echo $course['filepath']; ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            <div class="course-status">
                                <span class="status-badge enrolled">Enrolled</span>
                            </div>
                        </div>
                        
                        <div class="course-content">
                            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            
                            <?php if ($course['teacher_name']): ?>
                                <p class="teacher"><i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($course['teacher_name']); ?></p>
                            <?php endif; ?>
                            
                            <?php if ($course['description']): ?>
                                <p class="description"><?php echo htmlspecialchars(substr($course['description'], 0, 100)); ?>
                                   <?php echo strlen($course['description']) > 100 ? '...' : ''; ?></p>
                            <?php endif; ?>
                            
                            <div class="course-info">
                                <p><strong>Enrolled:</strong> <?php echo date('M d, Y', strtotime($course['enrollment_date'])); ?></p>
                                <p><strong>Progress:</strong> <?php echo number_format($course['progress'], 1); ?>%</p>
                            </div>
                            
                            <div class="course-actions">
                                <a href="student.php?page=my_learning_new.php" class="btn btn-primary">
                                    <i class="fas fa-play"></i> Continue Learning
                                </a>
                                <form method="post" style="display: inline;" onsubmit="return confirmUnenroll()">
                                    <input type="hidden" name="course_id" value="<?php echo $course['cid']; ?>">
                                    <button type="submit" name="unenroll_course" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times"></i> Unenroll
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-enrollments">
                <i class="fas fa-graduation-cap" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>No Enrolled Courses</h3>
                <p>You haven't enrolled in any courses yet. Browse available courses below to get started!</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Available Courses -->
    <div class="available-section">
        <h2>Available Courses</h2>
        <?php if ($available_courses->num_rows > 0): ?>
            <div class="courses-grid">
                <?php 
                $available_courses->data_seek(0);
                while ($course = $available_courses->fetch_assoc()): 
                    $is_enrolled = in_array($course['cid'], $enrolled_course_ids);
                ?>
                    <div class="course-card available <?php echo $is_enrolled ? 'enrolled' : ''; ?>">
                        <div class="course-image">
                            <?php if (!empty($course['filepath'])): ?>
                                <img src="<?php echo $course['filepath']; ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>">
                            <?php else: ?>
                                <div class="no-image">
                                    <i class="fas fa-book"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="course-status">
                                <?php if ($is_enrolled): ?>
                                    <span class="status-badge enrolled">Already Enrolled</span>
                                <?php else: ?>
                                    <span class="status-badge available">Available</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="course-content">
                            <h3><?php echo htmlspecialchars($course['course_name']); ?></h3>
                            
                            <?php if ($course['teacher_name']): ?>
                                <p class="teacher"><i class="fas fa-chalkboard-teacher"></i> <?php echo htmlspecialchars($course['teacher_name']); ?></p>
                                <?php if ($course['specialization']): ?>
                                    <p class="specialization"><i class="fas fa-tag"></i> <?php echo htmlspecialchars($course['specialization']); ?></p>
                                <?php endif; ?>
                            <?php endif; ?>
                            
                            <?php if ($course['description']): ?>
                                <p class="description"><?php echo htmlspecialchars(substr($course['description'], 0, 150)); ?>
                                   <?php echo strlen($course['description']) > 150 ? '...' : ''; ?></p>
                            <?php endif; ?>
                            
                            <div class="course-info">
                                <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($course['created_date'])); ?></p>
                            </div>
                            
                            <div class="course-actions">
                                <?php if ($is_enrolled): ?>
                                    <a href="student.php?page=my_learning_new.php" class="btn btn-primary">
                                        <i class="fas fa-play"></i> Continue Learning
                                    </a>
                                <?php else: ?>
                                    <form method="post" style="display: inline;">
                                        <input type="hidden" name="course_id" value="<?php echo $course['cid']; ?>">
                                        <button type="submit" name="enroll_course" class="btn btn-success">
                                            <i class="fas fa-plus"></i> Enroll Now
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-courses">
                <i class="fas fa-book-open" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
                <h3>No Courses Available</h3>
                <p>There are currently no courses available for enrollment. Please check back later.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .container {
        max-width: 1400px;
        margin: 2rem auto;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 2rem;
    }
    
    .enrolled-section, .available-section {
        margin-bottom: 3rem;
    }
    
    .courses-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
        margin-top: 1.5rem;
    }
    
    .course-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        position: relative;
    }
    
    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.15);
    }
    
    .course-card.enrolled {
        border-color: #10b981;
        background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
    }
    
    .course-image {
        position:relative ;
        height: 200px;
        overflow: hidden;
    }
    
    .course-image img {
        padding-left:100px ;
        width: auto;
        height: 200px;
        object-fit: cover;
        transition: transform 0.3s;
    }
    
    .course-card:hover .course-image img {
        transform: scale(1.05);
    }
    
    .no-image {
        height: 100%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 3rem;
    }
    
    .course-status {
        position: absolute;
        top: 1rem;
        right: 1rem;
    }
    
    .status-badge {
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge.available {
        background: #dbeafe;
        color: #1e40af;
    }
    
    .status-badge.enrolled {
        background: #dcfce7;
        color: #166534;
    }
    
    .course-content {
        padding: 1.5rem;
    }
    
    .course-content h3 {
        margin: 0 0 1rem 0;
        color: #1f2937;
        font-size: 1.25rem;
        font-weight: 600;
    }
    
    .teacher, .specialization {
        margin: 0.5rem 0;
        color: #6b7280;
        font-size: 0.875rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .description {
        color: #6b7280;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    .course-info {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1.5rem;
    }
    
    .course-info p {
        margin: 0.25rem 0;
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .course-actions {
        display: flex;
        gap: 0.75rem;
        justify-content: space-between;
        align-items: center;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
        text-align: center;
    }
    
    .btn-sm {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2563eb;
        color: white;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-success:hover {
        background: #059669;
        color: white;
    }
    
    .btn-danger {
        background: #ef4444;
        color: white;
    }
    
    .btn-danger:hover {
        background: #dc2626;
        color: white;
    }
    
    .no-enrollments, .no-courses {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
    }
    
    .no-enrollments h3, .no-courses h3 {
        margin: 1rem 0;
        color: #374151;
    }
    
    @media (max-width: 768px) {
        .container {
            margin: 1rem;
            padding: 1rem;
        }
        
        .courses-grid {
            grid-template-columns: 1fr;
        }
        
        .course-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<script>
function confirmUnenroll() {
    return confirm('Are you sure you want to unenroll from this course? You will lose access to all course materials and assignments.');
}

function showAlert(message, type = 'info') {
    const alertDiv = document.createElement('div');
    alertDiv.textContent = message;
    alertDiv.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 6px;
        color: white;
        z-index: 1000;
        font-weight: 500;
    `;
    
    const colors = {
        success: '#10b981',
        error: '#ef4444',
        warning: '#f59e0b',
        info: '#3b82f6'
    };
    
    alertDiv.style.backgroundColor = colors[type] || colors.info;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        alertDiv.remove();
    }, 3000);
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
