<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>My Learning</title>
    <link rel="stylesheet" href="student.css">
</head>

<body>

    <header class="nav-bar">
        <div class="logo"><img src="images/loginboxjpg.png" width="200px" hieght="100px"></div>
        <nav class="nav-tabs">
            <a href="#" class="nav-link active" onclick="switchTab('home')">Home</a>
            <a href="#" class="nav-link" onclick="switchTab('learning')">My Learning</a>
            <a href="#" class="nav-link" onclick="switchTab('degrees')">Online Degrees</a>
        </nav>
    </header>

    <main>
        <!-- HOME PAGE -->
        <section id="home" class="tab-content visible">
            <h2>Welcome Home!</h2>
            <p><?php
            session_start();
            $sid = $_SESSION['id'];

            //connection variables
            $user = "root";
            $pass = "amen";
            $db = "mini";
            $host = "localhost";
            $query = "select * from student_details where sid=$sid";
            $conn = mysqli_connect($host, $user, $pass, $db);
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }else{
            $result=$conn->query($query);
            $row=mysqli_fetch_assoc( $result );
            echo  " Welcome ".$row['name'];
            }
            ?></p>
        </section>

        <!-- MY LEARNING -->
        <section id="learning" class="tab-content">
            <h2>My Learning</h2>
            <div class="sub-tabs">
                <button onclick="toggleSubTab('progress')" class="subtab active">In Progress</button>
                <button onclick="toggleSubTab('completed')" class="subtab">Completed</button>
            </div>
            <div id="progress" class="subtab-content visible">
                <p>You are learning Generative AI 🧠...</p>
            </div>
            <div id="completed" class="subtab-content">
                <p>Courses you've completed will appear here ✅.</p>
            </div>
        </section>

        <!-- DEGREES -->
        <section id="degrees" class="tab-content">
            <h2>Degrees</h2>
            <p>Explore online degrees here 🎓.</p>
        </section>
    </main>

    <script src="student.js"></script>
</body>

</html>