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
    $teachercount = " select count(*) as number from teacher_user;";
    $t_count = $conn->query($teachercount);
    $trow = $t_count->fetch_assoc();
    $tcount = $trow['number'];
    ?>
    <h1 style="color: goldenrod;">🌟 Hello, <?php echo $name; ?>!</h1><br>
    <div class="row">
      <div class="card">
        <p>Courses</p>
        <img src="images/course.png" height="100px" width="100px">
        
        <?php
        echo $count; ?>
      </div>
      <div class="card">
        <p>Students</p>
        <img src="images/studentcount.png" height="100px" width="100px">
        <?php
        echo $scount;
        ?>
      </div>
      <div class="card">
        <p>Instructors</p>
        <img src="images/teachercount.png" height="100px" width="100px">
        <?php
        echo $tcount;
        ?>
      </div>
    </div>
    <div class="card" style="height: 400px; width:600px;">
      RECENTLY REGISTERED
      <div class="row">
        <div class="heading">
          USER COURSE DATE
        </div>
      </div>
      <?php
      $recent = "select * from student_details  order by register desc limit 5";
      $resultrec = $conn->query($recent);
      echo "<table  style='padding-top:10px;font-size: 22px;',bordere=2>";
      while ($rowrec = $resultrec->fetch_assoc()) {
    echo "<tr style='padding-top: 5px;'>";
echo "<td style='padding-top: 5px; text-align: center;'>
        <img src='" . $rowrec['profilepic'] . "' style='border-radius:100px; width:50px; height:50px;'>
      </td>";
    echo "<td style='width:160px;'>" . htmlspecialchars($rowrec['name']) . "</td>";
    echo "<td style='width:200px;padding-left:4px'>blahblahblah</td>";
    echo "<td>".$rowrec['register'];
    echo "</td></tr>";
}

      echo "<table>";
      ?>


    </div>

  </div>

  </div>



</body>


</html>