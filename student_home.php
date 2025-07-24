<html>
    <head>
        <title>
            home
        </title>
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
  <h1><?php echo $name." welcome"; ?>
</h1>
    </body>
</html>