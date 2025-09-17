<html>

<head>
    <title>
        yo
    </title>
    <style>
        .settings_shrunk {
            align-items: center;
            align-content: center;
            height: 100px;

  background-color: #1e293b;
            border-radius: 30px;
           text-align: center;
        font-size: 20;
        font-weight: 100;
        color:white;
        }
    </style>
</head>

<body>
    <?php
    session_start();
    $id = $_SESSION['id'];
    $conn = new mysqli('localhost', 'root', 'amen', 'mini');
    if ($conn->connect_error) {
        die('' . $conn->connect_error);
    } else {
        $sql = "select  * from student_details where sid=$id";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

    }
    ?>
    <div class="settings_shrunk">
        Account Settings
    </div>
    <br>
    <div class="settings_shrunk">
        Security Settings
    </div>
    <br>
    <div class="settings_shrunk">
        delete Account
    </div>
</body>

</html>