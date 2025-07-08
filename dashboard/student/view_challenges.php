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
    $logged_user = $login->check_login($_SESSION['myapp_ID']);
    $userid = $_SESSION['myapp_ID'];

    $query = "SELECT * FROM challenges ORDER BY CreatedAt DESC";
    $challenges = $db->read($query);

    // get img location of user being viewed
    $image = new Image();
    $img = $image->get_profile_img($logged_user);

    $view_challenge = $db->hasPermission($userid, "view_challenge");
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Profile Page</title>
        <link rel="stylesheet" href="/asset/css/base.css">
        <link rel="stylesheet" href="/asset/css/layout.css">
        <link rel="stylesheet" href="/asset/css/forms.css">
        <link rel="stylesheet" href="/asset/css/components.css">
    </head>

    <body style="font-family:Tahoma; background-color: #e9ebee;">

        <!-- bar -->
        <?php include("../../includes/profile_bar.php"); ?>

        <!-- challeng list -->
        <div class="challenge-list" style="width: 90%; margin: 100px auto 0 auto;">

            <?php
                if ($view_challenge && $challenges) 
                {
                    foreach ($challenges as $row) 
                    {
                        if ($row['class_id'] == $logged_user['class_id'])
                        {

                            echo "<div style='border: 1px solid #aaa; padding: 10px; margin-bottom: 15px; background: #f9f9f9'>";
                            echo "<h3>" . htmlspecialchars($row['Title']) . "</h3>";
    
                            // if file is image then show
                            $ext = strtolower(pathinfo($row['FilePath'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) 
                            {
                                echo "<img src='" . $row['FilePath'] . "' style='max-width: 300px'><br>";
                            } 
                            else 
                            {
                                // show filename only
                                echo "📎 <a href='" . $row['FilePath'] . "' download>" . basename($row['FilePath']) . "</a><br>";
                            }
    
                            echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($row['Description'])) . "</p>";
    
                            if (!empty($row['Hint'])) {
                                echo "<details style='margin-top: 8px;'>
                                        <summary style='cursor:pointer; font-weight:bold;'>💡 View Hint</summary>
                                        <div style='padding: 8px; background-color:#eef; border-radius:6px; margin-top:6px;'>" . nl2br(htmlspecialchars($row['Hint'])) . "</div>
                                    </details>";
                            }
    
                            echo "<p><small><strong>Uploaded at:</strong> " . $row['CreatedAt'] . "</small></p>";
    
                            // button to submit homework
                            echo "<a href='submit_challenge.php?challenge_id=" . $row['ID'] . "'>";
                            echo "<button style='margin-top: 10px; padding: 8px 16px; background-color: #4CAF50; color: white; border: none; border-radius: 5px;'>Nộp bài</button>";
                            echo "</a>";
                            echo "</div>";
                        }
                    }
                    
                    if (!$found) 
                    {
                        echo "No assignment uploaded yet.";
                    }
                }
                else 
                {
                    echo "No challenge uploaded yet.";
                }
            ?>
        </div>
    </body>
</html>
