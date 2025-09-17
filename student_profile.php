<?php
session_start();

// Ensure user is logged in
if (!isset($_SESSION["id"])) {
  die("Unauthorized access.");
}
$id = intval($_SESSION["id"]);

$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}

// Fetch current student details
$prefilq = "SELECT * FROM student_details WHERE sid=$id";
$result = $conn->query($prefilq);
$fill = $result->fetch_assoc();

if (isset($_POST["updatebutt"])) {
  $name = $conn->real_escape_string($_POST["name"]);
  $dob = $conn->real_escape_string($_POST["dob"]);
  $gender = $conn->real_escape_string($_POST["gender"]);
  $email = $conn->real_escape_string($_POST["email"]);
  $address = $conn->real_escape_string($_POST["address"]);
  $profilePath = $fill["profilepic"]; // default old image

  // Handle new image if uploaded
  if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
    $oldFile = $profilePath;

    $targetDir = "uploads/";
    $fileName = preg_replace("/[^A-Za-z0-9_\-\.]/", "_", basename($_FILES["image"]["name"]));
    $filePath = $targetDir . time() . "_" . $fileName;

    // Check MIME type
    $allowedTypes = ["image/jpeg", "image/png", "image/gif"];
    if (!in_array($_FILES["image"]["type"], $allowedTypes)) {
      die("<div style='color:red'>Invalid file type. Only JPG, PNG, GIF allowed.</div>");
    }

    if (move_uploaded_file($_FILES["image"]["tmp_name"], $filePath)) {
      $profilePath = $filePath;

      // delete old file only if inside uploads folder
      if ($oldFile && strpos($oldFile, "uploads/") === 0 && file_exists($oldFile)) {
        unlink($oldFile);
      }
    } else {
      echo "<div style='color:red'>Error moving uploaded file.</div>";
      exit;
    }
  }


  $update = "UPDATE student_details
               SET name='$name', dob='$dob', gender='$gender',
                   e_mail='$email', address='$address', profilepic='$profilePath'
               WHERE sid=$id";

  if ($conn->query($update)) {
    // Refresh to show updated data
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
  } else {
    echo "Error updating record: " . $conn->error;
  }
}
if (isset($_POST["back"])) {
  header("location:student.php");
}
?>
<html>

<head>
  <link href="images/signup-image.jpg.png" rel="icon" type="image/x-icon">
  <title>Profile</title>
  <style>
    .form-section {
      background-color: white;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      width: 400px;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    input[readonly],
    input[disabled] {
      background-color: white;
      color: #1e293b;
      border: none;
      font-size: 20px;
    }

    .form-actions {
      margin-top: 15px;

    }

    textarea[readonly],
    textarea[disabled] {
      resize: none;
font-size: 20px;
      height: 20px;
      overflow-y: auto;
      overflow-x: hidden;
      background-color: white;
      color: #1e293b;
      border: none;
      cursor: not-allowed;
      width: 300px;
    }
textarea{
  font-size: 20px;
     resize: none;
      width: 300px;
      height: 20px;
      overflow-y: auto;
      overflow-x: hidden;
      background-color: white;
      color: #1e293b;
      border: none;
   
}
    body {
      display: flex;
      justify-content: center;
      align-items: center;
      background-color: #1e293b;
    }

    .form-group {
      background-color: white;
      font-size: 20px;
      padding-top: 20px;
      color: #1e293b;
    }

    input {
      font-size: 20px;
      color: #1e293b;
      border: none;
    }

    button {
      color: white;
      font-size: 20px;
      font-weight: 100;
      background-color: #1e293b;
      border-radius: 15px;
      width: 100px;
      height: 50px;
    }
  </style>
  <script>
    function enableEdit() {
      let inputs = document.querySelectorAll(".form-section input, .form-section textarea");
      inputs.forEach(el => el.removeAttribute("readonly"));
      inputs.forEach(el => el.removeAttribute("disabled"));

      document.getElementById("updateBtn").style.display = "inline-block";
      document.getElementById("cancelBtn").style.display = "inline-block";
      document.getElementById("editBtn").style.display = "none";
      document.getElementById("back").style.display = "none";

      // Re-enable image input
      document.getElementById("imageInput").disabled = false;

      // Make image clickable to trigger file picker
      document.getElementById("profileImage").addEventListener("click", () => {
        document.getElementById("imageInput").click();
      });

      // Show preview immediately after selection
      document.getElementById("imageInput").addEventListener("change", (e) => {
        if (e.target.files && e.target.files[0]) {
          const reader = new FileReader();
          reader.onload = (ev) => {
            document.getElementById("profileImage").src = ev.target.result;
          };
          reader.readAsDataURL(e.target.files[0]);
        }
      });
    }

    function cancelEdit() {
      location.reload();
    }
  </script>

</head>

<body>
  <?php if ($fill): ?>
    <div class="form-section">
      <h2 style="font-size:30px;"> <u style="color: #1e293b;">PROFILE</u></h2>


      </form>
      <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

        <div class="image-box" style="padding-left: 40px;">
          <?php if (!empty($fill['profilepic'])): ?>
            <img src="<?php echo $fill['profilepic']; ?>" id="profileImage" width="200" alt="profilepic"
              style="cursor:pointer; border:2px solid #1e293b; border-radius:100px;width:200px;height:200px;">
          <?php else: ?>
            <img src="images/default-avatar.png" id="profileImage" width="200" alt="No profilepic"
              style="cursor:pointer; border:2px dashed #1e293b; border-radius:8px;">
          <?php endif; ?>
          <input type="file" name="image" id="imageInput" accept="image/*" style="display:none;" disabled>

        </div>
        <div class="form-group">
          <label>NAME:</label>
          <input type="text" name="name" value="<?php echo $fill['name']; ?>" readonly>
        </div>

        <div class="form-group">
          <label>DOB:</label>
          <input type="date" name="dob" value="<?php echo $fill['dob']; ?>" readonly>
        </div>

        <div class="form-group">
          <label>Gender:</label>
          <input type="text" name="gender" value="<?php echo $fill['gender']; ?>" readonly>
        </div>

        <div class="form-group">
          <label>Email:</label>
          <input type="text" name="email" value="<?php echo $fill['e_mail']; ?>" readonly>
        </div>

        <div class="form-group">
          <label>Address:</label>
          <textarea name="address" readonly><?php echo $fill['address']; ?></textarea>
        </div>
<br>

  <!-- Hidden file input -->
 
 




        <div class="form-actions">
          <button type="button" id="editBtn" onclick="enableEdit()">Edit</button>
          <button type="submit" id="back" name="back">BACK</button>
          <button type="submit" id="updateBtn" name="updatebutt" style="display:none;">Update</button>
          <button type="button" id="cancelBtn" onclick="cancelEdit()" style="display:none;">Cancel</button>

        </div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>