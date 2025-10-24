<?php
session_start();
$tid = $_SESSION['id'];
$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get teacher details
$teacher_sql = "SELECT * FROM teacher_details WHERE tid = $tid";
$teacher_result = $conn->query($teacher_sql);
$teacher = $teacher_result->fetch_assoc();

// Get statistics
$courses_count = $conn->query("SELECT COUNT(*) as count FROM course WHERE  created_by = $tid")->fetch_assoc()['count'];
$assignments_count = $conn->query("SELECT COUNT(*) as count FROM assignments WHERE teacher_id = $tid")->fetch_assoc()['count'];
$materials_count = $conn->query("SELECT COUNT(*) as count FROM course_materials WHERE uploaded_by = $tid")->fetch_assoc()['count'];

// Get enrolled students count across all teacher's courses
$students_sql = "SELECT COUNT(DISTINCT e.student_id) as count 
                FROM enrollments e 
                JOIN course c ON e.course_id = c.cid 
                WHERE c.created_by = $tid";
$students_count = $conn->query($students_sql)->fetch_assoc()['count'];

// Get recent assignments
$recent_assignments = $conn->query("
    SELECT a.*, c.course_name 
    FROM assignments a 
    JOIN course c ON a.course_id = c.cid 
    WHERE a.teacher_id = $tid 
    ORDER BY a.created_date DESC 
    LIMIT 5
");

// Get recent submissions
$recent_submissions = $conn->query("
    SELECT s.*, a.title as assignment_title, sd.name as student_name, c.course_name
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.assignment_id
    JOIN student_details sd ON s.student_id = sd.sid
    JOIN course c ON a.course_id = c.cid
    WHERE a.teacher_id = $tid
    ORDER BY s.submitted_date DESC
    LIMIT 5
");
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 style="color: #2c3e50;">🎓 Welcome back, <?php echo htmlspecialchars($teacher['name']); ?>!</h1>
            <p style="color: #7f8c8d;">Here's what's happening in your classes today.</p>
        </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
        <div class="card">
            <div class="card-body text-center">
                <div class="number"><?php echo $courses_count; ?></div>
                <div class="label">My Courses</div>
                <i class="fas fa-book" style="font-size: 2rem; color: #3498db; margin-top: 1rem;"></i>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div class="number"><?php echo $students_count; ?></div>
                <div class="label">Total Students</div>
                <i class="fas fa-users" style="font-size: 2rem; color: #27ae60; margin-top: 1rem;"></i>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div class="number"><?php echo $assignments_count; ?></div>
                <div class="label">Assignments</div>
                <i class="fas fa-tasks" style="font-size: 2rem; color: #f39c12; margin-top: 1rem;"></i>
            </div>
        </div>
        
        <div class="card">
            <div class="card-body text-center">
                <div class="number"><?php echo $materials_count; ?></div>
                <div class="label">Course Materials</div>
                <i class="fas fa-file-alt" style="font-size: 2rem; color: #e74c3c; margin-top: 1rem;"></i>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Recent Assignments -->
        <div class="col-md-6">
            <div class="table-container">
                <h3>📝 Recent Assignments</h3>
                <?php if ($recent_assignments->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Assignment</th>
                                <th>Course</th>
                                <th>Due Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($assignment = $recent_assignments->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                    <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($assignment['due_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $assignment['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                            <?php echo ucfirst($assignment['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No assignments created yet. <a href="?page=teacher_assignments.php">Create your first assignment</a></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Recent Submissions -->
        <div class="col-md-6">
            <div class="table-container">
                <h3>📤 Recent Submissions</h3>
                <?php if ($recent_submissions->num_rows > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Assignment</th>
                                <th>Submitted</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($submission = $recent_submissions->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($submission['assignment_title']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($submission['submitted_date'])); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $submission['status'] == 'graded' ? 'success' : 
                                                ($submission['status'] == 'late' ? 'warning' : 'primary'); 
                                        ?>">
                                            <?php echo ucfirst($submission['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No submissions yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h3>🚀 Quick Actions</h3>
                    <div class="d-flex flex-wrap gap-3 mt-3">
                        <a href="?page=teacher_materials.php" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Upload Material
                        </a>
                        <a href="?page=teacher_assignments.php" class="btn btn-success">
                            <i class="fas fa-tasks"></i> Create Assignment
                        </a>
                        <a href="?page=teacher_students.php" class="btn btn-info">
                            <i class="fas fa-users"></i> View Students
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.badge {
    padding: 0.5em 0.75em;
    font-size: 0.75em;
    border-radius: 0.375rem;
}

.bg-success { background-color: #27ae60 !important; color: white; }
.bg-primary { background-color: #3498db !important; color: white; }
.bg-warning { background-color: #f39c12 !important; color: white; }
.bg-secondary { background-color: #95a5a6 !important; color: white; }
.bg-info { background-color: #17a2b8 !important; color: white; }

.gap-3 {
    gap: 1rem !important;
}

.d-flex {
    display: flex !important;
}

.flex-wrap {
    flex-wrap: wrap !important;
}

.text-muted {
    color: #6c757d !important;
}

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

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 0 0.75rem;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .dashboard-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .dashboard-cards {
        grid-template-columns: 1fr;
    }
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.mt-3 {
    margin-top: 1rem !important;
}

.mt-4 {
    margin-top: 1.5rem !important;
}
</style>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
