<?php
session_start();
require_once 'config.php';
$conn = getConnection();

$tid = $_SESSION['id'];

/* ---------------- Create Assignment ---------------- */
if (isset($_POST['create_assignment'])) {
    $course_id = intval($_POST['course_id']);
    $title = escapeString($conn, $_POST['title']);
    $description = escapeString($conn, $_POST['description']);
    $due_date = $_POST['due_date'];
    $max_points = intval($_POST['max_points']);
    
    // Verify teacher has access to this course
    $course_check = $conn->query("SELECT cid FROM course WHERE cid = $course_id AND (created_by = $tid OR cid IN (SELECT cid FROM teacher_details WHERE tid = $tid))");
    
    if ($course_check->num_rows > 0) {
        $insert_sql = "INSERT INTO assignments (course_id, teacher_id, title, description, due_date, max_points, created_date, status) 
                       VALUES ($course_id, $tid, '$title', '$description', '$due_date', $max_points, NOW(), 'active')";
        
        if ($conn->query($insert_sql)) {
            echo "<script>showAlert('Assignment created successfully!', 'success');</script>";
        } else {
            echo "<script>showAlert('Error creating assignment: " . $conn->error . "', 'error');</script>";
        }
    } else {
        echo "<script>showAlert('You do not have permission to create assignments for this course.', 'error');</script>";
    }
}

/* ---------------- Grade Assignment ---------------- */
if (isset($_POST['grade_submission'])) {
    $submission_id = intval($_POST['submission_id']);
    $grade = floatval($_POST['grade']);
    $feedback = escapeString($conn, $_POST['feedback']);
    
    // Verify teacher has permission to grade this submission
    $submission_check = $conn->query("
        SELECT s.*, a.teacher_id, a.max_points 
        FROM submissions s 
        JOIN assignments a ON s.assignment_id = a.assignment_id 
        WHERE s.submission_id = $submission_id AND a.teacher_id = $tid
    ");
    
    if ($submission_check->num_rows > 0) {
        $submission_data = $submission_check->fetch_assoc();
        
        if ($grade <= $submission_data['max_points']) {
            $update_sql = "UPDATE submissions SET grade = $grade, feedback = '$feedback', status = 'graded' WHERE submission_id = $submission_id";
            
            if ($conn->query($update_sql)) {
                echo "<script>showAlert('Grade submitted successfully!', 'success');</script>";
            } else {
                echo "<script>showAlert('Error submitting grade: " . $conn->error . "', 'error');</script>";
            }
        } else {
            // Build a safe message using PHP variables outside the JS string to avoid syntax issues
            $max = intval($submission_data['max_points']);
            $msg = "Grade cannot exceed maximum points ($max).";
            echo "<script>showAlert(" . json_encode($msg) . ", 'error');</script>";
        }
    } else {
        echo "<script>showAlert('You do not have permission to grade this submission.', 'error');</script>";
    }
}

/* ---------------- Fetch teacher's courses ---------------- */
$courses_query = "
    SELECT DISTINCT c.* 
    FROM course c 
    WHERE c.created_by = $tid OR c.cid IN (SELECT cid FROM teacher_details WHERE tid = $tid)
    ORDER BY c.course_name
";
$courses_result = $conn->query($courses_query);

/* ---------------- Fetch teacher's assignments ---------------- */
$assignments_query = "
    SELECT a.*, c.course_name, 
           COUNT(s.submission_id) as total_submissions,
           COUNT(CASE WHEN s.status = 'graded' THEN 1 END) as graded_submissions
    FROM assignments a
    JOIN course c ON a.course_id = c.cid
    LEFT JOIN submissions s ON a.assignment_id = s.assignment_id
    WHERE a.teacher_id = $tid
    GROUP BY a.assignment_id
    ORDER BY a.created_date DESC
";
$assignments_result = $conn->query($assignments_query);

/* ---------------- Fetch submissions for grading ---------------- */
$submissions_query = "
    SELECT s.*, a.title as assignment_title, a.max_points, c.course_name, 
           sd.name as student_name, sd.e_mail as student_email
    FROM submissions s
    JOIN assignments a ON s.assignment_id = a.assignment_id
    JOIN course c ON a.course_id = c.cid
    JOIN student_details sd ON s.student_id = sd.sid
    WHERE a.teacher_id = $tid AND s.status = 'submitted'
    ORDER BY s.submitted_date ASC
";
$submissions_result = $conn->query($submissions_query);
?>

<div class="container">
    <h1>Assignment Management</h1>
    
    <!-- Create Assignment Form -->
    <div class="form-section">
        <h2>Create New Assignment</h2>
        <form method="post" action="teacher_assignment_management.php" onsubmit="submitAssignmentForm(event, this)">
            <div class="form-row">
                <div class="form-group">
                    <label for="course_id">Course</label>
                    <select name="course_id" id="course_id" required>
                        <option value="">Select a course...</option>
                        <?php while ($course = $courses_result->fetch_assoc()): ?>
                            <option value="<?php echo $course['cid']; ?>">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="title">Assignment Title</label>
                    <input type="text" name="title" id="title" required placeholder="Enter assignment title">
                </div>
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea name="description" id="description" rows="4" placeholder="Describe the assignment requirements..."></textarea>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="due_date">Due Date</label>
                    <input type="datetime-local" name="due_date" id="due_date" required>
                </div>
                
                <div class="form-group">
                    <label for="max_points">Maximum Points</label>
                    <input type="number" name="max_points" id="max_points" required min="1" max="1000" value="100">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="create_assignment" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Assignment
                </button>
            </div>
        </form>
    </div>
    
    <!-- My Assignments -->
    <div class="assignments-section">
        <h2>My Assignments</h2>
        <?php if ($assignments_result->num_rows > 0): ?>
            <div class="assignments-grid">
                <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                    <div class="assignment-card">
                        <div class="assignment-header">
                            <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                            <span class="course-badge"><?php echo htmlspecialchars($assignment['course_name']); ?></span>
                        </div>
                        
                        <div class="assignment-info">
                            <p><strong>Due Date:</strong> <?php echo date('M d, Y H:i', strtotime($assignment['due_date'])); ?></p>
                            <p><strong>Max Points:</strong> <?php echo $assignment['max_points']; ?></p>
                            <p><strong>Created:</strong> <?php echo date('M d, Y', strtotime($assignment['created_date'])); ?></p>
                        </div>
                        
                        <?php if ($assignment['description']): ?>
                            <div class="assignment-description">
                                <p><?php echo nl2br(htmlspecialchars(substr($assignment['description'], 0, 150))); ?>
                                   <?php echo strlen($assignment['description']) > 150 ? '...' : ''; ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="submission-stats">
                            <div class="stat">
                                <span class="stat-number"><?php echo $assignment['total_submissions']; ?></span>
                                <span class="stat-label">Submissions</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number"><?php echo $assignment['graded_submissions']; ?></span>
                                <span class="stat-label">Graded</span>
                            </div>
                            <div class="stat">
                                <span class="stat-number"><?php echo $assignment['total_submissions'] - $assignment['graded_submissions']; ?></span>
                                <span class="stat-label">Pending</span>
                            </div>
                        </div>
                        
                        <div class="assignment-actions">
                            <button class="btn btn-secondary" onclick="viewAssignmentDetails(<?php echo $assignment['assignment_id']; ?>)">
                                <i class="fas fa-eye"></i> View Details
                            </button>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-assignments">
                <i class="fas fa-clipboard-list" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                <p>No assignments created yet. Create your first assignment above.</p>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Pending Submissions for Grading -->
    <div class="grading-section">
        <h2>Submissions to Grade</h2>
        <?php if ($submissions_result->num_rows > 0): ?>
            <div class="submissions-grid">
                <?php while ($submission = $submissions_result->fetch_assoc()): ?>
                    <div class="submission-card">
                        <div class="submission-header">
                            <h4><?php echo htmlspecialchars($submission['assignment_title']); ?></h4>
                            <span class="course-badge"><?php echo htmlspecialchars($submission['course_name']); ?></span>
                        </div>
                        
                        <div class="student-info">
                            <p><strong>Student:</strong> <?php echo htmlspecialchars($submission['student_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($submission['student_email']); ?></p>
                            <p><strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($submission['submitted_date'])); ?></p>
                        </div>
                        
                        <?php if ($submission['submission_text']): ?>
                            <div class="submission-text">
                                <strong>Text Submission:</strong>
                                <p><?php echo nl2br(htmlspecialchars(substr($submission['submission_text'], 0, 200))); ?>
                                   <?php echo strlen($submission['submission_text']) > 200 ? '...' : ''; ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($submission['file_path']): ?>
                            <div class="submission-file">
                                <strong>File:</strong>
                                <a href="<?php echo $submission['file_path']; ?>" target="_blank" class="file-link">
                                    <i class="fas fa-download"></i> Download File
                                </a>
                            </div>
                        <?php endif; ?>
                        
                        <div class="grading-form">
                            <form method="post" action="teacher_assignment_management.php" onsubmit="submitGradeForm(event, this)">
                                <input type="hidden" name="submission_id" value="<?php echo $submission['submission_id']; ?>">
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="grade_<?php echo $submission['submission_id']; ?>">Grade (Max: <?php echo $submission['max_points']; ?>)</label>
                                        <input type="number" name="grade" id="grade_<?php echo $submission['submission_id']; ?>" 
                                               required min="0" max="<?php echo $submission['max_points']; ?>" 
                                               step="0.1" placeholder="Enter grade">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="feedback_<?php echo $submission['submission_id']; ?>">Feedback</label>
                                    <textarea name="feedback" id="feedback_<?php echo $submission['submission_id']; ?>" 
                                              rows="3" placeholder="Provide feedback to the student..."></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" name="grade_submission" class="btn btn-success">
                                        <i class="fas fa-check"></i> Submit Grade
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-submissions">
                <i class="fas fa-check-circle" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                <p>No pending submissions to grade. Great job!</p>
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
    
    .form-section {
        background: #f8f9fa;
        padding: 2rem;
        border-radius: 10px;
        margin-bottom: 2rem;
        border-left: 4px solid #3b82f6;
    }
    
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 2rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        display: flex;
        flex-direction: column;
    }
    
    .form-group label {
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #374151;
    }
    
    .form-group input, .form-group select, .form-group textarea {
        padding: 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        background-color: white;
        transition: border-color 0.3s;
    }
    
    .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .form-actions {
        text-align: right;
    }
    
    .assignments-section, .grading-section {
        margin-top: 2rem;
    }
    
    .assignments-grid, .submissions-grid {
        display: grid;
        gap: 1.5rem;
        margin-top: 1rem;
    }
    
    .assignment-card, .submission-card {
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .assignment-card:hover, .submission-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .assignment-header, .submission-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .assignment-header h3, .submission-header h4 {
        margin: 0;
        color: #1f2937;
        font-size: 1.125rem;
    }
    
    .course-badge {
        background: #3b82f6;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .assignment-info, .student-info {
        margin-bottom: 1rem;
    }
    
    .assignment-info p, .student-info p {
        margin: 0.5rem 0;
        color: #6b7280;
    }
    
    .assignment-description {
        background: #fff;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 4px solid #3b82f6;
    }
    
    .submission-stats {
        display: flex;
        justify-content: space-around;
        margin-bottom: 1rem;
        padding: 1rem;
        background: #fff;
        border-radius: 8px;
    }
    
    .stat {
        text-align: center;
    }
    
    .stat-number {
        display: block;
        font-size: 1.5rem;
        font-weight: 600;
        color: #3b82f6;
    }
    
    .stat-label {
        font-size: 0.875rem;
        color: #6b7280;
    }
    
    .assignment-actions {
        text-align: right;
    }
    
    .submission-text, .submission-file {
        margin-bottom: 1rem;
        padding: 1rem;
        background: #fff;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    
    .file-link {
        color: #3b82f6;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .file-link:hover {
        text-decoration: underline;
    }
    
    .grading-form {
        background: #f0f9ff;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #bfdbfe;
        margin-top: 1rem;
    }
    
    .btn {
        padding: 0.75rem 1.5rem;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        font-size: 0.875rem;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s;
    }
    
    .btn-primary {
        background: #3b82f6;
        color: white;
    }
    
    .btn-primary:hover {
        background: #2563eb;
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-success:hover {
        background: #059669;
    }
    
    .btn-secondary {
        background: #6b7280;
        color: white;
    }
    
    .btn-secondary:hover {
        background: #4b5563;
    }
    
    .no-assignments, .no-submissions {
        text-align: center;
        padding: 3rem 2rem;
        color: #6b7280;
    }
    
    .no-assignments p, .no-submissions p {
        margin: 0;
        font-size: 1.125rem;
    }
    
    @media (max-width: 768px) {
        .container {
            margin: 1rem;
            padding: 1rem;
        }
        
        .form-row {
            grid-template-columns: 1fr;
        }
        
        .assignment-header, .submission-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .submission-stats {
            flex-direction: column;
            gap: 1rem;
        }
    }
</style>

<script>
function submitAssignmentForm(e, form) {
    e.preventDefault();
    const data = new FormData(form);
    fetch(form.action, { method: "POST", body: data })
      .then(res => res.text())
      .then(html => {
        document.getElementById("main-content").innerHTML = html;
      });
}

function submitGradeForm(e, form) {
    e.preventDefault();
    const data = new FormData(form);
    fetch(form.action, { method: "POST", body: data })
      .then(res => res.text())
      .then(html => {
        document.getElementById("main-content").innerHTML = html;
      });
}

function viewAssignmentDetails(assignmentId) {
    // This would open a modal or navigate to a detailed view
    showAlert('Assignment details view will be available in the next update!', 'info');
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
