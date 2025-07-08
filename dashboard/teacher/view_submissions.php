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

    $image = new Image();
    $img = $image->get_profile_img($logged_user);

    $grade_submission = $db->hasPermission($userid, "grade_assignment");
    if ($grade_submission)
    {
        // Lấy thông tin bài tập
        $assignment_id = (int) $_GET['id'];
        $params = [':id' => $assignment_id];
        $assignment = null;
        $assignment_query = "SELECT * FROM upload_assignment WHERE ID = :id LIMIT 1";
        $assignment_result = $db->read($assignment_query, $params);
        if ($assignment_result && count($assignment_result) > 0) {
            $assignment = $assignment_result[0];
        }
        
        // Lấy bài nộp mới nhất của mỗi học sinh cho bài này
        $submissions_query = "
            SELECT ws.ID, ws.Post, ws.UserID, ws.Date, ws.Grade, u.Fname, u.Lname 
            FROM work_submission ws
            INNER JOIN users u ON ws.UserID = u.S_ID
            INNER JOIN (
                SELECT UserID, MAX(ID) as MaxID
                FROM work_submission
                GROUP BY UserID
            ) latest ON ws.UserID = latest.UserID AND ws.ID = latest.MaxID
            WHERE ws.assignment_id = :id
        ";
        $submissions = $db->read($submissions_query, $params);
    } else
    {
        die("No access!");
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>View Assignment Submissions</title>
        <link rel="stylesheet" href="/asset/css/base.css">
        <link rel="stylesheet" href="/asset/css/layout.css">
        <link rel="stylesheet" href="/asset/css/forms.css">
        <link rel="stylesheet" href="/asset/css/components.css">
    </head>

    <body style="font-family:Tahoma; background-color: #e9ebee; padding: 20px;">
        
        <?php include("../../includes/profile_bar.php"); ?>

        <div style="max-width: 800px; margin: auto; background: #fff; padding: 20px; border-radius: 10px;">
            <h2>Assignment Detail</h2>
            <?php if ($assignment): ?>
                <p><strong>Title:</strong> <?= htmlspecialchars($assignment['Title']) ?></p>
                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($assignment['Description'])) ?></p>
                <p><strong>Uploaded at:</strong> <?= $assignment['CreatedAt'] ?></p>
                <?php
                    $ext = strtolower(pathinfo($assignment['FilePath'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                        echo "<img src='" . $assignment['FilePath'] . "' style='max-width: 400px;'><br>";
                    } else {
                        echo "📎 <a href='" . $assignment['FilePath'] . "' download>" . basename($assignment['FilePath']) . "</a><br>";
                    }
                ?>
            <?php else: ?>
                <p style="color:red;">Assignment not found.</p>
            <?php endif; ?>
        </div>

        <div style="max-width: 800px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 10px;">
            <h2>Student Submissions (Latest Only)</h2>
            <?php if ($submissions): ?>
                <?php foreach ($submissions as $sub): ?>
                    <div style="margin-bottom: 20px; border-bottom: 1px solid #ccc; padding-bottom: 10px;">
                        <p><strong><?= htmlspecialchars($sub['Fname'] . " " . $sub['Lname']) ?></strong> (User ID: <?= $sub['UserID'] ?>)</p>
                        <p><small>Submitted at: <?= $sub['Date'] ?></small></p>
                        <?php
                            $file_ext = strtolower(pathinfo($sub['Post'], PATHINFO_EXTENSION));
                            if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])) {
                                echo "<img src='" . $sub['Post'] . "' style='max-width: 300px'><br>";
                            } else {
                                echo "📎 <a href='" . $sub['Post'] . "' download>" . basename($sub['Post']) . "</a><br>";
                            }

                            $submission_id = $sub['ID'];
                            // if not null then get existing grade
                            $existing_grade = '';
                            if (isset($sub['Grade']) && $sub['Grade'] !== null) 
                            {
                                $existing_grade = htmlspecialchars($sub['Grade']);
                            }
                            echo '<form action="grade_assignment.php" method="POST">';
                            echo '<input type="hidden" name="submission_id" value="' . $submission_id . '">';
                            echo '<label>Grade: <input type="text" name="grade" value="' . htmlspecialchars($existing_grade) . '"></label>';
                            echo '<button type="submit">Save</button>';
                            echo '</form>';
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No student submissions yet.</p>
            <?php endif; ?>
        </div>
    </body>
</html>
