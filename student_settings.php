<html>
    <head>
        <title>
            yo
        </title>
    </head>
    <body>
        <?php
        session_start();
        $id=$_SESSION['id'];
        $conn=new mysqli('localhost','root','amen','mini');
        if($conn->connect_error){
            die(''. $conn->connect_error);
        }
        else{
            $sql= "select  * from student_details where sid=$id";
            $result = $conn->query( $sql );
            $row=$result->fetch_assoc();
            echo "wassup?".$row['name'];
        }
        ?>
        
    </body>
</html>