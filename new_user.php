<?php
//connection variables
$user = "root";
$pass = "amen";
$db = "mini";
$host = "localhost";
//getting form data
$name = $_REQUEST['name'];
$email = $_REQUEST['email'];
$dob = $_REQUEST['dob'];
$addres = $_REQUEST['address'];
$pword = $_REQUEST['repword'];
$uname = $_REQUEST['uname'];
$role = $_REQUEST['role'];
//establishing connection
$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
//quries 
$teacher = "insert into teacher_details(name,e_mail,cid,dob,address) values('$name','$email',0,'$dob','$addres')";
$student = "insert into student_details(name,e_mail,cid,dob,address) values('$name','$email',0,'$dob','$addres')";

if ($role == "teacher") {
    if ($conn->query($teacher)) {
        $fetcht = " select * from teacher_details order by tid desc limit 1";
        //fetch tid from teacher_details
        $result = $conn->query($fetcht);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $tid = $row['tid'];
            $teachu = "insert into teacher_user values('$uname','$pword','$tid','NULL')";
            if ($conn->query($teachu)) {
                    header("location:login.html");
            }
            else{
                echo "inserintg into user table error";
            }
        }
        else{
            echo "no rows";
        }
    } else {
        echo "error";
    }
} else {
    if ($conn->query($student)) {
        $fetchs = " select * from student_details order by sid desc limit 1";
        //fetch sid from teacher_details
        $result = $conn->query($fetchs);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $sid = $row['sid'];
            $studentu = "insert into student_user values('$uname','$pword','$sid','NULL')";
            if ($conn->query($studentu)) {
                    header("location:login.html");
            }
        }
    } else {
        echo "error";
    }

}
?>