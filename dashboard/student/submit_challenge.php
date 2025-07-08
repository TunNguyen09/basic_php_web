<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();

    include("../../classes/db.php");
    include("../../classes/login.php");
    include("../../classes/image.php");

    $db = new Database();
    $login = new Login();
    $userid = $_SESSION['myapp_ID'];
    $logged_user = $login->check_login($userid);

    // Get user image
    $image = new Image();
    $img = $image->get_profile_img($logged_user);

    // Get challenge_id from GET or POST
    $challenge_id = isset($_GET['challenge_id']) 
        ? (int)$_GET['challenge_id'] 
        : (isset($_POST['challenge_id']) ? (int)$_POST['challenge_id'] : 0);

    $challenge = null;
    if ($challenge_id > 0) {
        $query = "SELECT * FROM challenges WHERE ID = :id";
        $read_params = [ ':id' => $challenge_id ];
        
        $results = $db->read($query, $read_params);
        if ($results && count($results) > 0) {
            $challenge = $results[0];
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
                $query = "INSERT INTO challenge_submission (challenge_id, UserID, Post, class_id) 
                        VALUES (:id, :userid, :file, :class_id)";
                $params = [ ':id' => $challenge_id, ':userid' => $userid, ':file' => $filename, ':class_id' => $challenge['class_id'] ];

                $db->write($query, $params);

                header("Location: submit_challenge.php?challenge_id=$challenge_id");
                exit;
            } else {
                echo "Error uploading file!";
            }
        }
    }

    // Check submission status
    $submission_status = "No submission";
    if ($challenge_id > 0) {
        $check_query = "SELECT * FROM challenge_submission
                        WHERE challenge_id = :id AND UserID = :userid LIMIT 1";
        $check_params = [ ':id' => $challenge_id, ':userid' => $userid ];
        
        $check_result = $db->read($check_query, $check_params);
        if ($check_result && count($check_result) > 0) {
            $submission_status = "Submitted";
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Submit Challenge</title>
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
        <?php if ($challenge): ?>
            <div style='border: 1px solid #aaa; padding: 10px; background: #f9f9f9'>
                <h2><?= htmlspecialchars($challenge['Title']) ?></h2>
                <p><strong>Description:</strong> <?= nl2br(htmlspecialchars($challenge['Description'])) ?></p>
                <p><small><strong>Created At:</strong> <?= $challenge['CreatedAt'] ?></small></p>
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
    <?php if ($challenge): ?>
        <div style="width: 90%; margin: 30px auto;">
            <h3>Submission:</h3>
            <form method="post" enctype="multipart/form-data" action="submit_challenge.php?challenge_id=<?= $challenge_id ?>">
                <input type="file" name="file" required><br><br>
                <button type="submit">Submit</button>
            </form>
        </div>
    <?php endif; ?>

    </body>
</html>
