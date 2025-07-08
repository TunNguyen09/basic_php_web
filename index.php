<?php
    session_start();

    include("classes/db.php");
    include("classes/login.php");

    $login = new Login();
    
    // Check if user is logged in
    if (isset($_SESSION['myapp_ID'])) 
    {
        $user_data = $login->check_login($_SESSION['myapp_ID']);
        if ($user_data['S_Role'] === 'teacher') 
        {
            header("Location: dashboard/teacher/teacher_dashboard.php");
            exit;
        } else 
        {
            header("Location: dashboard/student/profile.php");
            exit;
        }  
    } else
    {
        // Redirect to login page
        header("Location: auth/login.php");
        exit;
    }
?>