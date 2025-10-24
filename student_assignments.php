<?php
session_start();
require_once 'config.php';
$conn = getConnection();

$sid = $_SESSION['id'];

/* ---------------- Submit Assignment ---------------- */
if (isset($_POST['submit_assignment'])) {
    $assignment_id = intval($_POST['assignment_id']);
    $submission_text = escapeString($conn, $_POST['submission_text']);
    
    $file_path = '';
    $filename = '';
    
    // Handle file upload if provided
    if (isset($_FILES['submission_file']) && $_FILES['submission_file']['error'] == 0) {
        $uploadResult = uploadFile($_FILES['submission_file']);
        if ($uploadResult) {
            $file_path = $uploadResult['filepath'];
            $filename = $uploadResult['filename'];
        }
    }
    
    // Check if submission already exists
    $check_submission = $conn->query("SELECT submission_id FROM submissions WHERE assignment_id = $assignment_id AND student_id = $sid");
    
    if ($check_submission->num_rows > 0) {
        // Update existing submission
        $update_sql = "UPDATE submissions SET 
                       submission_text = '$submission_text', 
                       file_path = '$file_path',
                       submitted_date = NOW(),
                       status = 'submitted'
                       WHERE assignment_id = $assignment_id AND student_id = $sid";
        
        if ($conn->query($update_sql)) {
            echo "<script>showAlert('Assignment updated successfully!', 'success');</script>";
        } else {
            echo "<script>showAlert('Error updating assignment: " . $conn->error . "', 'error');</script>";
        }
    } else {
        // Create new submission
        $insert_sql = "INSERT INTO submissions (assignment_id, student_id, submission_text, file_path, submitted_date, status) 
                       VALUES ($assignment_id, $sid, '$submission_text', '$file_path', NOW(), 'submitted')";
        
        if ($conn->query($insert_sql)) {
            echo "<script>showAlert('Assignment submitted successfully!', 'success');</script>";
        } else {
            echo "<script>showAlert('Error submitting assignment: " . $conn->error . "', 'error');</script>";
        }
    }
}

/* ---------------- Fetch student's assignments ---------------- */
$assignments_query = "
    SELECT a.*, c.course_name, t.name as teacher_name, s.submission_id, s.submission_text, 
           s.file_path, s.grade, s.feedback, s.status as submission_status, s.submitted_date
    FROM assignments a
    JOIN course c ON a.course_id = c.cid
    JOIN teacher_details t ON a.teacher_id = t.tid
    JOIN enrollments e ON a.course_id = e.course_id AND e.student_id = $sid
    LEFT JOIN submissions s ON a.assignment_id = s.assignment_id AND s.student_id = $sid
    WHERE a.status = 'active'
    ORDER BY a.due_date ASC
";

$assignments_result = $conn->query($assignments_query);
?>

<div class="container">
    <h1>My Assignments</h1>
    
    <?php if ($assignments_result->num_rows > 0): ?>
        <div class="assignments-grid">
            <?php while ($assignment = $assignments_result->fetch_assoc()): ?>
                <div class="assignment-card">
                    <div class="assignment-header">
                        <h3><?php echo htmlspecialchars($assignment['title']); ?></h3>
                        <span class="course-badge"><?php echo htmlspecialchars($assignment['course_name']); ?></span>
                    </div>
                    
                    <div class="assignment-info">
                        <p><strong>Teacher:</strong> <?php echo htmlspecialchars($assignment['teacher_name']); ?></p>
                        <p><strong>Due Date:</strong> 
                            <?php 
                            $due_date = new DateTime($assignment['due_date']);
                            $now = new DateTime();
                            $is_late = $due_date < $now;
                            echo $due_date->format('M d, Y H:i');
                            if ($is_late && $assignment['submission_status'] !== 'submitted') {
                                echo ' <span class="late-badge">LATE</span>';
                            }
                            ?>
                        </p>
                        <p><strong>Max Points:</strong> <?php echo $assignment['max_points']; ?></p>
                    </div>
                    
                    <?php if ($assignment['description']): ?>
                        <div class="assignment-description">
                            <p><?php echo nl2br(htmlspecialchars($assignment['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <div class="submission-status">
                        <?php if ($assignment['submission_id']): ?>
                            <div class="submitted-info">
                                <h4>Your Submission:</h4>
                                <p><strong>Submitted:</strong> <?php echo date('M d, Y H:i', strtotime($assignment['submitted_date'])); ?></p>
                                
                                <?php if ($assignment['submission_text']): ?>
                                    <div class="submission-text">
                                        <strong>Text:</strong>
                                        <p><?php echo nl2br(htmlspecialchars($assignment['submission_text'])); ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($assignment['file_path']): ?>
                                    <div class="submission-file">
                                        <strong>File:</strong>
                                        <a href="<?php echo $assignment['file_path']; ?>" target="_blank" class="file-link">
                                            <i class="fas fa-download"></i> Download Submitted File
                                        </a>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($assignment['grade'] !== null): ?>
                                    <div class="grade-info">
                                        <strong>Grade:</strong> 
                                        <span class="grade"><?php echo $assignment['grade']; ?>/<?php echo $assignment['max_points']; ?></span>
                                        <?php if ($assignment['feedback']): ?>
                                            <div class="feedback">
                                                <strong>Feedback:</strong>
                                                <p><?php echo nl2br(htmlspecialchars($assignment['feedback'])); ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <button class="btn btn-primary" onclick="toggleSubmissionForm(<?php echo $assignment['assignment_id']; ?>)">
                                    <i class="fas fa-edit"></i> Update Submission
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="not-submitted">
                                <p class="status-not-submitted">Not submitted yet</p>
                                <button class="btn btn-primary" onclick="toggleSubmissionForm(<?php echo $assignment['assignment_id']; ?>)">
                                    <i class="fas fa-upload"></i> Submit Assignment
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Submission Form (Hidden by default) -->
                    <div class="submission-form" id="form_<?php echo $assignment['assignment_id']; ?>" style="display: none;">
                        <h4>Submit Assignment</h4>
                        <form method="post" enctype="multipart/form-data" onsubmit="submitAssignmentForm(event, this)">
                            <input type="hidden" name="assignment_id" value="<?php echo $assignment['assignment_id']; ?>">
                            
                            <div class="form-group">
                                <label for="submission_text_<?php echo $assignment['assignment_id']; ?>">Submission Text</label>
                                <textarea name="submission_text" id="submission_text_<?php echo $assignment['assignment_id']; ?>" 
                                          rows="5" placeholder="Enter your submission text here..."><?php echo htmlspecialchars($assignment['submission_text'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="submission_file_<?php echo $assignment['assignment_id']; ?>">Upload File (Optional)</label>
                                <input type="file" name="submission_file" id="submission_file_<?php echo $assignment['assignment_id']; ?>" 
                                       accept=".pdf,.doc,.docx,.txt,.jpg,.jpeg,.png">
                                <small>Supported formats: PDF, DOC, DOCX, TXT, JPG, PNG (Max 10MB)</small>
                            </div>
                            
                            <div class="form-actions">
                                <button type="submit" name="submit_assignment" class="btn btn-success">
                                    <i class="fas fa-check"></i> Submit Assignment
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="toggleSubmissionForm(<?php echo $assignment['assignment_id']; ?>)">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="no-assignments">
            <i class="fas fa-clipboard-list" style="font-size: 4rem; color: #ccc; margin-bottom: 1rem;"></i>
            <h3>No assignments found</h3>
            <p>You don't have any assignments yet. Enroll in courses to see assignments.</p>
            <a href="student.php?page=my_learning_new.php" class="btn btn-primary">
                <i class="fas fa-book"></i> Browse Courses
            </a>
        </div>
    <?php endif; ?>
</div>

<style>
    .container {
        max-width: 1200px;
        margin: 2rem auto;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 2rem;
    }
    
    .assignments-grid {
        display: grid;
        gap: 2rem;
        margin-top: 1.5rem;
    }
    
    .assignment-card {
        background: #f8f9fa;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .assignment-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .assignment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .assignment-header h3 {
        margin: 0;
        color: #1f2937;
        font-size: 1.25rem;
    }
    
    .course-badge {
        background: #3b82f6;
        color: white;
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .assignment-info {
        margin-bottom: 1rem;
    }
    
    .assignment-info p {
        margin: 0.5rem 0;
        color: #6b7280;
    }
    
    .late-badge {
        background: #ef4444;
        color: white;
        padding: 0.125rem 0.5rem;
        border-radius: 12px;
        font-size: 0.75rem;
        font-weight: 600;
        margin-left: 0.5rem;
    }
    
    .assignment-description {
        background: #fff;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
        border-left: 4px solid #3b82f6;
    }
    
    .submission-status {
        margin-top: 1rem;
    }
    
    .submitted-info, .not-submitted {
        background: #fff;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
    }
    
    .status-not-submitted {
        color: #dc2626;
        font-weight: 600;
        margin-bottom: 1rem;
    }
    
    .submission-text, .submission-file, .grade-info, .feedback {
        margin-bottom: 1rem;
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
    
    .grade {
        color: #059669;
        font-weight: 600;
        font-size: 1.125rem;
    }
    
    .feedback {
        background: #f0f9ff;
        padding: 0.75rem;
        border-radius: 6px;
        border-left: 3px solid #3b82f6;
    }
    
    .submission-form {
        background: #f0f9ff;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid #bfdbfe;
        margin-top: 1rem;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 600;
        color: #374151;
    }
    
    .form-group textarea, .form-group input[type="file"] {
        width: 100%;
        padding: 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 6px;
        font-size: 1rem;
        box-sizing: border-box;
    }
    
    .form-group textarea {
        resize: vertical;
        min-height: 100px;
    }
    
    .form-group small {
        color: #6b7280;
        font-size: 0.875rem;
        margin-top: 0.25rem;
        display: block;
    }
    
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
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
    
    .no-assignments {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
    }
    
    .no-assignments h3 {
        margin: 1rem 0;
        color: #374151;
    }
    
    @media (max-width: 768px) {
        .container {
            margin: 1rem;
            padding: 1rem;
        }
        
        .assignment-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 0.5rem;
        }
        
        .form-actions {
            flex-direction: column;
        }
    }
</style>

<script>
function toggleSubmissionForm(assignmentId) {
    const form = document.getElementById('form_' + assignmentId);
    if (form.style.display === 'none') {
        form.style.display = 'block';
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
        form.style.display = 'none';
    }
}

function submitAssignmentForm(e, form) {
    e.preventDefault();
    const data = new FormData(form);
    fetch(form.action, { method: "POST", body: data })
      .then(res => res.text())
      .then(html => {
        document.getElementById("main-content").innerHTML = html;
      });
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
