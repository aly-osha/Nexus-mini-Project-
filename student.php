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
  $sid = $_SESSION['id'];
  $conn = new mysqli("localhost", "root", "amen", "mini");
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  } else {
    $sql = "SELECT * FROM student_details WHERE sid = $sid";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $name = $row["name"];
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
        <li><a href="?page=my_learning.php" data-page="my_learning.php">My Learning</a></li>
        <li><a href="?page=my_uploads.html" data-page="my_uploads.html">My Uploads</a></li>
      </ul>
    </div>

    <div class="nav-right">
      <div class="column">
        <img src="images/person-and-floating-shapes.webp" alt="Profile" class="profile-pic" id="profilePic">
        <span><?php echo htmlspecialchars($name); ?></span>
        <div class="profile-drawer" id="drawer">
          <a href="student_profile.php">Profile</a>
          <a href="student_settings.php">Settings</a>
          <a href="#">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- === MAIN CONTENT AREA  <main> tag is an HTML5 semantic element that represents the primary content of a webpage
     — the content that is directly related to or expands upon the central topic of the document. 
     It is intended to contain content that is unique to the page and not repeated across other pages
     -->
  <main id="main-content" class="main-content">
    <h2>Welcome to your dashboard, <?php echo htmlspecialchars($name); ?>!</h2>
    <p>Click on the tabs above to explore your learning journey.</p>
  </main>


  <script src="student.js">

  </script>
</body>

</html>