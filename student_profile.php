<?php
session_start();
$id = $_SESSION["id"];
?>
<html>

<head>
  <link href="images/signup-image.jpg.png" rel="icon" type="image/x-icon">
  <title>Profile</title>
  <style>
    .form-section {
      width: 400px;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 8px;
    }

    input[readonly],
    input[disabled] {
      background-color: white;
      color: #000;
      border: none;
    }

    .form-actions {
      margin-top: 15px;
    }

    textarea[readonly],
    textarea[disabled] {
      background-color: white;
      color: #000;
      border: none;
      cursor: copy;
    }
  </style>
  <script>
    function enableEdit() {
      // Enable all input fields
      let inputs = document.querySelectorAll(".form-section input, .form-section textarea");
      inputs.forEach(el => el.removeAttribute("readonly"));
      inputs.forEach(el => el.removeAttribute("disabled"));

      // Show update + cancel, hide edit
      document.getElementById("updateBtn").style.display = "inline-block";
      document.getElementById("cancelBtn").style.display = "inline-block";
      document.getElementById("editBtn").style.display = "none";
    }

    function cancelEdit() {
      // Reload the page (back to readonly mode)
      location.reload();
    }
  </script>
</head>

<body>
  <?php
  $conn = new mysqli("localhost", "root", "amen", "mini");

  // Fetch current student details
  $prefilq = "SELECT * FROM student_details WHERE sid=$id";
  $result = $conn->query($prefilq);
  $fill = $result->fetch_assoc();

  if (isset($_POST["updatebutt"])) {
    $name = $_POST["name"];
    $dob = $_POST["dob"];
    $gender = $_POST["gender"];
    $email = $_POST["email"];
    $address = $_POST["address"];
    $profilePath = $_FILES['image']; // keep old image by default

    // If new image uploaded
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
      $oldFile = null;
      $res = $conn->query("SELECT profilepic FROM student_details WHERE sid='$id'");
      if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $oldFile = $row['profilepic'];
      }

      $targetDir = "uploads/";
      $fileName = preg_replace("/[^A-Za-z0-9_\-\.]/", "_", basename($_FILES["image"]["name"]));
      $filePath = $targetDir . time() . "_" . $fileName;

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

    // Update student details
    $update = "UPDATE student_details
               SET name='$name', dob='$dob', gender='$gender', e_mail='$email', address='$address', profilepic='$profilePath'
               WHERE sid='$id'";
    $conn->query($update);

    // Refresh data after update
    $result = $conn->query($prefilq);
    $fill = $result->fetch_assoc();
  }
  ?>

  <?php if ($fill): ?>
    <div class="form-section">
      <h2>PROFILE</h2>
      <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">

        <div class="form-group">
          <label>NAME:</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($fill['name']); ?>" readonly>
        </div>

        <div class="form-group">
          <label>DOB:</label>
          <input type="date" name="dob" value="<?php echo htmlspecialchars($fill['dob']); ?>" readonly>
        </div>

        <div class="form-group">
          <label>Gender:</label>
          <input type="text" name="gender" value="<?php echo htmlspecialchars($fill['gender']); ?>" readonly>
        </div>

        <div class="form-group">
          <label>Email:</label>
          <input type="text" name="email" value="<?php echo htmlspecialchars($fill['e_mail']); ?>" readonly>
        </div>

        <div class="form-group">
          <label>Address:</label>
          <textarea name="address" readonly><?php echo htmlspecialchars($fill['address']); ?></textarea>
        </div>

        <div class="form-group">
          <label>Current Image</label><br>
          <?php if (!empty($fill['profilepic'])): ?>
            <img src="<?php echo htmlspecialchars($fill['profilepic']); ?>" width="200" alt="profilepic"><br>
          <?php else: ?>
            <i>No image uploaded</i><br>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label>Upload New Image</label>
          <input type="file" name="image" accept="image/*" disabled>
        </div>

        <div class="form-actions">
          <button type="button" id="editBtn" onclick="enableEdit()">Edit</button>
          <button type="submit" id="updateBtn" name="updatebutt" style="display:none;">Update</button>
          <button type="button" id="cancelBtn" onclick="cancelEdit()" style="display:none;">Cancel</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>
