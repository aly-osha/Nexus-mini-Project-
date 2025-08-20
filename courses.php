<?php
session_start();
$conn = mysqli_connect("localhost", "root", "amen", "mini");
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

$filter = $_GET['filter'] ?? 'all';

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
    $update = "UPDATE course SET course_name='$cname' WHERE cid='$cid'";
   if ($conn->query($update) === TRUE) {
    // Reload same page with same filter
     header('Location: admin.php#courses');
    exit;
}
 else {
        echo "<div style='color:red'>Error updating course: " . $conn->error . "</div>";
        exit;
    }
}

/* ---------------- Fetch all courses ---------------- */
$result = mysqli_query($conn, "SELECT * FROM course");
?>

<div class="container">
    <h1>Manage Courses</h1>

    <!-- If a card is clicked, show the edit form -->
    <?php if ($edit_course): ?>
        <div class="form-section">
            <h2>Edit Course</h2>
            <form method="post" action="courses.php?filter=<?php echo $filter; ?>" onsubmit="submitCourseForm(event,this)">
                <input type="hidden" name="cid" value="<?php echo $edit_course['cid']; ?>">
                <div class="form-group">
                    <label>Course Name</label>
                    <input type="text" name="course_name" required 
                           value="<?php echo htmlspecialchars($edit_course['course_name']); ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_course">Update Course</button>
                    <a href="#" onclick="loadPage('courses');return false;" style="margin-left:1rem; color:#e74c3c;">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Show all courses as cards -->
    <div class="course-grid">
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <div class="course-card" onclick="loadPage('courses?edit_course=<?php echo $row['cid']; ?>&filter=<?php echo $filter; ?>')">
                <h3><?php echo htmlspecialchars($row['course_name']); ?></h3>
            </div>
        <?php } ?>
    </div>
</div>

<style>
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
        margin: 0;
        color: #1e293b;
        font-size: 1rem;
        font-weight: 600;
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
