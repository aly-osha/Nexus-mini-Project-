<?php
session_start();
require_once 'config.php';
$conn = getConnection();

/* ---------------- Verify Single User ---------------- */
if (isset($_POST['verify_user'])) {
    $user_type = $_POST['user_type'];
    $user_id = intval($_POST['user_id']);
    
    if ($user_type === 'student') {
        $update_sql = "UPDATE student_user SET verified = 'yes' WHERE sid = $user_id";
    } else if ($user_type === 'teacher') {
        $update_sql = "UPDATE teacher_user SET verified = 'yes' WHERE tid = $user_id";
    }
    
    if ($conn->query($update_sql)) {
        echo "<script>showAlert('User verified successfully!', 'success');</script>";
    } else {
        echo "<script>showAlert('Error verifying user: " . $conn->error . "', 'error');</script>";
    }
}

/* ---------------- Verify Multiple Users ---------------- */
if (isset($_POST['verify_selected'])) {
    $selected_users = $_POST['selected_users'] ?? [];
    
    if (empty($selected_users)) {
        echo "<script>showAlert('Please select users to verify.', 'error');</script>";
    } else {
        $success_count = 0;
        $error_count = 0;
        
        foreach ($selected_users as $user_info) {
            list($user_type, $user_id) = explode('_', $user_info);
            $user_id = intval($user_id);
            
            if ($user_type === 'student') {
                $update_sql = "UPDATE student_user SET verified = 'yes' WHERE sid = $user_id";
            } else if ($user_type === 'teacher') {
                $update_sql = "UPDATE teacher_user SET verified = 'yes' WHERE tid = $user_id";
            }
            
            if ($conn->query($update_sql)) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
        
        if ($success_count > 0) {
            echo "<script>showAlert('$success_count user(s) verified successfully!', 'success');</script>";
        }
        if ($error_count > 0) {
            echo "<script>showAlert('Error verifying $error_count user(s).', 'error');</script>";
        }
    }
}

/* ---------------- Verify All Users ---------------- */
if (isset($_POST['verify_all'])) {
    $student_update = "UPDATE student_user SET verified = 'yes' WHERE verified != 'yes'";
    $teacher_update = "UPDATE teacher_user SET verified = 'yes' WHERE verified != 'yes'";
    
    $student_result = $conn->query($student_update);
    $teacher_result = $conn->query($teacher_update);
    
    $total_affected = $conn->affected_rows;
    
    if ($student_result && $teacher_result) {
        echo "<script>showAlert('All unverified users have been verified successfully!', 'success');</script>";
    } else {
        echo "<script>showAlert('Some errors occurred while verifying users.', 'error');</script>";
    }
}

/* ---------------- Fetch unverified users ---------------- */
$unverified_students_query = "
    SELECT sd.*, su.user_name, su.register, su.verified
    FROM student_details sd
    JOIN student_user su ON sd.sid = su.sid
    WHERE su.verified != 'yes' OR su.verified IS NULL
    ORDER BY su.register ASC
";

$unverified_teachers_query = "
    SELECT td.*, tu.user_name, tu.register, tu.verified
    FROM teacher_details td
    JOIN teacher_user tu ON td.tid = tu.tid
    WHERE tu.verified != 'yes' OR tu.verified IS NULL
    ORDER BY tu.register ASC
";

$unverified_students = $conn->query($unverified_students_query);
$unverified_teachers = $conn->query($unverified_teachers_query);

/* ---------------- Count unverified users ---------------- */
$total_unverified = $unverified_students->num_rows + $unverified_teachers->num_rows;
?>

<div class="container">
    <h1>User Verification Management</h1>
    
    <!-- Summary Cards -->
    <div class="summary-cards">
        <div class="summary-card">
            <div class="card-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $total_unverified; ?></h3>
                <p>Total Unverified Users</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="card-icon">
                <i class="fas fa-user-graduate"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $unverified_students->num_rows; ?></h3>
                <p>Unverified Students</p>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="card-icon">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <div class="card-content">
                <h3><?php echo $unverified_teachers->num_rows; ?></h3>
                <p>Unverified Teachers</p>
            </div>
        </div>
    </div>
    
    <?php if ($total_unverified > 0): ?>
        <!-- Bulk Actions -->
        <div class="bulk-actions">
            <form method="post" action="admin.php#admin_user_verification.php" onsubmit="return confirmBulkAction('verify all unverified users')">
                <button type="submit" name="verify_all" class="btn btn-success btn-large">
                    <i class="fas fa-check-double"></i> Verify All Users
                </button>
            </form>
        </div>
        
        <!-- Verification Form -->
        <form method="post" action="admin_user_verification.php" onsubmit="return submitVerificationForm(event, this)">
            <div class="verification-section">
                <div class="section-header">
                    <h2>Unverified Users</h2>
                    <div class="select-all-container">
                        <label class="select-all-label">
                            <input type="checkbox" id="select-all-users" onchange="toggleAllUsers(this)">
                            Select All
                        </label>
                    </div>
                </div>
                
                <!-- Unverified Students -->
                <?php if ($unverified_students->num_rows > 0): ?>
                    <div class="user-category">
                        <h3><i class="fas fa-user-graduate"></i> Students (<?php echo $unverified_students->num_rows; ?>)</h3>
                        <div class="users-grid">
                            <?php 
                            // Reset pointer
                            $unverified_students->data_seek(0);
                            while ($student = $unverified_students->fetch_assoc()): 
                            ?>
                                <div class="user-card">
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php if (!empty($student['profilepic'])): ?>
                                                <img src="<?php echo $student['profilepic']; ?>" alt="Profile Picture">
                                            <?php else: ?>
                                                <i class="fas fa-user"></i>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="user-details">
                                            <h4><?php echo htmlspecialchars($student['name']); ?></h4>
                                            <p><strong>Username:</strong> <?php echo htmlspecialchars($student['user_name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($student['e_mail']); ?></p>
                                            <p><strong>Registered:</strong> <?php echo date('M d, Y', strtotime($student['register'])); ?></p>
                                            <?php if ($student['phone']): ?>
                                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($student['phone']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="user-actions">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="selected_users[]" value="student_<?php echo $student['sid']; ?>" class="user-checkbox">
                                            <span class="checkmark"></span>
                                            Select
                                        </label>
                                        
                                        <form method="post" action="admin_user_verification.php" style="display: inline;">
                                            <input type="hidden" name="user_type" value="student">
                                            <input type="hidden" name="user_id" value="<?php echo $student['sid']; ?>">
                                            <button type="submit" name="verify_user" class="btn btn-primary btn-sm">
                                                <i class="fas fa-check"></i> Verify
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Unverified Teachers -->
                <?php if ($unverified_teachers->num_rows > 0): ?>
                    <div class="user-category">
                        <h3><i class="fas fa-chalkboard-teacher"></i> Teachers (<?php echo $unverified_teachers->num_rows; ?>)</h3>
                        <div class="users-grid">
                            <?php 
                            // Reset pointer
                            $unverified_teachers->data_seek(0);
                            while ($teacher = $unverified_teachers->fetch_assoc()): 
                            ?>
                                <div class="user-card">
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php if (!empty($teacher['profilepic'])): ?>
                                                <img src="<?php echo $teacher['profilepic']; ?>" alt="Profile Picture">
                                            <?php else: ?>
                                                <i class="fas fa-user"></i>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="user-details">
                                            <h4><?php echo htmlspecialchars($teacher['name']); ?></h4>
                                            <p><strong>Username:</strong> <?php echo htmlspecialchars($teacher['user_name']); ?></p>
                                            <p><strong>Email:</strong> <?php echo htmlspecialchars($teacher['e_mail']); ?></p>
                                            <p><strong>Registered:</strong> <?php echo date('M d, Y', strtotime($teacher['register'])); ?></p>
                                            <?php if ($teacher['specialization']): ?>
                                                <p><strong>Specialization:</strong> <?php echo htmlspecialchars($teacher['specialization']); ?></p>
                                            <?php endif; ?>
                                            <?php if ($teacher['phone']): ?>
                                                <p><strong>Phone:</strong> <?php echo htmlspecialchars($teacher['phone']); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="user-actions">
                                        <label class="checkbox-label">
                                            <input type="checkbox" name="selected_users[]" value="teacher_<?php echo $teacher['tid']; ?>" class="user-checkbox">
                                            <span class="checkmark"></span>
                                            Select
                                        </label>
                                        
                                        <form method="post" action="admin_user_verification.php" style="display: inline;">
                                            <input type="hidden" name="user_type" value="teacher">
                                            <input type="hidden" name="user_id" value="<?php echo $teacher['tid']; ?>">
                                            <button type="submit" name="verify_user" class="btn btn-primary btn-sm">
                                                <i class="fas fa-check"></i> Verify
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Bulk Verify Selected -->
                <div class="bulk-verify-section">
                    <button type="submit" name="verify_selected" class="btn btn-warning btn-large">
                        <i class="fas fa-check-square"></i> Verify Selected Users
                    </button>
                </div>
            </div>
        </form>
    <?php else: ?>
        <div class="no-unverified">
            <i class="fas fa-check-circle" style="font-size: 4rem; color: #10b981; margin-bottom: 1rem;"></i>
            <h3>All Users Verified!</h3>
            <p>Great job! All users in the system have been verified.</p>
        </div>
    <?php endif; ?>
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
    
    .summary-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .summary-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .card-icon {
        font-size: 2.5rem;
        opacity: 0.9;
    }
    
    .card-content h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
    }
    
    .card-content p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
    }
    
    .bulk-actions {
        text-align: center;
        margin-bottom: 2rem;
        padding: 1.5rem;
        background: #f0f9ff;
        border-radius: 10px;
        border: 2px solid #bfdbfe;
    }
    
    .verification-section {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 2rem;
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e5e7eb;
    }
    
    .select-all-container {
        display: flex;
        align-items: center;
    }
    
    .select-all-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #374151;
        cursor: pointer;
    }
    
    .user-category {
        margin-bottom: 2rem;
    }
    
    .user-category h3 {
        color: #1f2937;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .users-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 1.5rem;
    }
    
    .user-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 1.5rem;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .user-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    }
    
    .user-info {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .user-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #f3f4f6;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        overflow: hidden;
    }
    
    .user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .user-avatar i {
        font-size: 1.5rem;
        color: #9ca3af;
    }
    
    .user-details {
        flex: 1;
    }
    
    .user-details h4 {
        margin: 0 0 0.5rem 0;
        color: #1f2937;
        font-size: 1.125rem;
    }
    
    .user-details p {
        margin: 0.25rem 0;
        color: #6b7280;
        font-size: 0.875rem;
    }
    
    .user-actions {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }
    
    .checkbox-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        font-weight: 500;
        color: #374151;
    }
    
    .checkmark {
        width: 18px;
        height: 18px;
        border: 2px solid #d1d5db;
        border-radius: 4px;
        position: relative;
        transition: all 0.3s;
    }
    
    .checkbox-label input[type="checkbox"] {
        display: none;
    }
    
    .checkbox-label input[type="checkbox"]:checked + .checkmark {
        background: #3b82f6;
        border-color: #3b82f6;
    }
    
    .checkbox-label input[type="checkbox"]:checked + .checkmark::after {
        content: '✓';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 12px;
        font-weight: bold;
    }
    
    .bulk-verify-section {
        text-align: center;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #e5e7eb;
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
    }
    
    .btn-large {
        padding: 1rem 2rem;
        font-size: 1rem;
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
    }
    
    .btn-success {
        background: #10b981;
        color: white;
    }
    
    .btn-success:hover {
        background: #059669;
    }
    
    .btn-warning {
        background: #f59e0b;
        color: white;
    }
    
    .btn-warning:hover {
        background: #d97706;
    }
    
    .no-unverified {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
    }
    
    .no-unverified h3 {
        margin: 1rem 0;
        color: #374151;
    }
    
    @media (max-width: 768px) {
        .container {
            margin: 1rem;
            padding: 1rem;
        }
        
        .summary-cards {
            grid-template-columns: 1fr;
        }
        
        .section-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }
        
        .users-grid {
            grid-template-columns: 1fr;
        }
        
        .user-actions {
            flex-direction: column;
            gap: 1rem;
            align-items: stretch;
        }
    }
</style>

<script>
function toggleAllUsers(selectAllCheckbox) {
    const userCheckboxes = document.querySelectorAll('.user-checkbox');
    userCheckboxes.forEach(checkbox => {
        checkbox.checked = selectAllCheckbox.checked;
    });
}

function submitVerificationForm(e, form) {
    const selectedUsers = form.querySelectorAll('input[name="selected_users[]"]:checked');
    if (selectedUsers.length === 0) {
        e.preventDefault();
        showAlert('Please select at least one user to verify.', 'error');
        return false;
    }
    
    return confirmBulkAction('verify ' + selectedUsers.length + ' selected user(s)');
}

function confirmBulkAction(action) {
    return confirm('Are you sure you want to ' + action + '? This action cannot be undone.');
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
