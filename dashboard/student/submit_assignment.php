<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();

    include("../../classes/db.php");
    include("../../classes/login.php");
    include("../../classes/image.php");
    include("../../classes/user.php");

    $db = new Database();
    $login = new Login();
    $userid = $_SESSION['myapp_ID'];
    $logged_user = $login->check_login($userid);

    // Get user image
    $image = new Image();
    $img = $image->get_profile_img($logged_user);

    // Get assignment_id from GET or POST
    $assignment_id = isset($_GET['assignment_id']) 
        ? (int)$_GET['assignment_id'] 
        : (isset($_POST['assignment_id']) ? (int)$_POST['assignment_id'] : 0);

    $assignment = null;
    if ($assignment_id > 0) {
        $query = "SELECT * FROM upload_assignment WHERE ID = :id";
        $read_params = [':id' => $assignment_id ];

        $results = $db->read($query, $read_params);
        if ($results && count($results) > 0) {
            $assignment = $results[0];
        }
    }

    // Handle file upload
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        if (isset($_FILES['file']['name']) && $_FILES['file']['name'] !== "") {
            $folder = __DIR__ . "/submissions/" . $logged_user['S_ID'] . "/";
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $filename = "/submissions/" . $logged_user['S_ID'] . '/' . basename($_FILES['file']['name']);
            $targetPath = __DIR__ . $filename;

            if (move_uploaded_file($_FILES['file']['tmp_name'], $targetPath)) {
                $query = "INSERT INTO work_submission (UserID, Post, assignment_id, class_id) 
                        VALUES (:userid, :file, :id, :class_id)";
                $update_param = [ ':userid' => $userid, ':file' => $filename, ':id' => $assignment_id, ':class_id' => $assignment['class_id'] ];

                $db->write($query, $update_param);
                header("Location: submit_assignment.php?assignment_id=$assignment_id");
                exit;
            } else {
                echo "Error uploading file!";
            }
        }
    }

    // Check submission status
    $submission_status = "No submission";
    if ($assignment_id > 0) {
        $check_query = "SELECT * FROM work_submission 
                        WHERE assignment_id = :id AND UserID = :userid LIMIT 1";
        $check_params = [ ':userid' => $userid, ':id' => $assignment_id ];

        $check_result = $db->read($check_query, $check_params);
        
        if ($check_result && count($check_result) > 0) {
            $submission_status = "Submitted";
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Submit Assignment</title>
        <link rel="stylesheet" href="/asset/css/base.css">
        <link rel="stylesheet" href="/asset/css/layout.css">
        <link rel="stylesheet" href="/asset/css/forms.css">
        <link rel="stylesheet" href="/asset/css/components.css">
    </head>
    <body style="font-family:Tahoma; background-color: #e9ebee;">

    <!-- bar -->
    <?php include("../../includes/profile_bar.php"); ?>

    <!-- Assignment Info -->
    <div style="width: 90%; margin: 100px auto 0 auto;">
        <?php if ($assignment): ?>
            <div style='border: 1px solid #aaa; padding: 10px; background: #f9f9f9'>
                <h2><?= htmlspecialchars($assignment['Title']) ?></h2>
                <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($assignment['Description'])) ?></p>
                <p><small><strong>Created At:</strong> <?= $assignment['CreatedAt'] ?></small></p>
                <p>
                    <strong>Status:</strong>
                    <?php if ($submission_status == "Submitted"): ?>
                        <span style="color: green;">✅ Submitted</span>
                    <?php else: ?>
                        <span style="color: red;">❌ No submission</span>
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <p style="color:red;">No homework.</p>
        <?php endif; ?>
    </div>

    <!-- Upload Form -->
    <?php if ($assignment): ?>
        <div style="width: 90%; margin: 30px auto;">
            <h3>Submission:</h3>
            <form method="post" enctype="multipart/form-data" action="submit_assignment.php?assignment_id=<?= $assignment_id ?>">
                <input type="file" name="file" required><br><br>
                <button type="submit">Submit</button>
            </form>
        </div>
    <?php endif; ?>

    </body>
</html>
