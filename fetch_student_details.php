<?php
session_start();
$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET['sid'])) {
    echo "<p style='color:red;'>Invalid student ID.</p>";
    exit;
}

$sid = intval($_GET['sid']);

$sql = "
    SELECT sd.*, su.user_name, e.enrollment_date, e.status AS enrollment_status, 
           e.progress, c.course_name 
    FROM student_details sd
    JOIN student_user su ON sd.sid = su.sid
    LEFT JOIN enrollments e ON sd.sid = e.student_id
    LEFT JOIN course c ON e.course_id = c.cid
    WHERE sd.sid = $sid
    LIMIT 1
";

$result = $conn->query($sql);
if (!$result || $result->num_rows === 0) {
    echo "<p>No student found.</p>";
    exit;
}

$student = $result->fetch_assoc();
?>

<div style="text-align: center;">
    <img src="<?php echo $student['profilepic'] ?: 'images/signup-image.jpg.png'; ?>" 
         alt="Profile" 
         style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid #3498db; margin-bottom: 1rem;">
    <h4 style="margin-bottom: 0.3rem;"><?php echo htmlspecialchars($student['name']); ?></h4>
    <small style="color: #7f8c8d;">@<?php echo htmlspecialchars($student['user_name']); ?></small>
</div>

<hr>

<div style="text-align: left; margin-top: 1rem;">
    <p><strong>📚 Course:</strong> <?php echo htmlspecialchars($student['course_name'] ?? 'Not enrolled'); ?></p>
    <p><strong>📆 Enrolled On:</strong> <?php echo $student['enrollment_date'] ? date('M j, Y', strtotime($student['enrollment_date'])) : '—'; ?></p>
    <p><strong>📈 Progress:</strong> <?php echo $student['progress'] ?? 0; ?>%</p>
    <p><strong>🎯 Status:</strong> <?php echo ucfirst($student['enrollment_status'] ?? 'N/A'); ?></p>
    <p><strong>📧 Email:</strong> <a href="mailto:<?php echo $student['e_mail']; ?>"><?php echo $student['e_mail']; ?></a></p>
    <p><strong>📞 Phone:</strong> <a href="tel:<?php echo $student['phone']; ?>"><?php echo $student['phone']; ?></a></p>
</div>
