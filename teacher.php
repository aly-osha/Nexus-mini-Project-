<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Teacher Dashboard</title>
  <link rel="stylesheet" href="teacher.css" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
  <?php
  session_start();
  $tid = $_SESSION['id'];
  $conn = new mysqli("localhost", "root", "amen", "mini");
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  } else {
    $sql = "SELECT * FROM teacher_details WHERE tid = $tid";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $name = $row["name"];
  }
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
        <li><a href="?page=teacher_home.php" data-page="teacher_home.php">Dashboard</a></li>
        <li><a href="?page=teacher_assignment_management.php" data-page="teacher_assignment_management.php">Assignments</a></li>
        <li><a href="?page=teacher_students.php" data-page="teacher_students.php">Students</a></li>
        <li><a href="?page=teacher_materials.php" data-page="teacher_materials.php">Materials</a></li>
      </ul>
    </div>

    <div class="nav-right">
      <div class="column">
        <img src="<?php echo $row['profilepic'] ?? 'images/signup-image.jpg.png';?>" alt="Profile" class="profile-pic" id="profilePic">
        <span><?php echo htmlspecialchars($name); ?></span>
        <div class="profile-drawer" id="drawer">
          <a href="teacher_profile.php">Profile</a>
          <a href="teacher_settings.php">Settings</a>
          <form action="teacher.php" method="post">          
            <button type="submit" name="logout">Logout</button>
          </form>
        </div>
      </div>
    </div>
  </nav>

  <!-- === MAIN CONTENT AREA === -->
  <main id="main-content" class="main-content">
  </main>

  <script src="teacher.js"></script>
</body>

</html>