<?php
    session_start();

    include("../classes/login.php");

    $login = new Login();
    $userid = $_SESSION['myapp_ID'];
    $user_data = $login->check_login($userid);

    // getting id of other person
    $target_id = isset($_GET['id']) ? (int)$_GET['id'] : $userid;

    // teacher doesnt have setting like student
    if ($target_id == $userid) 
    {
        if ($user_data['S_Role'] === 'teacher') 
        {
            header("Location: teacher/teacher_setting.php");
        } else 
        {
            header("Location: student/student_setting.php");
        }
        exit;
    } else
    {
        // teacher viewing another student setting
        header("Location: teacher/teacher_setting.php?id=$target_id");
        exit;
    }
?>