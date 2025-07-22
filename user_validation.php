<?php
//staring session
session_start();
//connection variables
$user = "root";
$pass = "amen";
$db = "mini";
$host = "localhost";
//form data

$uname = $_REQUEST['uname'];
$pword = $_REQUEST['pword'];
//establising connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//query to check for user
$validate = "select sid ,'student' as role from student_user where user_name='$uname' and password='$pword' and verified='yes' UNION select tid ,'teacher' as role from teacher_user where user_name='$uname' and password='$pword' and verified='yes'  union select aid ,'admin' as role from adminnex where user_name='$uname' and password='$pword'";
if ($result = $conn->query($validate)) {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role = $row['role'];
        
     
        //redirection
        if ($role == "teacher") {
              $_SESSION['id'] = $row['tid'];
            header("location:teacher.html");
        } else if ($role == "student") {
              $_SESSION['id'] = $row['sid'];
            header("location:student.php");
        } else if ($role == "admin") {
            $_SESSION['id'] = $row['aid'];
            header("location:admin.php");
        }
    } else {
        echo "invalid login credisntianls";
    }
} else {
    die("error");
}
$conn->close();
?>