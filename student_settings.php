<?php
session_start();
$sid = $_SESSION['id'];
$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Handle password update
if (isset($_POST['update_password'])) {
    $old_password = mysqli_real_escape_string($conn, $_POST['old_password']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    // Get current password
    $user_result = $conn->query("SELECT su.password FROM student_user su WHERE su.sid = $sid");
    $user = $user_result->fetch_assoc();
    
    if ($old_password === $user['password']) {
        if ($new_password === $confirm_password) {
            $update_sql = "UPDATE student_user SET password='$new_password' WHERE sid=$sid";
            if ($conn->query($update_sql)) {
                echo "<script>showAlert('Password updated successfully!', 'success');</script>";
            } else {
                echo "<script>showAlert('Error updating password: " . $conn->error . "', 'error');</script>";
            }
        } else {
            echo "<script>showAlert('New passwords do not match!', 'error');</script>";
    }
  } else {
        echo "<script>showAlert('Current password is incorrect!', 'error');</script>";
    }
}

// Handle username update
if (isset($_POST['update_username'])) {
    $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
    
    // Check if username already exists
    $check_username = $conn->query("SELECT user_name FROM student_user WHERE user_name='$new_username' AND sid != $sid");
    
    if ($check_username->num_rows == 0) {
        $update_sql = "UPDATE student_user SET user_name='$new_username' WHERE sid=$sid";
        if ($conn->query($update_sql)) {
            echo "<script>showAlert('Username updated successfully!', 'success');</script>";
        } else {
            echo "<script>showAlert('Error updating username: " . $conn->error . "', 'error');</script>";
        }
    } else {
        echo "<script>showAlert('Username already exists! Please choose a different one.', 'error');</script>";
    }
}

// Handle account deletion
if (isset($_POST['delete_account'])) {
    $confirm_delete = mysqli_real_escape_string($conn, $_POST['confirm_delete']);
    
    if (strtolower($confirm_delete) === 'delete') {
        // Delete user data
        $conn->query("DELETE FROM submissions WHERE student_id = $sid");
        $conn->query("DELETE FROM enrollments WHERE student_id = $sid");
        $conn->query("DELETE FROM student_user WHERE sid = $sid");
        $conn->query("DELETE FROM student_details WHERE sid = $sid");
        
        session_destroy();
        echo "<script>alert('Account deleted successfully!'); window.location.href='login.php';</script>";
    exit();
  } else {
        echo "<script>showAlert('Please type DELETE to confirm account deletion.', 'error');</script>";
    }
}

// Get student and user details
$student_result = $conn->query("
    SELECT sd.*, su.user_name 
    FROM student_details sd 
    JOIN student_user su ON sd.sid = su.sid 
    WHERE sd.sid = $sid
");
$student = $student_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Settings</title>
    <link rel="stylesheet" href="student.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f4f4;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 2rem;
        }
        
        .settings-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .settings-header {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .settings-content {
            padding: 2rem;
        }
        
        .settings-section {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            border-left: 4px solid #3498db;
        }
        
        .settings-section h3 {
            color: #2c3e50;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-warning {
            background-color: #f39c12;
            color: white;
        }
        
        .btn-warning:hover {
            background-color: #e67e22;
        }
        
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .btn-secondary {
            background-color: #95a5a6;
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #7f8c8d;
        }
        
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #3498db;
            text-decoration: none;
            margin-bottom: 2rem;
            font-weight: 500;
        }
        
        .back-link:hover {
            color: #2980b9;
        }
        
        .danger-zone {
            border-left-color: #e74c3c;
            background: #fdf2f2;
        }
        
        .danger-zone h3 {
            color: #e74c3c;
        }
        
        .warning-text {
            color: #e74c3c;
            font-size: 0.9rem;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #fff5f5;
            border-radius: 6px;
            border: 1px solid #fed7d7;
        }
        
        .current-info {
            background: #e8f5e8;
            padding: 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
            border: 1px solid #c6f6c6;
        }
        
        .current-info strong {
            color: #27ae60;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .settings-header {
                padding: 1.5rem;
            }
            
            .settings-content {
                padding: 1rem;
            }
            
            .settings-section {
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="settings-container">
        <div class="settings-header">
            <h1><i class="fas fa-cog"></i> Account Settings</h1>
            <p>Manage your account preferences and security</p>
      </div>
        
        <div class="settings-content">
            <a href="javascript:history.back()" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <!-- Username Settings -->
            <div class="settings-section">
                <h3><i class="fas fa-user"></i> Username</h3>
                <div class="current-info">
                    <strong>Current Username:</strong> <?php echo htmlspecialchars($student['user_name']); ?>
                </div>
                <form method="post">
            <div class="form-group">
                        <label for="new_username">New Username</label>
                        <input type="text" id="new_username" name="new_username" required 
                               placeholder="Enter new username">
                    </div>
                    <button type="submit" name="update_username" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Username
                    </button>
                </form>
            </div>

            <!-- Password Settings -->
            <div class="settings-section">
                <h3><i class="fas fa-lock"></i> Password</h3>
                <form method="post">
                    <div class="form-group">
                        <label for="old_password">Current Password</label>
                        <input type="password" id="old_password" name="old_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required 
                               minlength="6" placeholder="Minimum 6 characters">
                    </div>
            <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" name="update_password" class="btn btn-warning">
                        <i class="fas fa-key"></i> Update Password
                    </button>
                </form>
            </div>

            <!-- Privacy & Data -->
            <div class="settings-section">
                <h3><i class="fas fa-shield-alt"></i> Privacy & Data</h3>
                <p style="color: #7f8c8d; margin-bottom: 1rem;">
                    Your data is securely stored and only used for educational purposes. 
                    You can request a copy of your data or delete your account at any time.
                </p>
                <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                    <a href="student_profile.php" class="btn btn-primary">
                        <i class="fas fa-user-edit"></i> Edit Profile
                    </a>
                    <button onclick="exportData()" class="btn btn-secondary">
                        <i class="fas fa-download"></i> Export My Data
                    </button>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="settings-section danger-zone">
                <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                <div class="warning-text">
                    <strong>Warning:</strong> Deleting your account is permanent and cannot be undone. 
                    All your course progress, submissions, and data will be permanently removed.
                </div>
                <form method="post" onsubmit="return confirmDelete()">
                    <div class="form-group">
                        <label for="confirm_delete">Type "DELETE" to confirm account deletion</label>
                        <input type="text" id="confirm_delete" name="confirm_delete" 
                               placeholder="Type DELETE to confirm">
                    </div>
                    <button type="submit" name="delete_account" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Delete Account Permanently
                    </button>
          </form>
        </div>
      </div>
    </div>

    <script>
        // Show alert function
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
                success: '#27ae60',
                error: '#e74c3c',
                warning: '#f39c12',
                info: '#3498db'
            };
            
            alertDiv.style.backgroundColor = colors[type] || colors.info;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 3000);
        }
        
        // Confirm password match
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Confirm account deletion
        function confirmDelete() {
            const confirmText = document.getElementById('confirm_delete').value;
            if (confirmText.toUpperCase() !== 'DELETE') {
                showAlert('Please type "DELETE" exactly to confirm account deletion.', 'error');
                return false;
            }
            
            return confirm('Are you absolutely sure you want to delete your account? This action cannot be undone!');
        }
        
        // Export data function
        function exportData() {
            showAlert('Data export feature will be available soon!', 'info');
            // In a real implementation, this would generate and download a data export
        }
    </script>
</body>
</html>