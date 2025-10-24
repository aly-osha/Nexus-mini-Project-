<?php
session_start();
$tid = $_SESSION['id'];
$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle assignment creation
if (isset($_POST['create_assignment'])) {
    $course_id = intval($_POST['course_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $max_points = intval($_POST['max_points']);
    
    $insert_sql = "INSERT INTO assignments (course_id, teacher_id, title, description, due_date, max_points) 
                   VALUES ($course_id, $tid, '$title', '$description', '$due_date', $max_points)";
    
    if ($conn->query($insert_sql)) {
        echo "<script>showAlert('Assignment created successfully!', 'success');</script>";
    } else {
        echo "<script>showAlert('Error creating assignment: " . $conn->error . "', 'error');</script>";
    }
}

// Handle assignment update
if (isset($_POST['update_assignment'])) {
    $assignment_id = intval($_POST['assignment_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);
    $max_points = intval($_POST['max_points']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);
    
    $update_sql = "UPDATE assignments SET title='$title', description='$description', 
                   due_date='$due_date', max_points=$max_points, status='$status' 
                   WHERE assignment_id=$assignment_id AND teacher_id=$tid";
    
    if ($conn->query($update_sql)) {
        echo "<script>showAlert('Assignment updated successfully!', 'success');</script>";
    } else {
        echo "<script>showAlert('Error updating assignment: " . $conn->error . "', 'error');</script>";
    }
}

// Handle grade submission
if (isset($_POST['grade_submission'])) {
    $submission_id = intval($_POST['submission_id']);
    $grade = floatval($_POST['grade']);
    $feedback = mysqli_real_escape_string($conn, $_POST['feedback']);
    
    $grade_sql = "UPDATE submissions SET grade=$grade, feedback='$feedback', status='graded' 
                  WHERE submission_id=$submission_id";
    
    if ($conn->query($grade_sql)) {
        echo "<script>showAlert('Grade submitted successfully!', 'success');</script>";
    } else {
        echo "<script>showAlert('Error submitting grade: " . $conn->error . "', 'error');</script>";
    }
}

// Get filter parameters
$course_filter = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;
$view = isset($_GET['view']) ? $_GET['view'] : 'assignments';

// Get teacher's courses for dropdown
$courses_result = $conn->query("SELECT * FROM course WHERE created_by = $tid ORDER BY course_name");

// Get edit assignment data
$edit_assignment = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $edit_result = $conn->query("SELECT * FROM assignments WHERE assignment_id=$edit_id AND teacher_id=$tid");
    if ($edit_result->num_rows > 0) {
        $edit_assignment = $edit_result->fetch_assoc();
    }
}

// Get assignments based on filter
$assignments_sql = "SELECT a.*, c.course_name 
                   FROM assignments a 
                   JOIN course c ON a.course_id = c.cid 
                   WHERE a.teacher_id = $tid";
if ($course_filter > 0) {
    $assignments_sql .= " AND a.course_id = $course_filter";
}
$assignments_sql .= " ORDER BY a.created_date DESC";
$assignments_result = $conn->query($assignments_sql);

// Get submissions for grading
if ($view == 'submissions') {
    $submissions_sql = "SELECT s.*, a.title as assignment_title, a.max_points, 
                       sd.name as student_name, c.course_name
                       FROM submissions s
                       JOIN assignments a ON s.assignment_id = a.assignment_id
                       JOIN student_details sd ON s.student_id = sd.sid
                       JOIN course c ON a.course_id = c.cid
                       WHERE a.teacher_id = $tid";
    if ($course_filter > 0) {
        $submissions_sql .= " AND a.course_id = $course_filter";
    }
    $submissions_sql .= " ORDER BY s.submitted_date DESC";
    $submissions_result = $conn->query($submissions_sql);
}
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 style="color: #2c3e50;">📝 Assignments & Grading</h1>
            <p style="color: #7f8c8d;">Create assignments and grade student submissions.</p>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="nav-tabs-container mb-4">
        <a href="?page=teacher_assignments.php&view=assignments<?php echo $course_filter ? '&course_id='.$course_filter : ''; ?>" 
           class="nav-tab <?php echo $view == 'assignments' ? 'active' : ''; ?>">
            📝 My Assignments
        </a>
        <a href="?page=teacher_assignments.php&view=submissions<?php echo $course_filter ? '&course_id='.$course_filter : ''; ?>" 
           class="nav-tab <?php echo $view == 'submissions' ? 'active' : ''; ?>">
            📤 Submissions to Grade
        </a>
    </div>

    <!-- Course Filter -->
    <div class="filter-container mb-4">
        <select onchange="filterByCourse(this.value)" class="form-control" style="width: auto; display: inline-block;">
            <option value="0">All Courses</option>
            <?php while ($course = $courses_result->fetch_assoc()): ?>
                <option value="<?php echo $course['cid']; ?>" <?php echo $course_filter == $course['cid'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <?php if ($view == 'assignments'): ?>
        <!-- Assignment Creation/Edit Form -->
        <?php if (!$edit_assignment || $edit_assignment): ?>
            <div class="form-container">
                <h3><?php echo $edit_assignment ? 'Edit Assignment' : 'Create New Assignment'; ?></h3>
                <form method="post" onsubmit="return handleAssignmentForm(this);">
                    <?php if ($edit_assignment): ?>
                        <input type="hidden" name="assignment_id" value="<?php echo $edit_assignment['assignment_id']; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="course_id">Course *</label>
                                <select id="course_id" name="course_id" required>
                                    <option value="">Select Course</option>
                                    <?php 
                                    $courses_result->data_seek(0); // Reset result pointer
                                    while ($course = $courses_result->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $course['cid']; ?>" 
                                                <?php echo ($edit_assignment && $edit_assignment['course_id'] == $course['cid']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="max_points">Maximum Points *</label>
                                <input type="number" id="max_points" name="max_points" required min="1" 
                                       value="<?php echo $edit_assignment ? $edit_assignment['max_points'] : '100'; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="title">Assignment Title *</label>
                                <input type="text" id="title" name="title" required 
                                       value="<?php echo $edit_assignment ? htmlspecialchars($edit_assignment['title']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="due_date">Due Date *</label>
                                <input type="datetime-local" id="due_date" name="due_date" required 
                                       value="<?php echo $edit_assignment ? date('Y-m-d\TH:i', strtotime($edit_assignment['due_date'])) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($edit_assignment): ?>
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status">
                                <option value="active" <?php echo $edit_assignment['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo $edit_assignment['status'] == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="description">Assignment Description</label>
                        <textarea id="description" name="description" rows="4" 
                                  placeholder="Provide detailed instructions for the assignment..."><?php echo $edit_assignment ? htmlspecialchars($edit_assignment['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" name="<?php echo $edit_assignment ? 'update_assignment' : 'create_assignment'; ?>" 
                                class="btn btn-primary">
                            <?php echo $edit_assignment ? 'Update Assignment' : 'Create Assignment'; ?>
                        </button>
                        <?php if ($edit_assignment): ?>
                            <a href="?page=teacher_assignments.php&view=assignments" class="btn btn-secondary">Cancel</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        <?php endif; ?>

        <!-- Assignments List -->
        <div class="table-container">
            <h3>📋 My Assignments</h3>
            <?php if ($assignments_result->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Assignment</th>
                            <th>Course</th>
                            <th>Due Date</th>
                            <th>Points</th>
                            <th>Status</th>
                            <th>Submissions</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                            <?php
                            // Get submission count
                            $sub_count = $conn->query("SELECT COUNT(*) as count FROM submissions WHERE assignment_id = " . $assignment['assignment_id'])->fetch_assoc()['count'];
                            ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($assignment['title']); ?></strong>
                                    <?php if ($assignment['description']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars(substr($assignment['description'], 0, 50)) . '...'; ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($assignment['due_date'])); ?></td>
                                <td><?php echo $assignment['max_points']; ?> pts</td>
                                <td>
                                    <span class="badge bg-<?php echo $assignment['status'] == 'active' ? 'success' : 'secondary'; ?>">
                                        <?php echo ucfirst($assignment['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info"><?php echo $sub_count; ?> submissions</span>
                                </td>
                                <td>
                                    <a href="?page=teacher_assignments.php&view=assignments&edit=<?php echo $assignment['assignment_id']; ?>" 
                                       class="btn btn-warning btn-sm">Edit</a>
                                    <a href="?page=teacher_assignments.php&view=submissions&assignment_id=<?php echo $assignment['assignment_id']; ?>" 
                                       class="btn btn-info btn-sm">Grade</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No assignments created yet. Create your first assignment above!</p>
            <?php endif; ?>
        </div>

    <?php else: // Submissions view ?>
        <!-- Submissions for Grading -->
        <div class="table-container">
            <h3>📤 Submissions to Grade</h3>
            <?php if (isset($submissions_result) && $submissions_result->num_rows > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Assignment</th>
                            <th>Course</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Grade</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($submission = $submissions_result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($submission['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($submission['assignment_title']); ?></td>
                                <td><?php echo htmlspecialchars($submission['course_name']); ?></td>
                                <td><?php echo date('M j, Y g:i A', strtotime($submission['submitted_date'])); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo $submission['status'] == 'graded' ? 'success' : 
                                            ($submission['status'] == 'late' ? 'warning' : 'primary'); 
                                    ?>">
                                        <?php echo ucfirst($submission['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($submission['grade']): ?>
                                        <?php echo $submission['grade']; ?>/<?php echo $submission['max_points']; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Not graded</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button onclick="gradeSubmission(<?php echo $submission['submission_id']; ?>, '<?php echo htmlspecialchars($submission['student_name']); ?>', '<?php echo htmlspecialchars($submission['assignment_title']); ?>', <?php echo $submission['max_points']; ?>, '<?php echo htmlspecialchars($submission['submission_text']); ?>', '<?php echo $submission['grade'] ?? ''; ?>', '<?php echo htmlspecialchars($submission['feedback'] ?? ''); ?>')" 
                                            class="btn btn-primary btn-sm">
                                        <?php echo $submission['grade'] ? 'Update Grade' : 'Grade'; ?>
                                    </button>
                                    <?php if ($submission['file_path']): ?>
                                        <a href="<?php echo $submission['file_path']; ?>" target="_blank" class="btn btn-info btn-sm">View File</a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-muted">No submissions to grade at this time.</p>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Grading Modal -->
<div id="gradingModal" class="modal" style="display: none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Grade Submission</h3>
            <span class="close" onclick="closeGradingModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="gradingForm" method="post">
                <input type="hidden" id="submission_id" name="submission_id">
                
                <div class="form-group">
                    <label><strong>Student:</strong> <span id="student_name"></span></label>
                </div>
                
                <div class="form-group">
                    <label><strong>Assignment:</strong> <span id="assignment_title"></span></label>
                </div>
                
                <div class="form-group">
                    <label>Submission:</label>
                    <div id="submission_content" style="background: #f8f9fa; padding: 1rem; border-radius: 6px; margin-top: 0.5rem;"></div>
                </div>
                
                <div class="form-group">
                    <label for="grade">Grade (out of <span id="max_points"></span> points) *</label>
                    <input type="number" id="grade" name="grade" required min="0" step="0.1">
                </div>
                
                <div class="form-group">
                    <label for="feedback">Feedback</label>
                    <textarea id="feedback" name="feedback" rows="4" placeholder="Provide feedback to the student..."></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" name="grade_submission" class="btn btn-primary">Submit Grade</button>
                    <button type="button" onclick="closeGradingModal()" class="btn btn-secondary">Cancel</button>
                </div>
            </form>
        </div>
    </div>
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

.filter-container {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
    .nav-tabs-container {
        flex-direction: column;
    }
    
    .nav-tab {
        text-align: center;
    }
    
    .modal-content {
        width: 95%;
        margin: 2% auto;
    }
}
</style>

<script>
function filterByCourse(courseId) {
    const currentView = '<?php echo $view; ?>';
    window.location.href = `?page=teacher_assignments.php&view=${currentView}&course_id=${courseId}`;
}

function handleAssignmentForm(form) {
    const formData = new FormData(form);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        loadContent('teacher_assignments.php&view=assignments', null, false);
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    });
    
    return false;
}

function gradeSubmission(submissionId, studentName, assignmentTitle, maxPoints, submissionText, currentGrade, currentFeedback) {
    document.getElementById('submission_id').value = submissionId;
    document.getElementById('student_name').textContent = studentName;
    document.getElementById('assignment_title').textContent = assignmentTitle;
    document.getElementById('max_points').textContent = maxPoints;
    document.getElementById('submission_content').textContent = submissionText || 'No text submission';
    document.getElementById('grade').value = currentGrade || '';
    document.getElementById('grade').max = maxPoints;
    document.getElementById('feedback').value = currentFeedback || '';
    
    document.getElementById('gradingModal').style.display = 'block';
}

function closeGradingModal() {
    document.getElementById('gradingModal').style.display = 'none';
}

// Handle grading form submission
document.getElementById('gradingForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    fetch(window.location.href, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        closeGradingModal();
        loadContent('teacher_assignments.php&view=submissions', null, false);
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('An error occurred. Please try again.', 'error');
    });
});

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('gradingModal');
    if (event.target == modal) {
        closeGradingModal();
    }
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
