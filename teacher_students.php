<?php
session_start();
$tid = $_SESSION['id'];
$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get filter parameters
$course_filter = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

// Get teacher's courses for dropdown
$courses_result = $conn->query("SELECT * FROM course WHERE created_by = $tid ORDER BY course_name");

// Get students enrolled in teacher's courses
$students_sql = "
    SELECT DISTINCT sd.*, su.user_name, c.course_name, e.enrollment_date, e.status as enrollment_status, e.progress
    FROM student_details sd
    JOIN student_user su ON sd.sid = su.sid
    JOIN enrollments e ON sd.sid = e.student_id
    JOIN course c ON e.course_id = c.cid
    WHERE c.created_by = $tid
";

if ($course_filter > 0) {
    $students_sql .= " AND c.cid = $course_filter";
}

$students_sql .= " ORDER BY sd.name ASC";
$students_result = $conn->query($students_sql);

// Get course statistics
$course_stats = [];
$courses_result->data_seek(0); // Reset pointer
while ($course = $courses_result->fetch_assoc()) {
    $course_id = $course['cid'];
    $enrolled_count = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE course_id = $course_id")->fetch_assoc()['count'];
    $completed_count = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE course_id = $course_id AND status = 'completed'")->fetch_assoc()['count'];
    $avg_progress = $conn->query("SELECT AVG(progress) as avg FROM enrollments WHERE course_id = $course_id")->fetch_assoc()['avg'];
    
    $course_stats[$course_id] = [
        'enrolled' => $enrolled_count,
        'completed' => $completed_count,
        'avg_progress' => $avg_progress ?: 0
    ];
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 style="color: #2c3e50;">👥 My Students</h1>
            <p style="color: #7f8c8d;">Track student progress and manage enrollments.</p>
        </div>
    </div>

    <!-- Course Filter -->
    <div class="filter-container mb-4">
        <div class="d-flex align-items-center gap-3">
            <label for="course_filter" style="margin: 0; font-weight: 500;">Filter by Course:</label>
            <select id="course_filter" onchange="filterByCourse(this.value)" class="form-control" style="width: auto;">
                <option value="0">All Courses</option>
                <?php 
                $courses_result->data_seek(0); // Reset pointer
                while ($course = $courses_result->fetch_assoc()): 
                ?>
                    <option value="<?php echo $course['cid']; ?>" <?php echo $course_filter == $course['cid'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($course['course_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
    </div>

    <!-- Course Statistics -->
    <?php if (!empty($course_stats)): ?>
        <div class="stats-grid mb-4">
            <?php 
            $courses_result->data_seek(0); // Reset pointer
            while ($course = $courses_result->fetch_assoc()): 
                if ($course_filter > 0 && $course['cid'] != $course_filter) continue;
                $stats = $course_stats[$course['cid']];
            ?>
                <div class="stat-card">
                    <h4><?php echo htmlspecialchars($course['course_name']); ?></h4>
                    <div class="stat-row">
                        <span class="stat-label">Enrolled:</span>
                        <span class="stat-value"><?php echo $stats['enrolled']; ?> students</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Completed:</span>
                        <span class="stat-value"><?php echo $stats['completed']; ?> students</span>
                    </div>
                    <div class="stat-row">
                        <span class="stat-label">Avg Progress:</span>
                        <span class="stat-value"><?php echo number_format($stats['avg_progress'], 1); ?>%</span>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php endif; ?>

    <!-- Students Table -->
    <div class="table-container">
        <h3>📊 Student List</h3>
        <?php if ($students_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Course</th>
                            <th>Enrolled Date</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Contact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($student = $students_result->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <div class="student-info">
                                        <img src="<?php echo $student['profilepic'] ?: 'images/signup-image.jpg.png'; ?>" 
                                             alt="Profile" class="student-avatar">
                                        <div>
                                            <strong><?php echo htmlspecialchars($student['name']); ?></strong>
                                            <br><small class="text-muted">@<?php echo htmlspecialchars($student['user_name']); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($student['course_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($student['enrollment_date'])); ?></td>
                                <td>
                                    <div class="progress-container">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: <?php echo $student['progress']; ?>%"></div>
                                        </div>
                                        <span class="progress-text"><?php echo number_format($student['progress'], 1); ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $student['enrollment_status'] == 'completed' ? 'success' : 
                                            ($student['enrollment_status'] == 'dropped' ? 'danger' : 'primary'); 
                                    ?>">
                                        <?php echo ucfirst($student['enrollment_status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="contact-info">
                                        <?php if ($student['e_mail']): ?>
                                            <a href="mailto:<?php echo $student['e_mail']; ?>" class="contact-link">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($student['phone']): ?>
                                            <a href="tel:<?php echo $student['phone']; ?>" class="contact-link">
                                                <i class="fas fa-phone"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <button onclick="viewStudentDetails(<?php echo $student['sid']; ?>)" 
                                            class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View
                                    </button>
                                    <button onclick="messageStudent(<?php echo $student['sid']; ?>, '<?php echo htmlspecialchars($student['name']); ?>')" 
                                            class="btn btn-primary btn-sm">
                                        <i class="fas fa-message"></i> Message
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-users" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 1rem;"></i>
                <h3 style="color: #7f8c8d;">No students found</h3>
                <p style="color: #95a5a6;">
                    <?php if ($course_filter > 0): ?>
                        No students are enrolled in the selected course yet.
                    <?php else: ?>
                        No students are enrolled in your courses yet.
                    <?php endif; ?>
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Student Details Modal -->
<div id="studentModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Student Details</h3>
            <span class="close" onclick="closeStudentModal()">&times;</span>
        </div>
        <div class="modal-body" id="studentModalBody">
            <!-- Content will be loaded dynamically -->
        </div>
    </div>
</div>

<style>
.filter-container {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid #3498db;
}

.stat-card h4 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-size: 1rem;
}

.stat-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.5rem;
}

.stat-label {
    color: #7f8c8d;
    font-size: 0.9rem;
}

.stat-value {
    color: #2c3e50;
    font-weight: 500;
    font-size: 0.9rem;
}

.student-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.student-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ecf0f1;
}

.progress-container {
    min-width: 120px;
}

.progress-bar {
    width: 100%;
    height: 6px;
    background-color: #ecf0f1;
    border-radius: 3px;
    overflow: hidden;
    margin-bottom: 0.25rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #3498db, #2ecc71);
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.75rem;
    color: #7f8c8d;
}

.contact-info {
    display: flex;
    gap: 0.5rem;
}

.contact-link {
    color: #3498db;
    text-decoration: none;
    padding: 0.25rem;
    border-radius: 4px;
    transition: background-color 0.3s;
}

.contact-link:hover {
    background-color: #f8f9fa;
    color: #2980b9;
}

.table-responsive {
    overflow-x: auto;
}

.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.gap-3 {
    gap: 1rem;
}

.modal {
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: white;
    margin: 5% auto;
    padding: 0;
    border-radius: 8px;
    width: 80%;
    max-width: 600px;
    max-height: 80vh;
    overflow-y: auto;
}

.modal-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #ecf0f1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 1.5rem;
}

.close {
    font-size: 1.5rem;
    cursor: pointer;
    color: #7f8c8d;
}

.close:hover {
    color: #2c3e50;
}

@media (max-width: 768px) {
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .student-info {
        flex-direction: column;
        text-align: center;
        gap: 0.5rem;
    }
    
    .modal-content {
        width: 95%;
        margin: 2% auto;
    }
    
    .table-responsive {
        font-size: 0.9rem;
    }
    
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.75rem;
    }
}
</style>

<script>
function filterByCourse(courseId) {
    window.location.href = `?page=teacher_students.php&course_id=${courseId}`;
}

function viewStudentDetails(studentId) {
    // In a real implementation, this would fetch and display detailed student information
    document.getElementById('studentModalBody').innerHTML = `
        <div class="text-center">
            <i class="fas fa-user" style="font-size: 3rem; color: #3498db; margin-bottom: 1rem;"></i>
            <h4>Student Details</h4>
            <p>Detailed student information would be displayed here, including:</p>
            <ul style="text-align: left; max-width: 300px; margin: 0 auto;">
                <li>Complete profile information</li>
                <li>Course enrollment history</li>
                <li>Assignment submissions</li>
                <li>Grade history</li>
                <li>Progress analytics</li>
            </ul>
            <p style="margin-top: 1rem; color: #7f8c8d;">
                <em>This feature will be implemented in the next update.</em>
            </p>
        </div>
    `;
    document.getElementById('studentModal').style.display = 'block';
}

function messageStudent(studentId, studentName) {
    // In a real implementation, this would open a messaging interface
    showAlert(`Messaging feature for ${studentName} will be available soon!`, 'info');
}

function closeStudentModal() {
    document.getElementById('studentModal').style.display = 'none';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('studentModal');
    if (event.target == modal) {
        closeStudentModal();
    }
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
