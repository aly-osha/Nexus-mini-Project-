<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard</title>
  <link rel="stylesheet" href="student.css" />
</head>

<body>
  <?php
  session_start();
  if (!isset($_SESSION['id'])) {
      header('Location: login.html');
      exit;
  }
  $sid = (int) $_SESSION['id'];

  $conn = new mysqli("localhost", "root", "amen", "mini");
  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
  }

 
  $stmt = $conn->prepare("SELECT name, profilepic FROM student_details WHERE sid = ?");
  $stmt->bind_param('i', $sid);
  $stmt->execute();
  $res = $stmt->get_result();
  $row = $res->fetch_assoc();

  $name = $row['name'] ?? 'Student';
  $profilepic = $row['profilepic'] ?? 'images/signup-image.jpg.png';

  if(isset($_POST['logout'])){
    session_destroy();
    header('location:login.html');
  }
  ?>

  <!-- === NAVIGATION BAR === -->
  <nav class="navbar">
    <div class="nav-left">
      <h1 class="logo">
        <img src="images/loginboxjpg.png" height="100px" width="200px" alt="Logo">
      </h1>
      <ul class="nav-links">
        <li><a href="?page=student_home.php" data-page="student_home.php">Home</a></li>
        <li><a href="?page=my_learning_new.php" data-page="my_learning_new.php">My Learning</a></li>
        <li><a href="?page=student_assignments.php" data-page="student_assignments.php">Assignments</a></li>
        <li><a href="?page=student_course_enrollment.php" data-page="student_course_enrollment.php">Browse Courses</a></li>
      </ul>
    </div>

    <div class="nav-right">
      <div class="column">
       <img src="<?php echo htmlspecialchars($profilepic); ?>" alt="Profile" class="profile-pic" id="profilePic">
<span><?php echo htmlspecialchars($name); ?></span>


        <div class="profile-drawer" id="drawer">
          <a href="student_profile.php">Profile</a>
          <a href="student_settings.php">Settings</a>
<form action="student.php" method="post">          <button type="submit" name="logout">Logout</button></form>
        </div>
      </div>
    </div>
  </nav>

  <!-- === MAIN CONTENT AREA  <main> tag is an HTML5 semantic element that represents the primary content of a webpage
     — the content that is directly related to or expands upon the central topic of the document. 
     It is intended to contain content that is unique to the page and not repeated across other pages
     -->
  <main id="main-content" class="main-content">
  </main>


  <script src="student.js">
  </script>
</body>

</html>