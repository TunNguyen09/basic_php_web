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
    $logged_user = $login->check_login($_SESSION['myapp_ID']);
    $userid = $_SESSION['myapp_ID'];

    $query = "SELECT * FROM upload_assignment ORDER BY CreatedAt DESC";
    $assignments = $db->read($query);
    
    // get img location of user being viewed
    $image = new Image();
    $img = $image->get_profile_img($logged_user);
    
    $user = new User();

    $view_assignment = $db->hasPermission($userid, "view_assignment");
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

        <!-- assignment list -->
        <div class="assignment-list" style="width: 90%; margin: 100px auto 0 auto;">

            <?php
                if ($view_assignment && $assignments) 
                {
                    $found = false;

                    foreach ($assignments as $row) 
                    {
                        if ($logged_user['class_id'] == $row['class_id'])
                        {
                            $found = true;

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

                            echo "<p><small><strong>Uploaded at:</strong> " . $row['CreatedAt'] . "</small></p>";

                            // button to submit homework
                            echo "<a href='submit_assignment.php?assignment_id=" . $row['ID'] . "'>";
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
                    echo "No assignment uploaded yet.";
                }
            ?>
        </div>
    </body>
</html>
