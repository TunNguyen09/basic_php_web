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
        // getting query by lastest
        $query = "SELECT * FROM upload_assignment ORDER BY CreatedAt DESC";
        $assignments = $db->read($query);
    
        // get img location of user being viewed
        $image = new Image();
        $img = $image->get_profile_img($logged_user);
    } else
    {
        die("Can't see");
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

        <!-- assignment list -->
        <div class="assignment-list" style="width: 90%; margin: 100px auto 0 auto;">

            <?php
                if ($assignments) 
                {
                    $found = false;
                    
                    foreach ($assignments as $row) 
                    {
                        if ($row['class_id'] == $logged_user['class_id'])
                        {
                            $found = true;

                            echo "<div style='border: 1px solid #aaa; padding: 10px; margin-bottom: 15px; background: #f9f9f9'>";
                            echo "<a href='view_submissions.php?id=" . $row['ID'] . "'>" . htmlspecialchars($row['Title']) . "</a>";
    
                            echo "<br><br>";
    
                            // if assignment is a picture or file
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
                    echo "No assignments uploaded yet.";
                }
            ?>

        </div>

    </body>
</html>
