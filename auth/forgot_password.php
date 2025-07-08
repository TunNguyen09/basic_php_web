<?php
    include("../classes/db.php");
    include("../classes/image.php");

    $db = new Database();

    if ($_SERVER['REQUEST_METHOD'] == "POST") 
    {
        // check for username
        if(isset($_POST['username']))
        {
            $query = "SELECT Email FROM users WHERE Email = :username LIMIT 1";
            $username = $_POST['username'];
            $param = [ ':username' => $username ];
            
            $result = $db->read($query, $param);
        }

        // if username exist update password
        if($result)
        {
            // update student password
            if (isset($_POST['update_password'])) 
            {
                $new_password = addslashes($_POST['new_password']);
                $retype_password = addslashes($_POST['retype_password']);
                
                if (!empty($retype_password) && !empty($new_password)) 
                {
                    if ($new_password === $retype_password) 
                    {
                        $hash_new_password = password_hash($new_password, PASSWORD_DEFAULT);
                        
                        // password matches, proceed with update
                        $update_query = "UPDATE users SET Password = :hash_password WHERE Email = :username;";
                        $param = [ ':username' => $username, ':hash_password' => $hash_new_password ];
                        $db->write($update_query, $param);

                    } else
                    {
                        echo "Retype password has to be the same.";
                    }
                }
            } else
            {
                echo "No username found in database!";
            }

            header("Location: login.php");
            exit;
        }
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Forgot Password</title>
        <link rel="stylesheet" href="/asset/css/base.css">
        <link rel="stylesheet" href="/asset/css/layout.css">
        <link rel="stylesheet" href="/asset/css/forms.css">
        <link rel="stylesheet" href="/asset/css/components.css">
    </head>

    <body style="font-family:Tahoma; background-color: #e9ebee;">

        <!-- cover -->
        <div class="dashboard-container" style="background-color: #e9ebee; text-align: center;">

            <div style="margin-top: 30px; padding: 20px; background-color: white; display: inline-block; border: solid thin #ccc;">
                <form method="post" action="">
                    <input type="text" name="username" placeholder="Username" style="width: 300px; padding: 8px;"><br><br>

                    <label for="password"> New Password: </label><br>
                    <input type="password" name="new_password" placeholder="Password" style="width: 300px; padding: 8px;"><br><br>
                    
                    <label for="password"> Retype Password: </label><br>
                    <input type="password" name="retype_password" placeholder="Retype Password" style="width: 300px; padding: 8px;"><br><br>

                    <input type="submit" name="update_password" value="Update Password" style="padding: 10px 20px; font-size: 16px;">
                </form>
            </div>
        </div>
    </body>
</html>