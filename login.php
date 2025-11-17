<!DOCTYPE html>
<html>

<head>
    <title>
        BUild the nexus
    </title>
    <link href="https://fonts.googleapis.com/css?family=Raleway:400,700" rel="stylesheet">
  <link href="login.css" rel="stylesheet" >
</head>

<body>
    <div class="container" id="container">
    <button id="loginBtn"><img src="images/signup-image.jpg.png" width="120px" height="100px"></button>
    <div class="top"></div>
    <div class="bottom"></div>
    <div class="center" id="loginBox">
      <img src="images/loginboxjpg.png" width="285px" height="120px">
      <form action="login.php" method="post" name="f1">
        user name:<input type="text" name="uname"><br>
        password:<input type="password" name="pword"><br>
        <input type="submit" name="s">
    </form>
    <a href="new_user.html">new around here ?create a Nexus</a>
    </div>
  </div>
  
  
  <script>
    const loginBtn = document.getElementById("loginBtn");
    const container = document.getElementById("container");
    const loginBox = document.getElementById("loginBox");

    // Show login box on button click
    loginBtn.addEventListener("click", function () {
      container.classList.add("show");
    });

    // Hide login box when mouse leaves the box
    loginBox.addEventListener("mouseleave", function () {
      container.classList.remove("show");
    });
  </script>
</body>

</html>
  <?php
if(isset($_POST['s'])){
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
        echo "<script>
        alert('Invalid username or password or you are not yet verified');
        window.location.href = 'login.php';
      </script>";  }
    die("error");
}

}
?>