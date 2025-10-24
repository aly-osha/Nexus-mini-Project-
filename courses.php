<?php
session_start();
require_once 'config.php';
$conn = getConnection();


$filter = $_GET['filter'] ?? 'all';
$plus = isset($_GET['plus']) ? true : false;
/* ---------------- Create Course ---------------- */
if (isset($_POST['create_course'])) {
    $cname = mysqli_real_escape_string($conn, $_POST['course_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    $filename = '';
    $filepath = '';
    
    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $targetDir = "uploads/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        $fileName = basename($_FILES["image"]["name"]);
        $filePath = $targetDir . time() . "_" . $fileName;
        
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $filePath)) {
            $filename = $fileName;
            $filepath = $filePath;
        }
    }
    
    $insert = "INSERT INTO course (course_name, description, filename, filepath, created_by, status) 
               VALUES ('$cname', '$description', '$filename', '$filepath', 0, 'active')";
    
    if ($conn->query($insert) === TRUE) {
        header('Location: admin.php#courses');
        exit;
    } else {
        echo "<div style='color:red'>Database error: " . $conn->error . "</div>";
        exit;
    }
}

/* ---------------- Edit Course ---------------- */
$edit_course = null;
if (isset($_GET['edit_course'])) {
    $cid = intval($_GET['edit_course']);
    $q = mysqli_query($conn, "SELECT * FROM course WHERE cid='$cid' LIMIT 1");
    if ($q && mysqli_num_rows($q) > 0) {
        $edit_course = mysqli_fetch_assoc($q);
    }
}

/* ---------------- Update Course ---------------- */
if (isset($_POST['update_course'])) {
    $cid = intval($_POST['cid']);
    $cname = mysqli_real_escape_string($conn, $_POST['course_name']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);

    $update = "UPDATE course SET course_name='$cname', description='$description' WHERE cid='$cid'";

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $oldFile = null;
        $res = $conn->query("SELECT filepath FROM course WHERE cid='$cid'");
        if ($res && $res->num_rows > 0) {
            $row = $res->fetch_assoc();
            $oldFile = $row['filepath'];
        }

        $targetDir = "uploads/";
        $fileName = basename($_FILES["image"]["name"]);
        $filePath = $targetDir . time() . "_" . $fileName;

        if (move_uploaded_file($_FILES["image"]["tmp_name"], $filePath)) {
            $update = "UPDATE course 
                       SET course_name='$cname', description='$description', filename='$fileName', filepath='$filePath' 
                       WHERE cid='$cid'";

            if ($oldFile && file_exists($oldFile)) {
                unlink($oldFile);
            }
        } else {
            echo "<div style='color:red'> Error moving uploaded file.</div>";
            exit;
        }
    }

    // Run the update query
    if ($conn->query($update) === TRUE) {
        header('Location: admin.php#courses');
        exit;
    } else {
        echo "<div style='color:red'>Database error: " . $conn->error . "</div>";
        exit;
    }
}

/* ---------------- Delete Course ---------------- */
if (isset($_POST['delete_course'])) {
    $cid = intval($_POST['delete_course']);
    
    // Get file path to delete
    $file_result = $conn->query("SELECT filepath FROM course WHERE cid='$cid'");
    if ($file_result && $file_result->num_rows > 0) {
        $file_path = $file_result->fetch_assoc()['filepath'];
        
        // Delete course
        if ($conn->query("DELETE FROM course WHERE cid='$cid'")) {
            // Delete associated file
            if ($file_path && file_exists($file_path)) {
                unlink($file_path);
            }
            header('Location: admin.php#courses');
            exit;
        }
    }
}

/* ---------------- Fetch all courses ---------------- */
$result = mysqli_query($conn, "SELECT * FROM course");
?>

<div class="container">
    <h1>Manage Courses</h1>

   <?php if ($plus): ?>
<div class="popup-form">
  <div class="popup-inner">
    <h2>Create New Course</h2>
    <form method="post" action="courses.php?filter=<?php echo $filter; ?>" enctype="multipart/form-data">
      <div class="form-group">
        <label>Course Name</label>
        <input type="text" name="course_name" required placeholder="Enter course name">
      </div>

      <div class="form-group">
        <label>Course Description</label>
        <textarea name="description" rows="3" placeholder="Describe what students will learn..."></textarea>
      </div>

      <div class="form-group">
        <label>Course Image</label>
        <input type="file" name="image" accept="image/*">
      </div>

      <div class="form-actions">
        <button type="submit" name="create_course">Create Course</button>
        <a href="admin.php#courses?filter=<?php echo $filter; ?>" class="cancel-link">Cancel</a>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>


    <!-- Edit Course Form -->
    <?php if ($edit_course): ?>
    <div class="form-section">
        <h2>Edit Course</h2>
        <form method="post" action="courses.php?filter=<?php echo $filter; ?>" 
              onsubmit="submitCourseForm(event,this)" 
              enctype="multipart/form-data">

            <input type="hidden" name="cid" value="<?php echo $edit_course['cid']; ?>">

            <div class="form-group">
                <label>Course Name</label>
                <input type="text" name="course_name" required 
                       value="<?php echo htmlspecialchars($edit_course['course_name']); ?>">
            </div>

            <div class="form-group">
                <label>Course Description</label>
                <textarea name="description" rows="3" placeholder="Describe what students will learn..."><?php echo htmlspecialchars($edit_course['description']); ?></textarea>
            </div>

            <div class="form-group">
                <label>Current Image</label><br>
                <?php if (!empty($edit_course['filepath'])): ?>
                    <img src="<?php echo $edit_course['filepath']; ?>" 
                         alt="<?php echo htmlspecialchars($edit_course['course_name']); ?>" 
                         width="200"><br>
                <?php else: ?>
                    <i>No image uploaded</i><br>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>Upload New Image</label>
                <input type="file" name="image" accept="image/*">
            </div>

            <div class="form-actions">
                <button type="submit" name="update_course">Update Course</button>
                <a href="#" onclick="loadPage('courses');return false;" 
                   style="margin-left:1rem; color:#e74c3c;">Cancel</a>
            </div>
        </form>
    </div>
<?php endif; ?>


    <!-- Course Cards -->
    <div class="course-grid">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="course-card">
                <?php if ($row['filepath']): ?>
                    <img src="<?php echo $row['filepath']; ?>" alt="<?php echo htmlspecialchars($row['course_name']); ?>" width="100">
                <?php else: ?>
                    <div class="no-image">
                        <i class="fas fa-book" style="font-size: 3rem; color: #bdc3c7;"></i>
                    </div>
                <?php endif; ?>
                <h3><?php echo htmlspecialchars($row['course_name']); ?></h3>
                <?php if ($row['description']): ?>
                    <p><?php echo htmlspecialchars(substr($row['description'], 0, 100)) . (strlen($row['description']) > 100 ? '...' : ''); ?></p>
                <?php endif; ?>
                <div class="course-actions">
                    <button onclick="loadPage('courses?edit_course=<?php echo $row['cid']; ?>&filter=<?php echo $filter; ?>')" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                 <form method="post" action="courses.php?filter=<?php echo $filter; ?>">
                       <button   onclick="return confirm('Are you sure you want to delete this course?');" name="delete_course" value="<?php echo $row['cid']?>" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                 </form>
                </div>
            </div>
        <?php } ?>
    </div>
    <button onclick="loadPage('courses?plus=<?php echo 1; ?>&filter=<?php echo $filter; ?>')" class="plus_button">
                         +
                    </button>
</div>



<style>
    
    .plus_button{
        
        border-radius: 10px;
      height: 50px;
      width: 50px;
        border: none;
     font-size: 25px;
     font-weight: bold;
        background-color: #3498db;
    color: white;
    }
    .container {
        max-width: 950px;
        margin: 2rem auto;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 2rem;
    }
    h1 {
        color: #1e293b;
        margin-bottom: 1.2rem;
    }
    .form-section {
        background: #f8f8f8;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 2rem;
    }
    .form-group {
        margin-bottom: 1rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.3rem;
    }
    .form-group input {
        width: 100%;
        padding: 0.6rem;
        border-radius: 6px;
        border: 1px solid #ccc;
    }
    .form-actions {
        text-align: right;
    }
    .form-actions button {
        background: #1e293b;
        color: #fff;
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 1rem;
        margin-top: 1.5rem;
    }
    .course-card {
        background: #f1f5f9;
        border-radius: 12px;
        padding: 1.2rem;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        text-align: center;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .course-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    }
    .course-card h3 {
        margin: 0 0 0.5rem 0;
        color: #1e293b;
        font-size: 1rem;
        font-weight: 600;
    }
    
    .course-card p {
        color: #7f8c8d;
        font-size: 0.85rem;
        margin-bottom: 1rem;
        line-height: 1.4;
    }
    
    .no-image {
        height: 100px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 1rem;
    }
    
    .course-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }
    
    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 0.8rem;
        transition: all 0.3s;
        text-decoration: none;
        display: inline-block;
    }
    
    .btn-primary {
        background-color: #3498db;
        color: white;
    }
    
    .btn-primary:hover {
        background-color: #2980b9;
    }
    
    .btn-danger {
        background-color: #e74c3c;
        color: white;
    }
    
    .btn-danger:hover {
        background-color: #c0392b;
    }
    
    .btn-sm {
        padding: 4px 8px;
        font-size: 0.75rem;
    }
    .popup-form {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999;
}

.popup-inner {
  background: #fff;
  padding: 2rem;
  border-radius: 12px;
  width: 400px;
  max-width: 90%;
}

.cancel-link {
  margin-left: 1rem;
  color: #e74c3c;
  text-decoration: none;
}

</style>

<script>
// Handle AJAX form submit
function submitCourseForm(e, form) {
    e.preventDefault();
    const data = new FormData(form);
    fetch(form.action, { method: "POST", body: data })
      .then(res => res.text())
      .then(html => {
        document.getElementById("main-content").innerHTML = html;
      });
}


</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
