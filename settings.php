<?php
session_start();
$host = "localhost";
$username = "root";
$password = "amen";
$database = "mini";
$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$id = $_SESSION['id'];

// Fetch user info
$stmt = $conn->prepare("SELECT * FROM adminnex WHERE aid=?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row1 = $result->fetch_assoc();

// === Update Profile (Name + Image) ===
if (isset($_POST['Profile'])) {
    $fullname = $_POST['fullname'];

    // If new file uploaded
    if (!empty($_FILES['profile_image']['name'])) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $fileName = basename($_FILES['profile_image']['name']);
        $uniqueName = uniqid() . "_" . $fileName;
        $targetFilePath = $targetDir . $uniqueName;

        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $targetFilePath)) {

            // Delete old image if exists
            if (!empty($row1['filepath']) && file_exists($row1['filepath'])) {
                unlink($row1['filepath']);
            }

            // Update DB with new image
            $update = "UPDATE adminnex SET name=?, filename=?, filepath=? WHERE aid=?";
            $stmt = $conn->prepare($update);
            $stmt->bind_param("sssi", $fullname, $fileName, $targetFilePath, $id);
            $stmt->execute();
        }
    } else {
        // Update only the name if no new file uploaded
        $update = "UPDATE adminnex SET name=? WHERE aid=?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("si", $fullname, $id);
        $stmt->execute();
    }

    header("Location: admin.php#settings");
    exit();
}


// === Update Password (plain text, no hashing) ===
if (isset($_POST['UpdatePassword'])) {
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];

    // Check old password directly
    if ($old_password === $row1['password']) {
        $update = "UPDATE adminnex SET password=? WHERE aid=?";
        $stmt = $conn->prepare($update);
        $stmt->bind_param("si", $new_password, $id);
        $stmt->execute();

        echo "<script>alert('Password updated successfully!'); window.location='admin.php#settings';</script>";
    } else {
        echo "<script>alert('Old password incorrect!'); window.location='admin.php#settings';</script>";
    }
}
if (isset($_POST["changeuname"])) {
  $uname= $_POST["username"];
  $update = "update adminnex set user_name='$uname' where aid=$id";
  if($conn->query($update)){
    header("Location: admin.php#settings");
    exit();
  }
  else{
echo "<script>alert('error');</script>";
  }
}

?>


<div class="container py-4">
  <h2 class="mb-4">Settings</h2>

  <div id="settingsAccordion">

    <!-- Profile Information -->
    <div class="card">
      <div class="card-header" id="headingProfile">
        <h5 class="mb-0">
          <button class="btn btn-link" data-toggle="collapse" data-target="#collapseProfile"
            aria-expanded="true" aria-controls="collapseProfile">
            Profile Information
          </button>
        </h5>
      </div>
      <div id="collapseProfile" class="collapse" aria-labelledby="headingProfile" data-parent="#settingsAccordion">
        <div class="card-body">
          <!-- enctype for file uploads -->
          <form method="post" action="settings.php" enctype="multipart/form-data">

            <!-- Current Image -->
            <div class="form-group">
              <label>Current Profile Image</label><br>
              <?php if (!empty($row1['filepath'])): ?>
                  <img src="<?php echo htmlspecialchars($row1['filepath']); ?>" 
                       width="200" class="img-thumbnail mb-2"><br>
              <?php else: ?>
                  <i>No image uploaded</i><br>
              <?php endif; ?>
            </div>

            <!-- Upload New Image -->
            <div class="form-group">
              <label>Upload New Profile Image</label>
              <input type="file" name="profile_image" class="form-control-file">
            </div>

            <!-- Full Name -->
            <div class="form-group">
              <label>Full Name</label>
              <input type="text" name="fullname" class="form-control"
                     value="<?php echo htmlspecialchars($row1['name']); ?>">
            </div>

            <button type="submit" class="btn btn-primary" name="Profile">Update Profile</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Account Settings -->
    <div class="card">
      <div class="card-header" id="headingAccount">
        <h5 class="mb-0">
          <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseAccount"
            aria-expanded="false" aria-controls="collapseAccount">
            Account Settings
          </button>
        </h5>
      </div>
      <div id="collapseAccount" class="collapse" aria-labelledby="headingAccount" data-parent="#settingsAccordion">
        <div class="card-body">
          <form method="post" action="settings.php">
            <div class="form-group">
              <label>Username</label>
              <input type="text" name="username" class="form-control"
                     value="<?php echo htmlspecialchars($row1['user_name']); ?>">
            </div>
            <button type="submit" name="changeuname" class="update-btn">Update</button>
            <button type="submit" class="btn btn-danger" name="deletacc">Delete Account</button>
          </form>
        </div>
      </div>
    </div>

    <!-- Security -->
    <div class="card">
      <div class="card-header" id="headingSecurity">
        <h5 class="mb-0">
          <button class="btn btn-link collapsed" data-toggle="collapse" data-target="#collapseSecurity"
            aria-expanded="false" aria-controls="collapseSecurity">
            Security
          </button>
        </h5>
      </div>
      <div id="collapseSecurity" class="collapse" aria-labelledby="headingSecurity" data-parent="#settingsAccordion">
        <div class="card-body">
          <form method="post" action="update_security.php">
            <div class="form-group">
              <label>Change Password</label>
              <input type="password" name="old_password" class="form-control" placeholder="Old Password">
              <input type="password" name="new_password" class="form-control mt-2" placeholder="New Password">
            </div>
            <button type="submit" class="btn btn-warning">Update Password</button>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>
<style>
.update-btn {
  background: linear-gradient(90deg, #16a085 0%, #27ae60 100%);
  color: #fff;
  border: none;
  border-radius: 5px;
  padding: 9px 20px;
  padding-top: 2px;
  font-size: 1.1rem;
  font-weight: bold;
  letter-spacing: 1px;
  cursor: pointer;
  box-shadow: 0 4px 15px rgba(44, 62, 80, 0.15);
  transition: all 0.3s cubic-bezier(.25,.8,.25,1);
}

.update-btn:hover,
.update-btn:focus {
  background: linear-gradient(90deg, #27ae60 0%, #16a085 100%);
  box-shadow: 0 8px 24px rgba(44, 62, 80, 0.2);
  transform: translateY(-3px) scale(1.03);
}

</style>
