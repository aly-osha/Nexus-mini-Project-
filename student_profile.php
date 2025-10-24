<?php
session_start();
$sid = $_SESSION['id'];
$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle profile update
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    
    $update_sql = "UPDATE student_details SET name='$name', e_mail='$email', phone='$phone', address='$address', dob='$dob' WHERE sid=$sid";
    
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        // Get old profile pic to delete
        $old_pic_result = $conn->query("SELECT profilepic FROM student_details WHERE sid=$sid");
        $old_pic = $old_pic_result->fetch_assoc()['profilepic'];
        
        $targetDir = "uploads/profiles/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES["profile_pic"]["name"]);
        $filePath = $targetDir . time() . "_" . $fileName;
        
        if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $filePath)) {
            $update_sql = "UPDATE student_details SET name='$name', e_mail='$email', phone='$phone', address='$address', dob='$dob', profilepic='$filePath' WHERE sid=$sid";
            
            // Delete old profile pic
            if ($old_pic && file_exists($old_pic)) {
                unlink($old_pic);
            }
        }
    }
    
    if ($conn->query($update_sql)) {
        echo "<script>showAlert('Profile updated successfully!', 'success');</script>";
    } else {
        echo "<script>showAlert('Error updating profile: " . $conn->error . "', 'error');</script>";
    }
}

// Get student details
$student_result = $conn->query("SELECT * FROM student_details WHERE sid = $sid");
$student = $student_result->fetch_assoc();

// Get enrollment statistics
$total_courses = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE student_id = $sid")->fetch_assoc()['count'];
$completed_courses = $conn->query("SELECT COUNT(*) as count FROM enrollments WHERE student_id = $sid AND status = 'completed'")->fetch_assoc()['count'];
$assignments_submitted = $conn->query("SELECT COUNT(*) as count FROM submissions WHERE student_id = $sid")->fetch_assoc()['count'];
$average_grade = $conn->query("SELECT AVG(grade) as avg_grade FROM submissions WHERE student_id = $sid AND grade IS NOT NULL")->fetch_assoc()['avg_grade'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link rel="stylesheet" href="student.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f4f4;
            font-family: 'Segoe UI', sans-serif;
            margin: 0;
            padding: 2rem;
        }
        
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .profile-header {
            background: linear-gradient(135deg, #3498db, #2ecc71);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .profile-pic-container {
            position: relative;
            display: inline-block;
            margin-bottom: 1rem;
        }
        
        .profile-pic-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid white;
            object-fit: cover;
        }
        
        .profile-content {
            padding: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #3498db;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #3498db;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 0.9rem;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 2rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
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
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #bdc3c7;
            border-radius: 6px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }
        
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
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
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .profile-header {
                padding: 1.5rem;
            }
            
            .profile-content {
                padding: 1rem;
            }
            
            .form-section {
                padding: 1rem;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-pic-container">
                <img src="<?php echo $student['profilepic'] ?: 'images/signup-image.jpg.png'; ?>" 
                     alt="Profile Picture" class="profile-pic-large">
            </div>
            <h1><?php echo htmlspecialchars($student['name']); ?></h1>
            <p>Student ID: <?php echo $student['sid']; ?></p>
            <p>Member since: <?php echo date('F Y', strtotime($student['register'])); ?></p>
        </div>
        
        <div class="profile-content">
            <a href="javascript:history.back()" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $total_courses; ?></div>
                    <div class="stat-label">Enrolled Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $completed_courses; ?></div>
                    <div class="stat-label">Completed Courses</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $assignments_submitted; ?></div>
                    <div class="stat-label">Assignments Submitted</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $average_grade ? number_format($average_grade, 1) : 'N/A'; ?></div>
                    <div class="stat-label">Average Grade</div>
                </div>
            </div>
            
            <!-- Profile Form -->
            <div class="form-section">
                <h3>Edit Profile Information</h3>
                <form method="post" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="profile_pic">Profile Picture</label>
                        <input type="file" id="profile_pic" name="profile_pic" accept="image/*">
                        <small style="color: #7f8c8d;">Choose a new profile picture (optional)</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name *</label>
                            <input type="text" id="name" name="name" required 
                                   value="<?php echo htmlspecialchars($student['name']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($student['e_mail']); ?>">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($student['phone']); ?>">
                        </div>
                        <div class="form-group">
                            <label for="dob">Date of Birth</label>
                            <input type="date" id="dob" name="dob" 
                                   value="<?php echo $student['dob']; ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"><?php echo htmlspecialchars($student['address']); ?></textarea>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                        <a href="javascript:history.back()" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Show alert function (if not already defined)
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
    </script>
</body>
</html>