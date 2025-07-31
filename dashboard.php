<html>

<head>
  <title>
    dashboard
  </title>
  <link href="dashboard.css" rel="stylesheet">
</head>

<body>
  <div style="padding: 20px;">
    <?php
    session_start();
    $id = $_SESSION['id'];
    $conn = new mysqli("localhost", "root", "amen", "mini");
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    } else {
      $sql = "SELECT * FROM adminnex WHERE aid = $id";
      $result = $conn->query($sql);
      $row = $result->fetch_assoc();
      $name = $row["user_name"];
    }
    $coursecount = " select count(*) as number from course;";
    $c_count = $conn->query($coursecount);
    $crow = $c_count->fetch_assoc();
    $count = $crow['number'];
    ?>
    <h1 style="color: goldenrod;">🌟 Hello, <?php echo $name; ?>!</h1><br>
    <div class="row">
      <div class="card" style="height: 220x; width:250px;">
        <p style="font-size:20px;
    padding-bottom: 6px;">Courses</p>


        <img src="images/course.png" height="100px" width="100px">
        <br>

        <p style="font: weight 500px; font-size: 25px;"><?php
        echo $count; ?></p>
      </div>

      <div class="card">
        <p style="font-size:20px">Students</p>
        <img src="images/studentcount.png" height="100px" width="100px">
      </div>
      <div class="card"></div>
    </div>

  </div>



</body>


</html>