<?php
session_start();
$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle user verification
if (isset($_POST['verify_user'])) {
    $user_type = $_POST['user_type'];
    $user_id = intval($_POST['user_id']);
    
    if ($user_type == 'student') {
        $conn->query("UPDATE student_user SET verified='yes' WHERE sid=$user_id");
    } else if ($user_type == 'teacher') {
        $conn->query("UPDATE teacher_user SET verified='yes' WHERE tid=$user_id");
    }
    
    echo "<script>showAlert('User verified successfully!', 'success');</script>";
}

// Get pending users
$pending_students = $conn->query("
    SELECT su.*, sd.name, sd.e_mail 
    FROM student_user su 
    JOIN student_details sd ON su.sid = sd.sid 
    WHERE su.verified = 'NUL' OR su.verified IS NULL
    ORDER BY sd.name
");

$pending_teachers = $conn->query("
    SELECT tu.*, td.name, td.e_mail 
    FROM teacher_user tu 
    JOIN teacher_details td ON tu.tid = td.tid 
    WHERE tu.verified = 'NUL' OR tu.verified IS NULL
    ORDER BY td.name
");
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 style="color: #2c3e50;">👥 User Management</h1>
            <p style="color: #7f8c8d;">Manage user registrations and verify new accounts.</p>
        </div>
    </div>

    <!-- Pending Students -->
    <?php if ($pending_students->num_rows > 0): ?>
        <div class="table-container mb-4">
            <h3>📚 Pending Student Registrations</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($student = $pending_students->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['name']); ?></td>
                            <td><?php echo htmlspecialchars($student['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['e_mail']); ?></td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="user_type" value="student">
                                    <input type="hidden" name="user_id" value="<?php echo $student['sid']; ?>">
                                    <button type="submit" name="verify_user" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Verify
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Pending Teachers -->
    <?php if ($pending_teachers->num_rows > 0): ?>
        <div class="table-container mb-4">
            <h3>🎓 Pending Teacher Registrations</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($teacher = $pending_teachers->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($teacher['name']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($teacher['e_mail']); ?></td>
                            <td>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="user_type" value="teacher">
                                    <input type="hidden" name="user_id" value="<?php echo $teacher['tid']; ?>">
                                    <button type="submit" name="verify_user" class="btn btn-success btn-sm">
                                        <i class="fas fa-check"></i> Verify
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- No Pending Users -->
    <?php if ($pending_students->num_rows == 0 && $pending_teachers->num_rows == 0): ?>
        <div class="text-center py-5">
            <i class="fas fa-check-circle" style="font-size: 4rem; color: #27ae60; margin-bottom: 1rem;"></i>
            <h3 style="color: #7f8c8d;">All caught up!</h3>
            <p style="color: #95a5a6;">No pending user registrations at this time.</p>
        </div>
    <?php endif; ?>
</div>

<style>
.table-container {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.table-container h3 {
    color: #2c3e50;
    margin-bottom: 1rem;
}

.table {
    width: 100%;
    border-collapse: collapse;
}

.table th,
.table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ecf0f1;
}

.table th {
    background-color: #34495e;
    color: white;
    font-weight: 600;
}

.table tr:hover {
    background-color: #f8f9fa;
}

.btn {
    padding: 6px 12px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 0.8rem;
    transition: all 0.3s;
    text-decoration: none;
    display: inline-block;
}

.btn-success {
    background-color: #27ae60;
    color: white;
}

.btn-success:hover {
    background-color: #229954;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.75rem;
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

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
}

.text-center {
    text-align: center !important;
}
</style>

<script>
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

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">