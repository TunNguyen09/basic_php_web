<?php
    // display error
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();

    include("../classes/db.php");
    include("../classes/login.php");

    $login = new Login();
    $db = new Database();

    if ($_SERVER['REQUEST_METHOD'] == 'POST')
    {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        $query = "SELECT * FROM users WHERE Email = :email LIMIT 1";
        $params = [ ':email' => $email ];
        $result = $db->read($query, $params);
    
        if ($result && count($result) > 0) {
            $user = $result[0];
    
            if (password_verify($password, $user['Password'])) {
                $_SESSION['myapp_ID'] = $user['S_ID'];
                // Redirect based on role
                if ($db->hasPermission($user['S_ID'], "view_teacher_profile")) {
                    header("Location: ../dashboard/teacher/teacher_dashboard.php");
                    exit();
                } else {
                    header("Location: ../dashboard/student/profile.php");
                    exit();
                }
            } else {
                echo "<div id='error'>Incorrect password.</div>";
            }
        } else {
            echo "<div id='error'>No user found with that email.</div>";
        }
    }
?>


<!DOCTYPE html>
<html> 
    <head>
        <meta charset="UTF-8">
        <title>Tuan's Website</title>
        <link rel="stylesheet" href="/asset/css/base.css">
        <link rel="stylesheet" href="/asset/css/layout.css">
        <link rel="stylesheet" href="/asset/css/forms.css">
        <link rel="stylesheet" href="/asset/css/components.css">
    </head>

    <body style="font-family:Tahoma; background-color: #e9ebee;">
        <div id="login_details">
    
            <form method="post" action="">
                Log in to your account!
                <br><br>
                
                <input name="email" type="text" id="text" placeholder="Email"><br><br>
                <input name="password" type="password" id="text" placeholder="Password"><br><br>
                
                <input type="submit" id="login_button" value="Log in">
            </form>

            <br>
            
            <a href="forgot_password.php" style="text-decoration: none;">
                Forgot password?
            </a>

            <br>

            <p>Don't have an account? 
                <a href="signup.php" id="signup_link" style="text-decoration: none;">
                    Sign up here
                </a>
            </p>
        </div>
    </body>
</html>