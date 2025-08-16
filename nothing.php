<?php
session_start();
$server = "localhost";
$u = "root";
$p = "amen";
$db = "mini";
$conn = mysqli_connect($server, $u, $p, $db);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $delete_sid = intval($_GET['delete']);
    $deletequery = "DELETE FROM student_user WHERE sid='$delete_sid'";
    if ($conn->query($deletequery) === TRUE) {
        header('Location: admin.php#users');
        exit;
    } else {
        echo "<div style='color:red'>Error deleting user: " . $conn->error . "</div>";
        exit;
    }
}

// Handle Edit User (fetch data for prefill)
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_sid = intval($_GET['edit']);
    $editquery = "SELECT * FROM student_user WHERE sid='$edit_sid' LIMIT 1";
    $editresult = mysqli_query($conn, $editquery);
    if ($editresult && mysqli_num_rows($editresult) > 0) {
        $edit_user = mysqli_fetch_assoc($editresult);
    }
}

// Handle Update User
if (isset($_POST['update_user'])) {
    $sid = intval($_POST['sid']);
    $user = mysqli_real_escape_string($conn, $_POST['user_name']);
    $pass = mysqli_real_escape_string($conn, $_POST['password']);

    $editquery = "UPDATE student_user SET user_name='$user', password='$pass' WHERE sid='$sid'";
    if ($conn->query($editquery) === TRUE) {
        header('Location: admin.php#users');
        exit;
    } else {
        echo "<div style='color:red'>Error updating user: " . $conn->error . "</div>";
        exit;
    }
}

// Default filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$where = $filter === 'nonverified' ? "WHERE verified IS NULL OR verified = ''" : "";
$query = "SELECT * FROM student_user $where";
$result = mysqli_query($conn, $query);
?>

<div class="container">
    <h1>Manage student_user</h1>

    <!-- Filter Tabs -->
    <div class="tabs">
        <a href="admin.php#users" class="<?php echo ($filter === 'all') ? 'active' : ''; ?>">All student_user</a>
        <a href="student_list.php?filter=nonverified" class="<?php echo ($filter === 'nonverified') ? 'active' : ''; ?>">Non-Verified</a>
    </div>

    <!-- Edit Form -->
    <?php if ($edit_user): ?>
        <div class="form-section">
            <h2>Edit User</h2>
            <form method="post" action="student_list.php?filter=<?php echo $filter; ?>">
                <input type="hidden" name="sid" value="<?php echo $edit_user['sid']; ?>">
                <div class="form-group">
                    <label>user_name</label>
                    <input type="text" name="user_name" required value="<?php echo htmlspecialchars($edit_user['user_name']); ?>">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="text" name="password" required value="<?php echo htmlspecialchars($edit_user['password']); ?>">
                </div>
                <div class="form-actions">
                    <button type="submit" name="update_user">Update</button>
                    <a href="admin.php#users" style="margin-left:1rem; color:#e74c3c;">Cancel</a>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- User List Table -->
    <table>
        <tr>
            <th>user_name</th>
            <th>Password</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = mysqli_fetch_assoc($result)) { ?>
            <tr>
                <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                <td><?php echo htmlspecialchars($row['password']); ?></td>
                <td class="actions">
                    <a href="student_list.php?edit=<?php echo $row['sid']; ?>">Edit</a>
                    <a href="student_list.php?delete=<?php echo $row['sid']; ?>" onclick="return confirm('Delete this user?')">Delete</a>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<style>
    .container {
        max-width: 900px;
        margin: 2rem auto;
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        padding: 2rem;
    }
    h1 {
        color: #ff6b00;
        margin-bottom: 1.2rem;
    }
    .tabs {
        margin-bottom: 1.5rem;
    }
    .tabs a {
        display: inline-block;
        margin-right: 1rem;
        padding: 0.6rem 1.2rem;
        border-radius: 6px;
        background: #ddd;
        text-decoration: none;
        color: #333;
        font-weight: 500;
    }
    .tabs a.active {
        background: #ff6b00;
        color: #fff;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 0.7rem 1rem;
        border-bottom: 1px solid #eee;
        text-align: left;
    }
    th {
        background: #ff6b00;
        color: #fff;
    }
    .actions a {
        margin-right: 0.7rem;
        text-decoration: none;
    }
    .actions a.edit { color: #3498db; }
    .actions a.delete { color: #e74c3c; }
    .form-section {
        background: #f8f8f8;
        padding: 1.5rem;
        border-radius: 10px;
        margin-bottom: 2rem;
    }
    .form-section h2 { margin-bottom: 1rem; }
    .form-group { margin-bottom: 1rem; }
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
    .form-actions { text-align: right; }
    .form-actions button {
        background: #ff6b00;
        color: #fff;
        border: none;
        padding: 0.7rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        cursor: pointer;
    }
</style>
