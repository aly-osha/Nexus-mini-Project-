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
    $studentcount = " select count(*) as number from student_user;";
    $s_count = $conn->query($studentcount);
    $srow = $s_count->fetch_assoc();
    $scount = $srow['number'];
    $teachercount = " select count(*) as number from student_user;";
    $t_count = $conn->query($teachercount);
    $trow = $t_count->fetch_assoc();
    $tcount = $trow['number'];
    ?>
    <h1 style="color: goldenrod;">🌟 Hello, <?php echo $name; ?>!</h1><br>
    <div class="row">
      <div class="card">
        <p>Courses</p>
        <img src="images/course.png" height="100px" width="100px">
        <br>
        <?php
        echo $count; ?>
      </div>
      <div class="card">
        <p>Students</p>
        <img src="images/studentcount.png" height="100px" width="100px"><br>
        <?php
        echo $scount;
        ?>
      </div>
      <div class="card">
        <p>Instructors</p>
        <img src="images/teachercount.png" height="100px" width="100px"><br>
        <?php
        echo $tcount;
        ?>
      </div>
    </div>
    <div class="card" style="height: 400px; width:600px;">
      RECENT ACTIVITY
      <div class="listlist">yoooooooooooooooooooooooooooooooooooooooo</div>
      /*list tile is not loading perhaps some conflict witht the outer card fic that idjit */
    </div>

  </div>

  </div>



</body>


</html>