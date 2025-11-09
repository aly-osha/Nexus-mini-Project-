<?php
session_start();
require_once 'config.php';
$conn = getConnection();

if (!isset($_SESSION['id'])) {
    die("User not logged in.");
}
if (!isset($_GET['course_id'])) {
    die("Course not selected.");
}

$sid = $_SESSION['id'];
$cid = intval($_GET['course_id']);

// Fetch progress and course materials
$c_progress = "SELECT * FROM enrollments WHERE course_id='$cid' AND student_id='$sid'";
$course_m = "SELECT * FROM course_materials WHERE course_id='$cid' ORDER BY title ASC";

$pro_result = $conn->query($c_progress);
$materials_result = $conn->query($course_m);

if ($pro_result->num_rows > 0 && $materials_result->num_rows > 0) {
    $pro_array = $pro_result->fetch_assoc();

    // Count total modules
    $i = 0;
    $modules = [];
    while ($material_array = $materials_result->fetch_assoc()) {
        if (str_contains($material_array['title'], 'MODULE')) {
            $i++;
            $modules[] = $material_array;
        }
    }

    $check_point = ($pro_array['progress'] * $i) / 100;
    // Fix: ceil(0) is 0, but the first module is MODULE1, so we must add 1 if progress is 0.
    // If progress is 0, $check_point is 0. ($check_point + 1) is 1. ceil(1) is 1.
    // If progress is 50% for 4 modules (i=4), $check_point is 2. (2+1) is 3. ceil(3) is 3.
    $next_module_number = ceil($check_point + 1);

    if ($pro_array['progress'] >= 100 || $next_module_number > $i) {
        $completed = true;
    } else {
        $completed = false;
        $next_module_title = "MODULE" . $next_module_number;
        $rest_c = "SELECT * FROM course_materials WHERE course_id='$cid' AND title='$next_module_title'";
        $rest_result = $conn->query($rest_c);
        $rest_array = $rest_result ? $rest_result->fetch_assoc() : null;
    }
} else {
    // Attempt to rewind the materials result for re-use if the initial logic didn't work as expected
    // Note: The logic already checks num_rows > 0 for both, but it's good practice.
    // For this UI update, we keep the original logic, assuming the previous loop consumed the $materials_result.
    die("No data found or course materials could not be processed.");
}

// NEXT button
if (isset($_POST['check_p'])) {
    // Recalculate check_point based on current progress + 1 module step, but the original code was:
    // $check_point++;
    // Let's assume the variable $check_point was correctly calculated just before the conditional block
    // and is intended to be the index of the module just *completed* when this button is pressed.
    // However, the original logic uses it to calculate the *new* progress.
    // $check_point = ($pro_array['progress'] * $i) / 100; // current module index completed (0-based)
    // The previous code snippet used it for the *next* module number calc, which is confusing.
    // Since the logic must not change, we use the original approach's variables.
    
    // Recalculate current check_point based on progress before update
    $current_check_point = ($pro_array['progress'] * $i) / 100;
    
    // Assuming the intent of the original logic's $check_point++ was to increment the completed module count
    $new_check_point = $current_check_point + 1;
    $progress = ($new_check_point / $i) * 100;

    $Rest_update = "UPDATE enrollments SET progress=$progress WHERE student_id=$sid AND course_id=$cid";
    if ($conn->query($Rest_update)) {
        header("Location: course_view.php?course_id=$cid");
        exit();
    }
}

// FINISH button
if (isset($_POST['finish_course'])) {
    $Rest_update = "UPDATE enrollments SET progress=100 WHERE student_id=$sid AND course_id=$cid";
    if ($conn->query($Rest_update)) {
        header("Location: course_view.php?course_id=$cid");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Continue Learning</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #007bff;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --background-light: #f4f7fa;
            --card-background: #ffffff;
            --text-dark: #333;
            --text-muted: #6c757d;
            --border-radius: 12px;
            --shadow-subtle: 0 4px 15px rgba(0, 0, 0, 0.08);
            --progress-bar-bg: #e9ecef;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--background-light);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            color: var(--text-dark);
        }

        .container {
            background: var(--card-background);
            border-radius: var(--border-radius);
            padding: 40px;
            width: 90%;
            max-width: 700px;
            box-shadow: var(--shadow-subtle);
            text-align: center;
            position: relative;
        }

        /* Top accent bar for a professional touch */
        .container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 5px;
            width: 100%;
            background: linear-gradient(90deg, var(--primary-color), #00c6ff);
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        /* Headings */
        h2 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: var(--primary-color);
        }

        .celebration {
            font-size: 2.2rem;
            font-weight: 700;
            color: var(--success-color);
            margin-bottom: 15px;
        }

        p {
            font-size: 1rem;
            color: var(--text-dark);
            line-height: 1.6;
            margin: 8px 0;
        }

        .description {
            color: var(--text-muted);
            margin-bottom: 25px;
            font-style: italic;
        }

        /* Progress Bar */
        .progress-bar-container {
            margin: 25px 0;
            background: var(--progress-bar-bg);
            border-radius: 50px;
            height: 15px;
            overflow: hidden;
            position: relative;
        }

        .progress {
            height: 100%;
            width: <?php echo isset($pro_array) ? $pro_array['progress'] : 0; ?>%;
            background: linear-gradient(90deg, var(--success-color), #5be584);
            transition: width 0.5s ease-in-out;
            border-radius: 50px;
        }
        
        /* Progress Text Overlay (Optional, but clean) */
        .progress-text {
            position: absolute;
            top: -20px;
            right: 0;
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--success-color);
        }


        /* Buttons */
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px 25px;
            border-radius: 50px; /* Pill shape for modern look */
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            margin: 10px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            min-width: 180px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .download-btn {
            background-color: var(--primary-color);
            color: #fff;
        }
        .download-btn:hover {
            background-color: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 123, 255, 0.3);
        }

        .next-btn {
            background-color: var(--success-color);
            color: white;
        }
        .next-btn:hover {
            background-color: #1e7e34;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.3);
        }

        .finish-btn {
            background-color: var(--danger-color);
            color: white;
        }
        .finish-btn:hover {
            background-color: #bd2130;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(220, 53, 69, 0.3);
        }

        /* Info/Note Box */
        .info-box {
            background: #e9f7fe;
            padding: 20px;
            border-radius: var(--border-radius);
            border: 1px solid #cce5ff;
            margin-top: 30px;
            text-align: left;
        }
        .info-box p {
            margin: 0;
            color: var(--primary-color);
            font-size: 0.95rem;
        }

        /* Footer */
        .footer-note {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        @media (max-width: 500px) {
            h2 { font-size: 1.5rem; }
            .container { padding: 25px 20px; }
            .action-btn { 
                width: 100%;
                margin: 8px 0;
            }
        }
    </style>
</head>
<body>
<div class="container">
    <?php if (isset($completed) && $completed): ?>
        <h2 class="celebration">🎉 Course Completed!</h2>
        <p>Congratulations on reaching the finish line! You’ve successfully completed all modules of this course.</p>
        
        <div class="progress-bar-container">
            <div class="progress" style="width:100%"></div>
            <span class="progress-text" style="color:var(--success-color);">100% Complete</span>
        </div>

        <form method="post">
            <button type="button" class="action-btn download-btn" onclick="window.location.href='student.php?page=my_learning_new.php'">
                <span style="margin-right: 5px;">🏠</span> Back to Dashboard
            </button>
            <button type="button" class="action-btn download-btn" style="background-color: #17a2b8;">
                <span style="margin-right: 5px;">📜</span> Download Certificate
            </button>
        </form>

    <?php elseif ($rest_array): ?>
        <h2><?php echo htmlspecialchars($rest_array['title']); ?></h2>
        <p class="description"><?php echo htmlspecialchars($rest_array['description']); ?></p>

        <div class="progress-bar-container">
            <div class="progress"></div>
            <span class="progress-text"><?php echo isset($pro_array) ? round($pro_array['progress']) : 0; ?>% Complete</span>
        </div>

        <?php if (!empty($rest_array['file_path'])): ?>
            <div class="info-box">
                <p><strong>Material Available:</strong> Download or view the notes/resources for this module.</p>
                <a class="action-btn download-btn" href="<?php echo htmlspecialchars($rest_array['file_path']); ?>" target="_blank" style="margin-top: 15px;">
                    <span style="margin-right: 5px;">&#128193;</span> View Module Notes
                </a>
            </div>
        <?php else: ?>
            <div class="info-box" style="border-left: 4px solid #ffc107; background: #fffde7;">
                <p style="color: #856404;">*Note: No dedicated file or resource is attached to this module yet.*</p>
            </div>
        <?php endif; ?>

        <form method="post" style="margin-top: 30px;">
            <?php if ($next_module_number <= $i): ?>
                <input type="submit" name="check_p" value="Complete & Next Module →" class="action-btn next-btn">
            <?php else: ?>
                <input type="submit" name="finish_course" value="Finish Course 🎯" class="action-btn finish-btn">
            <?php endif; ?>
        </form>
    <?php else: ?>
        <h3 style="color: var(--danger-color);">Module Not Found</h3>
        <p>We couldn't locate the next module in the sequence.</p>
        <div class="info-box" style="border-left: 4px solid #ffc107; background: #fffde7;">
            <p style="color: #856404;">*Debug Info: Could not find module: "<?php echo htmlspecialchars($next_module_title ?? 'N/A'); ?>"*</p>
        </div>
        <button type="button" class="action-btn download-btn" onclick="window.location.href='student.php?page=my_learning_new.php'">
            <span style="margin-right: 5px;">🏠</span> Go Back
        </button>
    <?php endif; ?>

    <p class="footer-note">Keep learning — every module brings you closer to expertise. Designed for a smooth learning experience.</p>
</div>
</body>
</html>