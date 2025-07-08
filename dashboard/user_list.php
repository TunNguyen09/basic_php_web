<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();
    include("../classes/db.php");
    include("../classes/login.php");
    include("../classes/image.php");
    
    $db = new Database();
    $userid = $_SESSION['myapp_ID'];
    $view_permission = $db->hasPermission($userid, "view_student_list");
    
    if ($view_permission)
    {
        $login = new Login();
        $logged_user = $login->check_login($_SESSION['myapp_ID']);

        // Lấy danh sách tất cả người dùng khác
        $query = "
                    SELECT * FROM users u
                    JOIN user_role ur ON u.S_ID = ur.user_id
                    JOIN roles r ON ur.role_id = r.Role_ID
                    WHERE r.Role_name = :role AND u.S_ID != :id
                ";
        $params = [ ':role' => "student", ':id' => $userid ];

        $list = [];
        $list= $db->read($query, $params);

        $hasStudents = is_array($list) && count($list) > 0;

        // getting img for profile
        $image = new Image();
        $img = $image->get_profile_img($logged_user);
    } else
    {
        die("You can't access this page");
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Class member list</title>
        <link rel="stylesheet" href="/asset/css/base.css">
        <link rel="stylesheet" href="/asset/css/layout.css">
        <link rel="stylesheet" href="/asset/css/forms.css">
        <link rel="stylesheet" href="/asset/css/components.css">
    </head>
    <body style="font-family: Tahoma; background-color: #e9ebee;">

        <?php include("../includes/profile_bar.php"); ?>

        <div style="max-width: 800px; margin: auto; padding: 20px;">
            <h2>List of students in the class:</h2>
            
            <?php if ($hasStudents): ?>
                <?php foreach ($list as $row): ?>
                    <?php if ($row['class_id'] == $logged_user['class_id']): ?>
                        <?php
                            $profile_link = "student/profile.php?id=" . $row['S_ID'];
                            $full_name = $row['Fname'] . ' ' . $row['Lname'];
                            $avatar = $row['profile_image'];
                        ?>

                        <div style="background-color: white; padding: 10px; margin-bottom: 10px; border-radius: 10px;">
                            <a href="<?= $profile_link ?>" style="text-decoration: none; color: black;">
                                <img src="<?= $avatar ?>" style="width: 50px; height: 50px; border-radius: 50%; vertical-align: middle;">
                                <span style="margin-left: 10px; font-size: 18px;"><?= $full_name ?></span>
                            </a>
                        </div>
                    <?php endif ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>Can't find any other member</p>
            <?php endif; ?>
        </div>

    </body>
</html>
