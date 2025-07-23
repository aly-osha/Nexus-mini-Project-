<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard</title>
  <link rel="stylesheet" href="student.css" />
</head>
<body>
  <nav class="navbar">
    <div class="nav-left">
      <h1 class="logo"><img src="images/loginboxjpg.png" height="100px" width="200px"></h1>
      <ul class="nav-links">
        <li><a href="#">Home</a></li>
        <li><a href="#">My Learning</a></li>
        <li><a href="#">My Uploads</a></li>
      </ul>
    </div>
    <div class="nav-right">
      <img src="https://via.placeholder.com/40" alt="Profile" class="profile-pic" id="profilePic">
      <div class="profile-drawer" id="drawer">
        <a href="#">Profile</a>
        <a href="#">Settings</a>
        <a href="#">Logout</a>
      </div>
    </div>
  </nav>

  <script>
    const pic = document.getElementById('profilePic');
    const drawer = document.getElementById('drawer');
    
    pic.addEventListener('click', () => {
      drawer.classList.toggle('open');
    });

    // Optional: Click outside to close drawer
    document.addEventListener('click', function (event) {
      if (!pic.contains(event.target) && !drawer.contains(event.target)) {
        drawer.classList.remove('open');
      }
    });
  </script>
</body>
</html>
