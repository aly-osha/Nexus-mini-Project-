<?php
session_start();
require_once 'config.php';


$conn = getConnection();

/* ---------------- Assign Course to Teacher ---------------- */
if (isset($_POST['assign_course'])) {
    $course_id = intval($_POST['course_id']);
    $teacher_id = intval($_POST['teacher_id']);
    
    // Update teacher_details table to assign course
    $update_query = "UPDATE teacher_details SET cid = ? WHERE tid = ?";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("ii", $course_id, $teacher_id);
    
    if ($stmt->execute()) {
          echo "<div class='alert alert-success'>Course assigned successfully!</div> ";
        header("location:admin.php#admin_course_assignment");
    } else {
        echo "<div class='alert alert-danger'>Error assigning course: " . $conn->error . "</div>";
    }
}

/* ---------------- Fetch all courses ---------------- */
$courses_query = "SELECT * FROM course WHERE status = 'active'";
$courses_result = $conn->query($courses_query);

/* ---------------- Fetch all teachers ---------------- */
$teachers_query = "SELECT td.*, tu.verified, c.course_name as assigned_course 
                   FROM teacher_details td 
                   LEFT JOIN teacher_user tu ON td.tid = tu.tid 
                   LEFT JOIN course c ON td.cid = c.cid";
$teachers_result = $conn->query($teachers_query);
?>

<div class="container">
    <h1>Course Assignment Management</h1>
    
    <!-- Assignment Form -->
    <div class="form-section">
        <h2>Assign Course to Teacher</h2>
        <form method="post" action="admin_course_assignment.php" onsubmit="submitAssignmentForm(event, this)">
            <div class="form-row">
                <div class="form-group">
                    <label for="course_id">Select Course</label>
                    <select name="course_id" id="course_id" required>
                        <option value="">Choose a course...</option>
                        <?php while ($course = $courses_result->fetch_assoc()): ?>
                            <option value="<?php echo $course['cid']; ?>">
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="teacher_id">Select Teacher</label>
                    <select name="teacher_id" id="teacher_id" required>
                        <option value="">Choose a teacher...</option>
                        <?php 
                        // Reset teachers result pointer
                        $teachers_result->data_seek(0);
                        while ($teacher = $teachers_result->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $teacher['tid']; ?>">
                                <?php echo htmlspecialchars($teacher['name']); ?>
                                <?php if ($teacher['assigned_course']): ?>
                                    (Currently: <?php echo htmlspecialchars($teacher['assigned_course']); ?>)
                                <?php endif; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" name="assign_course">Assign Course</button>
            </div>
        </form>
    </div>
    
    <!-- Current Assignments -->
    <div class="assignments-section">
        <h2>Current Course Assignments</h2>
        <div class="assignments-grid">
            <?php 
            // Reset teachers result pointer
            $teachers_result->data_seek(0);
            while ($teacher = $teachers_result->fetch_assoc()): 
            ?>
                <div class="assignment-card">
                    <div class="teacher-info">
                        <h3><?php echo htmlspecialchars($teacher['name']); ?></h3>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($teacher['e_mail']); ?></p>
                        <p><strong>Specialization:</strong> <?php echo htmlspecialchars($teacher['specialization'] ?? 'Not specified'); ?></p>
                        <p><strong>Status:</strong> 
                            <span class="status-badge <?php echo $teacher['verified'] === 'yes' ? 'verified' : 'pending'; ?>">
                                <?php echo $teacher['verified'] === 'yes' ? 'Verified' : 'Pending'; ?>
                            </span>
                        </p>
                    </div>
                    
                    <div class="course-info">
                        <?php if ($teacher['assigned_course']): ?>
                            <h4>Assigned Course:</h4>
                            <p class="assigned-course"><?php echo htmlspecialchars($teacher['assigned_course']); ?></p>
                        <?php else: ?>
                            <p class="no-course">No course assigned</p>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
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
    
    .form-group select {
        padding: 0.75rem;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        background-color: white;
        transition: border-color 0.3s;
    }
    
    .form-group select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    .form-actions {
        text-align: right;
    }
    
    .form-actions button {
        background: #3b82f6;
        color: white;
        border: none;
        padding: 0.75rem 2rem;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: background-color 0.3s;
    }
    
    .form-actions button:hover {
        background: #2563eb;
    }
    
    .assignments-section {
        margin-top: 2rem;
    }
    
    .assignments-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
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
    
    .teacher-info h3 {
        margin: 0 0 1rem 0;
        color: #1f2937;
        font-size: 1.25rem;
    }
    
    .teacher-info p {
        margin: 0.5rem 0;
        color: #6b7280;
    }
    
    .status-badge {
        padding: 0.25rem 0.75rem;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
    }
    
    .status-badge.verified {
        background-color: #d1fae5;
        color: #065f46;
    }
    
    .status-badge.pending {
        background-color: #fef3c7;
        color: #92400e;
    }
    
    .course-info {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .course-info h4 {
        margin: 0 0 0.5rem 0;
        color: #374151;
        font-size: 1rem;
    }
    
    .assigned-course {
        color: #059669;
        font-weight: 600;
        background-color: #ecfdf5;
        padding: 0.5rem;
        border-radius: 6px;
        margin: 0;
    }
    
    .no-course {
        color: #dc2626;
        font-style: italic;
        margin: 0;
    }
    
    .alert {
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .alert-success {
        background-color: #d1fae5;
        color: #065f46;
        border: 1px solid #a7f3d0;
    }
    
    .alert-danger {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
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
</script>
