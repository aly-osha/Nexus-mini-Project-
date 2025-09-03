<html>

<head>
  <title>
    home
  </title>
  <style>
    .course-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
      gap: 1rem;
      margin-top: 1.5rem;
    }

    .course-card {
      background: #f1f5f9;
      border-radius: 12px;
      padding: 1.2rem;
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
      text-align: center;
      cursor: pointer;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .course-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    }

    .course-card h3 {
      margin: 0;
      color: #1e293b;
      font-size: 1rem;
      font-weight: 600;
    }
  </style>

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
    $result1 = mysqli_query($conn, "SELECT * FROM course");
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $name = $row["name"];
  }
  ?>
  <h1><?php echo   " welcome back ".$name ; ?>
  </h1>
  <div class="course-grid">
    <?php while ($row1 = mysqli_fetch_assoc($result1)) { ?>
      <div class="course-card">
        <?php echo "<img src='" . $row1['filepath'] . "' alt='" . $row1['course_name'] . "' width='100'>"; ?>
        <h3><?php echo htmlspecialchars($row1['course_name']); ?></h3>
      </div>
    <?php } ?>
  </div>
  </div>
</body>

</html>