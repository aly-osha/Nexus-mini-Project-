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
$validate = "select sid as user_id ,'student' as role from student_user where user_name='$uname' and password='$pword' and verified='yes' UNION select tid as user_id,'teacher' as role from teacher_user where user_name='$uname' and password='$pword' and verified='yes'  union select aid as user_id,'admin' as role from adminnex where user_name='$uname' and password='$pword'";
if ($result = $conn->query($validate)) {
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $role = $row['role'];

        $_SESSION['id'] = $row['user_id'];
        //redirection
        if ($role == "teacher") {

            header("location:teacher.php");
        } else if ($role == "student") {

            header("location:student.php");
        } else if ($role == "admin") {

            header("location:admin.php");
        }
    } else {
        echo "invalid login credisntianls";
    }
} else {
    die("error");
}

?>