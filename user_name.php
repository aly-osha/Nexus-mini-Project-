<?php
// DB connection variables
$host = "localhost";
$user = "root";
$pass = "amen";
$db = "mini";

// Create DB connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get the username from POST
$uname = trim($_POST['uname'] ?? '');

if ($uname === '') {
    echo " No username received.";
    exit;
}

// Check if the username exists in any table
$sql = "
    SELECT user_name FROM adminnex WHERE user_name = ?
    UNION
    SELECT user_name FROM student_user WHERE user_name = ?
    UNION
    SELECT user_name FROM teacher_user WHERE user_name = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $uname, $uname, $uname);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo "already taken";
} else {
    echo "available";
}

$stmt->close();
$conn->close();
?>
