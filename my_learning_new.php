<?php
session_start();
require_once 'config.php';
$sid = $_SESSION['id'];
$conn = getConnection();

// Handle course enrollment
if (isset($_POST['enroll_course'])) {
    $course_id = intval($_POST['course_id']);
    
    // Check if already enrolled
    $check_enrollment = $conn->query("SELECT * FROM enrollments WHERE student_id = $sid AND course_id = $course_id");
    
    if ($check_enrollment->num_rows == 0) {
        $enroll_sql = "INSERT INTO enrollments (student_id, course_id) VALUES ($sid, $course_id)";
        if ($conn->query($enroll_sql)) {
            echo "<script>showAlert('Successfully enrolled in course!', 'success');</script>";
        } else {
            echo "<script>showAlert('Error enrolling in course: " . $conn->error . "', 'error');</script>";
        }
    } else {
        echo "<script>showAlert('You are already enrolled in this course!', 'warning');</script>";
    }
}

// Get enrolled courses with teacher info
$enrolled_courses = $conn->query("
    SELECT c.*, e.enrollment_date, e.progress, e.status as enrollment_status, 
           td.name as teacher_name, td.specialization
    FROM course c 
    JOIN enrollments e ON c.cid = e.course_id 
    LEFT JOIN teacher_details td ON c.cid = td.cid
    WHERE e.student_id = $sid 
    ORDER BY e.enrollment_date DESC
");


$view = isset($_GET['view']) ? $_GET['view'] : 'enrolled';
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 style="color: #2c3e50;">📚 My Learning</h1>
            <p style="color: #7f8c8d;">Track your progress and manage your courses.</p>
        </div>
    </div>

  
    <?php if ($view == 'enrolled'): ?>
        <!-- Enrolled Courses -->
        <div class="course-grid">
            <?php if ($enrolled_courses->num_rows > 0): ?>
                <?php while ($course = $enrolled_courses->fetch_assoc()): ?>
                    <div class="course-card">
                        <?php if ($course['filepath']): ?>
                            <img src="<?php echo $course['filepath']; ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-book" style="font-size: 3rem; color: #bdc3c7;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4><?php echo htmlspecialchars($course['course_name']); ?></h4>
                        
                        <?php if ($course['teacher_name']): ?>
                            <p class="teacher-info">
                                <i class="fas fa-chalkboard-teacher"></i> 
                                <?php echo htmlspecialchars($course['teacher_name']); ?>
                                <?php if ($course['specialization']): ?>
                                    <small>(<?php echo htmlspecialchars($course['specialization']); ?>)</small>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        
                        <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?></p>
                        
                        <!-- Progress Bar -->
                        <div class="progress-container">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $course['progress']; ?>%"></div>
                            </div>
                            <span class="progress-text"><?php echo number_format($course['progress'], 1); ?>% Complete</span>
                        </div>
                        
                        <div class="course-meta">
                            <small class="text-muted">
                                Enrolled: <?php echo date('M j, Y', strtotime($course['enrollment_date'])); ?>
                            </small>
                            <span class="badge bg-<?php echo $course['enrollment_status'] == 'completed' ? 'success' : 'primary'; ?>">
                                <?php echo ucfirst($course['enrollment_status']); ?>
                            </span>
                        </div>
                        
                        <div class="course-actions">
                            <a href="course_view.php?course_id=<?php echo $course['cid']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-play"></i> Continue Learning
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-graduation-cap" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 1rem;"></i>
                        <h3 style="color: #7f8c8d;">No courses enrolled yet</h3>
                        <p style="color: #95a5a6;">Browse available courses to start learning!</p>
                        <a href="?page=my_learning_new.php&view=available" class="btn btn-primary">Browse Courses</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

    <?php else: // available courses ?>
        <!-- Available Courses -->
        <div class="course-grid">
            <?php if ($available_courses->num_rows > 0): ?>
                <?php while ($course = $available_courses->fetch_assoc()): ?>
                    <div class="course-card">
                        <?php if ($course['filepath']): ?>
                            <img src="<?php echo $course['filepath']; ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-book" style="font-size: 3rem; color: #bdc3c7;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <h4><?php echo htmlspecialchars($course['course_name']); ?></h4>
                        
                        <?php if ($course['teacher_name']): ?>
                            <p class="teacher-info">
                                <i class="fas fa-chalkboard-teacher"></i> 
                                <?php echo htmlspecialchars($course['teacher_name']); ?>
                                <?php if ($course['specialization']): ?>
                                    <small>(<?php echo htmlspecialchars($course['specialization']); ?>)</small>
                                <?php endif; ?>
                            </p>
                        <?php endif; ?>
                        
                        <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?></p>
                        
                        <div class="course-meta">
                            <small class="text-muted">
                                Created: <?php echo date('M j, Y', strtotime($course['created_date'])); ?>
                            </small>
                            <span class="badge bg-success">Available</span>
                        </div>
                        
                        <div class="course-actions">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="course_id" value="<?php echo $course['cid']; ?>">
                                <button type="submit" name="enroll_course" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Enroll Now
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle" style="font-size: 4rem; color: #27ae60; margin-bottom: 1rem;"></i>
                        <h3 style="color: #7f8c8d;">All caught up!</h3>
                        <p style="color: #95a5a6;">You're enrolled in all available courses.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.nav-tabs-container {
    display: flex;
    border-bottom: 2px solid #ecf0f1;
    margin-bottom: 2rem;
}

.nav-tab {
    padding: 1rem 2rem;
    text-decoration: none;
    color: #7f8c8d;
    border-bottom: 3px solid transparent;
    transition: all 0.3s;
}

.nav-tab:hover {
    color: #2c3e50;
    background-color: #f8f9fa;
}

.nav-tab.active {
    color: #3498db;
    border-bottom-color: #3498db;
    background-color: #f8f9fa;
}

.course-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 1.5rem;
}

.course-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.course-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
}

.course-card img {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.no-image {
    height: 150px;
    display: flex;
    align-items: center;
    justify-content: center;
    background-color: #f8f9fa;
    border-radius: 8px;
    margin-bottom: 1rem;
}

.course-card h4 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
}

.course-card p {
    color: #7f8c8d;
    font-size: 0.9rem;
    margin-bottom: 1rem;
}

.teacher-info {
    color: #3498db !important;
    font-size: 0.85rem !important;
    margin-bottom: 0.75rem !important;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.teacher-info i {
    color: #2980b9;
}

.teacher-info small {
    color: #7f8c8d;
    font-style: italic;
}

.progress-container {
    margin-bottom: 1rem;
}

.progress-bar {
    width: 100%;
    height: 8px;
    background-color: #ecf0f1;
    border-radius: 4px;
    overflow: hidden;
    margin-bottom: 0.5rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.8rem;
    color: #7f8c8d;
}

.course-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    padding-top: 0.5rem;
    border-top: 1px solid #ecf0f1;
}

.course-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.course-actions .btn {
    flex: 1;
    min-width: 120px;
}

.badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.375rem;
}

.bg-success { background-color: #27ae60 !important; color: white; }
.bg-primary { background-color: #3498db !important; color: white; }

.text-muted { color: #6c757d !important; }

.container-fluid {
    padding: 0 1rem;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.75rem;
}

.col-12 {
    flex: 0 0 100%;
    max-width: 100%;
    padding: 0 0.75rem;
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
}

.text-center {
    text-align: center !important;
}

.btn {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    font-size: 14px;
    transition: all 0.3s;
}

.btn-primary {
    background-color: #3498db;
    color: white;
}

.btn-primary:hover {
    background-color: #2980b9;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
}

@media (max-width: 768px) {
    .course-grid {
        grid-template-columns: 1fr;
    }
    
    .nav-tabs-container {
        flex-direction: column;
    }
    
    .nav-tab {
        text-align: center;
    }
    
    .course-actions .btn {
        width: 100%;
        margin-bottom: 0.25rem;
    }
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
