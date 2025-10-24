<?php
session_start();
$tid = $_SESSION['id'];
$conn = new mysqli("localhost", "root", "amen", "mini");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

 

// Handle material upload
if (isset($_POST['upload_material'])) {
    $course_id = intval($_POST['course_id']);
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $material_type = mysqli_real_escape_string($conn, $_POST['material_type']);
    // Initialize file path
    $file_path = '';

    // Handle file upload when a file is provided
    if (isset($_FILES['material_file']) && isset($_FILES['material_file']['error']) && $_FILES['material_file']['error'] == 0) {
        $targetDir = __DIR__ . "/uploads/materials/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        // sanitize filename
        $origName = basename($_FILES["material_file"]["name"]);
        $safeName = preg_replace('/[^A-Za-z0-9._-]/', '_', $origName);
        $filePathRel = 'uploads/materials/' . time() . '_' . $safeName;
        $filePath = $targetDir . time() . '_' . $safeName;

        if (move_uploaded_file($_FILES["material_file"]["tmp_name"], $filePath)) {
            $file_path = $conn->real_escape_string($filePathRel);
        } else {
            echo "<script>showAlert('Failed to move uploaded file on server.', 'error');</script>";
            exit;
        }
    } else {
        // if material type requires a file, enforce it
        if ($material_type !== 'link') {
            echo "<script>showAlert('Please select a file to upload.', 'error');</script>";
            exit;
        }
    }

    $insert_sql = "INSERT INTO course_materials (course_id, title, description, file_path, material_type, uploaded_by) 
                   VALUES ($course_id, '$title', '$description', '$file_path', '$material_type', $tid)";
    
    if ($conn->query($insert_sql)) {
   
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: teacher.php?page=teacher_materials.php');
            exit;
        }
        echo "<script>showAlert('Material uploaded successfully!', 'success');</script>";
    } else {
        if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            header('Location: teacher.php?page=teacher_materials.php&error=' . urlencode($conn->error));
            exit;
        }
        echo "<script>showAlert('Error uploading material: " . $conn->error . "', 'error');</script>";
    }
}
// Handle material deletion
if (isset($_GET['delete_material'])) {
    $material_id = intval($_GET['delete_material']);


    // Get file path to delete
    $file_result = $conn->query("SELECT file_path FROM course_materials WHERE material_id=$material_id AND uploaded_by=$tid");
    if ($file_result->num_rows > 0) {
        $file_path = $file_result->fetch_assoc()['file_path'];

        // Delete material from DB
        if ($conn->query("DELETE FROM course_materials WHERE material_id=$material_id AND uploaded_by=$tid")) {
            // Delete associated file using absolute path
            if ($file_path) {
                $absPath = __DIR__ . '/' . $file_path; // ensure full server path
                if (file_exists($absPath)) {
                    unlink($absPath);
                }
            }
            // Redirect to avoid re-execution on page refresh
            header('Location: teacher.php?page=teacher_materials.php&msg=deleted');
            exit;
        } else {
            echo "<script>alert('Error deleting material: " . $conn->error . "');</script>";
        }
    } else {
        echo "<script>alert('Material not found or you do not have permission to delete it.');</script>";
    }
}


$assignedcourse = "SELECT cid FROM teacher_details WHERE tid=$tid";
$assined = $conn->query($assignedcourse);

$teacher_course_id = 0;
if ($assined->num_rows > 0) {
    $teacher_course_id = $assined->fetch_assoc()['cid'];
}

$course_filter = isset($_GET['course_id']) ? intval($_GET['course_id']) : 0;

$courses_result = $conn->query("SELECT * FROM course WHERE status='active' AND cid=$teacher_course_id ORDER BY course_name");

$materials_sql = "SELECT cm.*, c.course_name 
                 FROM course_materials cm 
                 JOIN course c ON cm.course_id = c.cid 
                 WHERE cm.uploaded_by = $tid";
if ($course_filter > 0) {
    $materials_sql .= " AND cm.course_id = $course_filter";
}
$materials_sql .= " ORDER BY cm.upload_date DESC";
$materials_result = $conn->query($materials_sql);
?>

<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 style="color: #2c3e50;">📁 Course Materials</h1>
            <p style="color: #7f8c8d;">Upload and manage learning materials for your courses.</p>
        </div>
    </div>

    <!-- Material Upload Form -->
    <div class="form-container">
        <h3>📤 Upload New Material</h3>
    <form method="post" enctype="multipart/form-data" action="teacher_materials.php">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="course_id">Course *</label>
                        <select id="course_id" name="course_id" required>
                            <option value="">Select Course</option>
                            <?php 
                            $courses_result->data_seek(0); 
                            while ($course = $courses_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $course['cid']; ?>" 
                                        <?php echo $course_filter == $course['cid'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($course['course_name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="material_type">Material Type *</label>
                        <select id="material_type" name="material_type" required>
                            <option value="document">Document</option>
                            <option value="video">Video</option>
                            <option value="image">Image</option>
                            <option value="link">Link/URL</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label for="title">Material Title *</label>
                <input type="text" id="title" name="title" required 
                       placeholder="Enter a descriptive title for the material">
            </div>
            
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3" 
                          placeholder="Provide additional details about this material..."></textarea>
            </div>
            
            <div class="form-group">
                <label for="material_file">Upload File *</label>
                <input type="file" id="material_file" name="material_file" required>
                <small class="text-muted">
                    Supported formats: PDF, DOC, DOCX, PPT, PPTX, MP4, AVI, JPG, PNG, etc.
                </small>
            </div>
            
            <div class="form-actions">
                <input type="hidden" name="upload_material" value="1">
                <button type="submit" name="upload_material" class="btn btn-primary">
                    <i class="fas fa-upload"></i> Upload Material
                </button>
            </div>
        </form>
    </div>

    <!-- Materials Grid -->
    <div class="materials-grid">
        <?php if ($materials_result->num_rows > 0): ?>
            <?php while ($material = $materials_result->fetch_assoc()): ?>
                <div class="material-card">
                    <div class="material-icon">
                        <?php
                        $icon = 'fas fa-file';
                        switch ($material['material_type']) {
                            case 'video':
                                $icon = 'fas fa-play-circle';
                                break;
                            case 'image':
                                $icon = 'fas fa-image';
                                break;
                            case 'link':
                                $icon = 'fas fa-link';
                                break;
                            case 'document':
                            default:
                                $icon = 'fas fa-file-alt';
                                break;
                        }
                        ?>
                        <i class="<?php echo $icon; ?>"></i>
                    </div>
                    
                    <div class="material-content">
                        <h4><?php echo htmlspecialchars($material['title']); ?></h4>
                        <p class="material-course"><?php echo htmlspecialchars($material['course_name']); ?></p>
                        
                        <?php if ($material['description']): ?>
                            <p class="material-description">
                                <?php echo htmlspecialchars(substr($material['description'], 0, 100)) . (strlen($material['description']) > 100 ? '...' : ''); ?>
                            </p>
                        <?php endif; ?>
                        
                        <div class="material-meta">
                            <span class="badge bg-<?php 
                                echo $material['material_type'] == 'video' ? 'danger' : 
                                    ($material['material_type'] == 'image' ? 'warning' : 
                                    ($material['material_type'] == 'link' ? 'info' : 'primary')); 
                            ?>">
                                <?php echo ucfirst($material['material_type']); ?>
                            </span>
                            <small class="text-muted">
                                <?php echo date('M j, Y', strtotime($material['upload_date'])); ?>
                            </small>
                        </div>
                        
                        <div class="material-actions">
                            <?php if ($material['file_path']): ?>
                                <a href="<?php echo $material['file_path']; ?>" target="_blank" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="<?php echo $material['file_path']; ?>" download class="btn btn-success btn-sm">
                                    <i class="fas fa-download"></i> Download
                                </a>
                            <?php endif; ?>
                       <form action="teacher_materials.php" method="get" style="display:inline;">
    <input type="hidden" name="page" value="teacher_materials.php">
    <input type="hidden" name="delete_material" value="<?php echo $material['material_id']; ?>">
    <button type="submit" class="btn btn-danger btn-sm"
            onclick="return confirm('Are you certain that you wish to delete this material?')">
        <i class="fas fa-trash"></i> Delete
    </button>
</form>



                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-folder-open" style="font-size: 4rem; color: #bdc3c7; margin-bottom: 1rem;"></i>
                    <h3 style="color: #7f8c8d;">No materials uploaded yet</h3>
                    <p style="color: #95a5a6;">Upload your first course material to get started!</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.filter-container {
    background: white;
    padding: 1rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.materials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.material-card {
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: transform 0.2s, box-shadow 0.2s;
    display: flex;
    gap: 1rem;
}

.material-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0, 0, 0, 0.15);
}

.material-icon {
    flex-shrink: 0;
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3498db, #2ecc71);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.material-content {
    flex: 1;
    min-width: 0;
}

.material-content h4 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 1.1rem;
    word-wrap: break-word;
}

.material-course {
    color: #3498db;
    font-weight: 500;
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.material-description {
    color: #7f8c8d;
    font-size: 0.85rem;
    margin-bottom: 1rem;
    line-height: 1.4;
}

.material-meta {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    flex-wrap: wrap;
    gap: 0.5rem;
}

.material-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.material-actions .btn {
    flex: 1;
    min-width: 80px;
}

.d-flex {
    display: flex;
}

.align-items-center {
    align-items: center;
}

.gap-3 {
    gap: 1rem;
}

.row {
    display: flex;
    flex-wrap: wrap;
    margin: 0 -0.75rem;
}

.col-12 {
    flex: 0 0 100%;
    max-width: 100%;
    padding: 0 0.75rem;
}

.col-md-6 {
    flex: 0 0 50%;
    max-width: 50%;
    padding: 0 0.75rem;
}

@media (max-width: 768px) {
    .col-md-6 {
        flex: 0 0 100%;
        max-width: 100%;
    }
    
    .materials-grid {
        grid-template-columns: 1fr;
    }
    
    .material-card {
        flex-direction: column;
        text-align: center;
    }
    
    .material-icon {
        align-self: center;
    }
    
    .material-actions .btn {
        width: 100%;
        margin-bottom: 0.25rem;
    }
}

.mb-4 {
    margin-bottom: 1.5rem !important;
}

.py-5 {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
}

.text-center {
    text-align: center !important;
}

.text-muted {
    color: #6c757d !important;
}

.badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
    border-radius: 0.375rem;
}

.bg-primary { background-color: #3498db !important; color: white; }
.bg-success { background-color: #27ae60 !important; color: white; }
.bg-warning { background-color: #f39c12 !important; color: white; }
.bg-danger { background-color: #e74c3c !important; color: white; }
.bg-info { background-color: #17a2b8 !important; color: white; }
</style>

<script>
function filterByCourse(courseId) {
    window.location.href = `?page=teacher_materials.php&course_id=${courseId}`;
}



// Update material type icon preview
var mtEl = document.getElementById('material_type');
if (mtEl) {
    mtEl.addEventListener('change', function() {
        // This could be used to show a preview of the icon that will be used
    });
}
</script>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
