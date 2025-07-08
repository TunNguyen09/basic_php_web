<?php
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
    session_start();

    include("../../classes/db.php");
    include("../../classes/login.php");
    include("../../classes/image.php");

    $db = new Database();
    $login = new Login();

    $userid = $_SESSION['myapp_ID'];
    $current_user = $login->check_login($userid);

    $grade_challenge = $db->hasPermission($userid, "grade_challenge");
    if ($grade_challenge)
    {

        $image = new Image();
        $img = $image->get_profile_img($current_user);
    
        // Get challenge ID
        $challenge_id = (int) $_GET['challenge_id'];
        $params = [ ':id' => $challenge_id ];
    
        // Get challenge details
        $challenge_query = "SELECT * FROM challenges WHERE ID = :id LIMIT 1";
        $challenge_result = $db->read($challenge_query, $params);
        $challenge = $challenge_result[0];
    
        // Get submissions for this challenge
        $sub_query = "
            SELECT cs.ID, cs.UserID, cs.Post, cs.CreatedAt, cs.Grade, u.Fname, u.Lname 
            FROM challenge_submission cs
            JOIN users u ON cs.UserID = u.S_ID
            WHERE cs.challenge_id = :id
            ORDER BY cs.CreatedAt DESC LIMIT 1
        ";
        $submissions = $db->read($sub_query, $params);
    } else
    {
        die("No access");
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
            
        <?php if ($challenge): ?>
                <h2><?= htmlspecialchars($challenge['Title']) ?></h2>

                <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($challenge['Description'])) ?></p>

                <?php if (!empty($challenge['Hint'])): ?>
                    <p><strong>Hint:</strong><br><?= nl2br(htmlspecialchars($challenge['Hint'])) ?></p>
                <?php endif; ?>

                <p><small><strong>Uploaded at:</strong> <?= $challenge['CreatedAt'] ?></small></p>
            <?php else: ?>
                <p style="color:red;">Challenge not found.</p>
            <?php endif; ?>
        </div>

        <div style="max-width: 800px; margin: 30px auto; background: #fff; padding: 20px; border-radius: 10px;">
            
        <h3>Student Submissions</h3>
            
            <?php if ($submissions): ?>
                <?php foreach ($submissions as $sub): ?>
                    <div style="border-bottom: 1px solid #ccc; margin-bottom: 15px; padding-bottom: 10px;">
                        <p><strong><?= htmlspecialchars($sub['Fname'] . " " . $sub['Lname']) ?></strong> (ID: <?= $sub['UserID'] ?>)</p>
                        
                        <p><small>Submitted at: <?= $sub['CreatedAt'] ?></small></p>
                        
                        <?php
                            $ext = strtolower(pathinfo($sub['Post'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg','jpeg','png','gif'])) 
                            {
                                echo "<img src='/dashboard/student" . $sub['Post'] . "' style='max-width:300px;'><br>";
                            } else 
                            {
                                echo "📎 <a href='" . $sub['Post'] . "' download>" . basename($sub['Post']) . "</a><br>";
                            }
                            
                            $submission_id = $sub['ID'];
                            // if not null then get existing grade
                            $existing_grade = '';
                            if (isset($sub['Grade']) && $sub['Grade'] !== null) 
                            {
                                $existing_grade = htmlspecialchars($sub['Grade']);
                            }
                            echo "<form action='grade_challenge.php' method='POST' style='margin-top: 10px;'>";
                            echo "<input type='hidden' name='submission_id' value='$submission_id'>";
                            echo "<label>Grade: <input type='text' name='grade' value='$existing_grade' style='width: 80px;'></label>";
                            echo "<button type='submit' style='margin-left: 10px;'>Save</button>";
                            echo "</form>";
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No student submissions yet.</p>
            <?php endif; ?>
        </div>
    </body>
</html>
