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

    
    $submission = $db->hasPermission($userid, "view_assignment");
    if ($submission)
    {
        $query = "SELECT * FROM challenges ORDER BY CreatedAt DESC";
        $challenges = $db->read($query);
    
        // get img location of user being viewed
        $image = new Image();
        $img = $image->get_profile_img($logged_user);
        
    } else
    {
        die("NO access");
    }
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

        <?php include("../../includes/profile_bar.php"); ?>

        <!-- challenges list -->
        <div class="challenges-list" style="width: 90%; margin: 100px auto 0 auto;">

            <?php
                if ($challenges) 
                {
                    $found = false;
                    
                    foreach ($challenges as $row) 
                    {
                        if ($row['class_id'] == $logged_user['class_id'])
                        {
                            $found = true;

                            echo "<div style='border: 1px solid #aaa; padding: 10px; margin-bottom: 15px; background: #f9f9f9'>";
                            echo "<a href='view_challenges.php?challenge_id=" . $row['ID'] . "'>" . htmlspecialchars($row['Title']) . "</a>";
                            echo "<br><br>";

                            // file or txt
                            $ext = strtolower(pathinfo($row['FilePath'], PATHINFO_EXTENSION));
                            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif'])) 
                            {
                                echo "<img src='" . $row['FilePath'] . "' style='max-width: 300px'><br>";
                            } 
                            else 
                            {
                                echo "📎 <a href='" . $row['FilePath'] . "' download>" . basename($row['FilePath']) . "</a><br>";
                            }

                            echo "<p><strong>Description:</strong> " . nl2br(htmlspecialchars($row['Description'])) . "</p>";
                            echo "<p><small><strong>Uploaded at:</strong> " . $row['CreatedAt'] . "</small></p>";
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
                    echo "No challenges uploaded yet.";
                }
            ?>

        </div>

    </body>
</html>
