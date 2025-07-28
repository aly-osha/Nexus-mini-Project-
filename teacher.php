<!DOCTYPE html>
<html>

<head>
    <title>
        teacher
    </title>
</head>

<body>
    <?php
    session_start();
    $id = $_SESSION['id'];
    echo "yooo" . $id;

    ?>
</body>

</html>