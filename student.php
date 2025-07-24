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
          <a href="#">Profile</a>
          <a href="#">Settings</a>
          <a href="#">Logout</a>
        </div>
      </div>
    </div>
  </nav>

  <!-- === MAIN CONTENT AREA === -->
  <main id="main-content" class="main-content">
    <h2>Welcome to your dashboard, <?php echo htmlspecialchars($name); ?>!</h2>
    <p>Click on the tabs above to explore your learning journey.</p>
  </main>

  <!-- === SCRIPTS === -->
  <script>
    // === Profile Drawer Toggle ===
    const pic = document.getElementById('profilePic');
    const drawer = document.getElementById('drawer');

    pic.addEventListener('click', () => {
      drawer.classList.toggle('open');
    });

    document.addEventListener('click', function (event) {
      if (!pic.contains(event.target) && !drawer.contains(event.target)) {
        drawer.classList.remove('open');
      }
    });

    // === Link Click Handler ===
    document.querySelectorAll('.nav-links a').forEach(link => {
      link.addEventListener('click', (e) => {
        e.preventDefault();
        const page = link.getAttribute('data-page');
        loadContent(page, link, true);
      });
    });

    // === Load Content Dynamically ===
    function loadContent(page, element, push = true) {
      fetch(page)
        .then(response => response.text())
        .then(data => {
          document.getElementById('main-content').innerHTML = data;

          if (push) {
            window.history.pushState({ page: page }, '', `?page=${page}`);
          }

          document.querySelectorAll('.nav-links a').forEach(link => {
            link.classList.remove('active');
          });

          if (element) {
            element.classList.add('active');
          } else {
            const autoLink = Array.from(document.querySelectorAll('.nav-links a'))
              .find(link => link.getAttribute('data-page') === page);
            if (autoLink) autoLink.classList.add('active');
          }
        })
        .catch(error => {
          document.getElementById('main-content').innerHTML = '<p>Error loading content.</p>';
          console.error(error);
        });
    }

    // === Handle Browser Back/Forward ===
    window.addEventListener('popstate', event => {
      const page = (event.state && event.state.page) || 'student_home.php';
      loadContent(page, null, false);
    });

    // === Initial Load Based on URL ===
    window.addEventListener('DOMContentLoaded', () => {
      const params = new URLSearchParams(window.location.search);
      const page = params.get('page') || 'student_home.php';
      loadContent(page, null, false);
    });
  </script>
</body>

</html>
