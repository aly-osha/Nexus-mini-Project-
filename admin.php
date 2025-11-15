<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/Chart.min.css">
  <link href="admin.css" rel="stylesheet" >
  <link href="images/signup-image.jpg.png" rel="icon" type="image/x-icon">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://code.jquery.com/jquery-1.10.2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/js/bootstrap.bundle.min.js"></script>

 
</head>
<body>
<?php
  session_start();
  $id=$_SESSION['id'];
  $conn = new mysqli("localhost", "root", "amen", "mini");
  if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  } else {
    $sql = "SELECT * FROM adminnex WHERE aid = $id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $name = $row["user_name"];
  }
  if (isset($_POST["logoutbutt"])) {
  session_unset();    
session_destroy();   
header("location:login.html");
exit();
}
?>
  <div class="sidebar">
    <h2>Welcome</h2>

    <!-- Profile Section with Drawer -->
    <div class="profile-section">
      <img src="<?php echo $row['filepath'];?>" id="profile-toggle">
      <p id="profile-name"><?php echo $name; ?></p>

      <div id="profile-drawer" class="profile-drawer">
        <button  class="settingsbutt" onclick="loadPage('settings')">Settings</button>
        
        <form action="settings.php" method="post">
        <button type="submit" class="logoutbuttt" name="logoutbutt">Logout</button>
      </form>
      </div>
    </div>

    <p style="margin-bottom: 20px; color: #9cb1d6;" id="sidebar-user"></p>
    <a href="#dashboard" class="active" data-page="dashboard">Dashboard</a>
    <a href="#users" data-page="users">Users</a>
    <a href="#courses" data-page="courses">Courses</a>
    <a href="#course_assignment" data-page="admin_course_assignment">Course Assignment</a>
    <a href="#user_verification" data-page="admin_user_verification">User Verification</a>
    <a href="#settings" data-page="settings">Settings</a>
  </div>

  <div class="main-content" id="main-content">
    <!-- Content will be loaded dynamically -->
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    function loadPage(page, pushState = true) {
      fetch(`${page}.php`)
        .then(res => res.text())
        .then(html => {
          document.getElementById('main-content').innerHTML = html;
          document.querySelectorAll('.sidebar a').forEach(a => {
            a.classList.toggle('active', a.dataset.page === page);
          });
        });

      if (pushState) {
        history.pushState({ page }, '', '#' + page);
      }
    }

    window.addEventListener('DOMContentLoaded', () => {
      const page = location.hash.substring(1) || 'dashboard';
      loadPage(page, false);
    });

    window.addEventListener('popstate', (e) => {
      const page = e.state?.page || 'dashboard';
      loadPage(page, false);
    });

    document.querySelectorAll('.sidebar a').forEach(a => {
      a.addEventListener('click', (e) => {
        e.preventDefault();
        loadPage(a.dataset.page);
      });
    });

    function loadUserPage(page, btn) {
      fetch(page)
        .then(res => res.text())
        .then(html => {
          document.getElementById("user-content").innerHTML = html;
        })
        .catch(err => {
          document.getElementById("user-content").innerHTML =
            "<p style='color:red'>Error loading " + page + ": " + err.message + "</p>";
          console.error(err);
        });

      document.querySelectorAll(".tab-btn").forEach(b => b.classList.remove("active"));
      if (btn) btn.classList.add("active");
    }

    // Profile Drawer Toggle
    document.addEventListener("DOMContentLoaded", () => {
      const profileToggle = document.getElementById("profile-toggle");
      const profileName = document.getElementById("profile-name");
      const drawer = document.getElementById("profile-drawer");

      function toggleDrawer() {
        drawer.style.display = drawer.style.display === "block" ? "none" : "block";
      }

      profileToggle.addEventListener("click", toggleDrawer);
      profileName.addEventListener("click", toggleDrawer);

      document.addEventListener("click", (e) => {
        if (!drawer.contains(e.target) && !profileToggle.contains(e.target) && !profileName.contains(e.target)) {
          drawer.style.display = "none";
        }
      });
    });
 
  </script>
</body>
</html>
