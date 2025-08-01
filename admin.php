<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js/dist/Chart.min.css">
   <link href="admin.css" rel="stylesheet" >
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
    <?php 
    echo $name;
    ?>
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
  </script>
</body>
</html>
