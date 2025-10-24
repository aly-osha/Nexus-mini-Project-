<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'amen');
define('DB_NAME', 'mini');

// Create database connection
function getConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Helper function to escape strings
function escapeString($conn, $string) {
    return mysqli_real_escape_string($conn, $string);
}

// Helper function to upload files
function uploadFile($file, $targetDir = "uploads/") {
    if (!file_exists($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    
    $fileName = basename($file["name"]);
    $uniqueName = uniqid() . "_" . $fileName;
    $targetFilePath = $targetDir . $uniqueName;
    
    if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
        return array(
            'filename' => $fileName,
            'filepath' => $targetFilePath
        );
    }
    
    return false;
}
?>
