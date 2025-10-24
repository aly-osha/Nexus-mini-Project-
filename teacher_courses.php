<?php
session_start();
$tid = $_SESSION['id'];
$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/* Course creation removed - teachers can only upload materials */

/* Course updating removed - teachers can only upload materials */

/* Course deletion removed - teachers can only upload materials */

/* Course editing removed - teachers can only upload materials */

// Get all active courses since teachers can no longer create courses
$courses_result = $conn->query("SELECT * FROM course WHERE status = 'active' ORDER BY course_name");
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 style="color: #2c3e50;">📚 Available Courses</h1>
            <p style="color: #7f8c8d;">View available courses and upload materials for students.</p>
        </div>
    </div>

    <!-- Course Creation/Edit Form removed - teachers can only upload materials -->

    <!-- Courses Grid -->
    <div class="course-grid">
        <?php if ($courses_result->num_rows > 0): ?>
            <?php while ($course = $courses_result->fetch_assoc()): ?>
                <div class="course-card">
                    <?php if ($course['filepath']): ?>
                        <img src="<?php echo $course['filepath']; ?>" alt="<?php echo htmlspecialchars($course['course_name']); ?>">
                    <?php else: ?>
                        <div class="no-image">
                            <i class="fas fa-book" style="font-size: 3rem; color: #bdc3c7;"></i>
                        </div>
                    <?php endif; ?>
                    
                    <h4><?php echo htmlspecialchars($course['course_name']); ?></h4>
                    <p><?php echo htmlspecialchars(substr($course['description'], 0, 100)) . (strlen($course['description']) > 100 ? '...' : ''); ?></p>
                    
                    <div class="course-meta">
                        <small class="text-muted">
                            Created: <?php echo date('M j, Y', strtotime($course['created_date'])); ?>
                        </small>
                        <span class="badge bg-<?php echo $course['status'] == 'active' ? 'success' : 'secondary'; ?>">
                            <?php echo ucfirst($course['status']); ?>
                        </span>
                    </div>
                    
                    <div class="course-actions">
                        <a href="?page=teacher_assignments.php&course_id=<?php echo $course['cid']; ?>" class="btn btn-info btn-sm">
                            <i class="fas fa-tasks"></i> Assignments
                        </a>
                        <a href="?page=teacher_materials.php&course_id=<?php echo $course['cid']; ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-file-alt"></i> Materials
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-book" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 1rem;"></i>
                    <h3 style="color: #7f8c8d;">No courses available</h3>
                    <p style="color: #95a5a6;">Contact administrator to add courses to the system.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.form-actions {
    display: flex;
    gap: 1rem;
    margin-top: 1.5rem;
}

.btn-secondary {
    background-color: #95a5a6;
    color: white;
    text-decoration: none;
}

.btn-secondary:hover {
    background-color: #7f8c8d;
}

.btn-sm {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
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
    min-width: 80px;
}

@media (max-width: 768px) {
    .course-actions .btn {
        flex: none;
        width: 100%;
        margin-bottom: 0.25rem;
    }
}
</style>

<script>
// Course management functions removed - teachers can only upload materials
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
