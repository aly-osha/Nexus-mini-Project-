<?php
session_start();
$id = $_SESSION["id"];
?>
<html>

<head>
  <title>Edit Course</title>
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
      cursor: not-allowed;
    }

    .form-actions {
      margin-top: 15px;
    }
  </style>
  <script>
    function enableEdit() {
      // Enable all input fields
      let inputs = document.querySelectorAll(".form-section input[type=text], .form-section input[type=file],.form-section input[type=date]");
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
  $prefilq = "select * from student_details where sid=$id";
  $result = $conn->query($prefilq);
  $fill = $result->fetch_assoc();
  ?>

  <?php if ($fill): ?>
    <div class="form-section">
      <h2>PROFILE</h2>
      <form method="post" enctype="multipart/form-data">

        <div class="form-group">
          <label>NAME:</label>
          <input type="text" name="name" value="<?php echo htmlspecialchars($fill['name']); ?>" readonly>
        </div>
        <div class="form-group">
               <label>DOB:</label>
               <input type="date" name="dob" 
                      value="<?php echo htmlspecialchars($fill['dob']); ?>" 
                      readonly>
           </div>

        <div class="form-group">
          <label>Email:</label>
          <input type="text" name="email" value="<?php echo htmlspecialchars($fill['e_mail']); ?>" readonly>
        </div>
<div class="form-group">
          <label>Address:</label>
          <input type="text" name="email" value="<?php echo htmlspecialchars($fill['address']); ?>" readonly>
        </div>
        <div class="form-group">
          <label>Current Image</label><br>
          <?php if (!empty($fill['filepath'])): ?>
            <img src="<?php echo htmlspecialchars($fill['filepath']); ?>" width="200" alt="profilepic"><br>
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
          <button type="submit" id="updateBtn" name="update_course" style="display:none;">Update</button>
          <button type="button" id="cancelBtn" onclick="cancelEdit()" style="display:none;">Cancel</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</body>

</html>