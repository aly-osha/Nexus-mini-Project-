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

  <style>
    .profile-section {
      position: relative;
      margin-bottom: 20px;
      text-align: center;
    }

    .profile-section img {
      border-radius: 50%;
      width: 50px;
      height: 50px;
      cursor: pointer;
    }

    .profile-section p {
      cursor: pointer;
      margin: 5px 0;
      color: #fff;
    }

    .profile-drawer {
      display: none;
      position: absolute;
      top: 70px; /* just below profile */
      left: 50%;
      transform: translateX(-50%);
      background: #fff;
      color: #333;
      border-radius: 12px;
      box-shadow: 0 6px 15px rgba(0,0,0,0.2);
      width: 180px;
      text-align: left;
      z-index: 1000;
      padding: 10px 0;
    }

    .profile-drawer a {
      display: block;
      padding: 10px 15px;
      text-decoration: none;
      color: #333;
      font-weight: 500;
    }

    .profile-drawer a:hover {
      background: #f2f2f2;
    }
  </style>
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
?>
  <div class="sidebar">
    <h2>Welcome</h2>

    <!-- Profile Section with Drawer -->
    <div class="profile-section">
      <img src="<?php echo $row['filepath'];?>" id="profile-toggle">
      <p id="profile-name"><?php echo $name; ?></p>

      <div id="profile-drawer" class="profile-drawer">
        <a href="#settings" onclick="loadPage('settings')">⚙️ Settings</a>
        <a href="logout.php"> Logout</a>
      </div>
    </div>

    <p style="margin-bottom: 20px; color: #9cb1d6;" id="sidebar-user"></p>
    <a href="#dashboard" class="active" data-page="dashboard">Dashboard</a>
    <a href="#users" data-page="users">Users</a>
    <a href="#courses" data-page="courses">Courses</a>
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
