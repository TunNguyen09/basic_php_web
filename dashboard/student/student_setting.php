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
    $image = new Image();

    $login_user_id = $_SESSION['myapp_ID'];
    $logged_user = $login->check_login($login_user_id);

    $s_Permission = $db->hasPermission($login_user_id, 'edit_own_profile');
    if ($s_Permission)
    {
        // 🖼 Handle profile image upload or URL
        if ($_SERVER['REQUEST_METHOD'] === 'POST') 
        {
    
            // 🔹 Image from URL
            if (isset($_POST['upload_url']) && !empty($_POST['profile_url'])) 
            {
                $profile_url = trim($_POST['profile_url']);
                echo $profile_url;
    
                // must be a valid image URL
                if (filter_var($profile_url, FILTER_VALIDATE_URL))
                {
                    $query = "UPDATE students SET profile_image = :path WHERE S_ID = :id";
                    $params = [':id' => $login_user_id, ':path' => $profile_url];
                    $db->write($query, $params);
    
                    header("Location: profile.php?id=$login_user_id");
                    exit;
                } else 
                {
                    echo "❌ Invalid image URL.";
                }
            }
    
            // 🔹 Image file upload
            if (isset($_POST['upload_image']) && isset($_FILES['file']['name']) && $_FILES['file']['name'] !== "") 
            {
                $folder = __DIR__ . "/uploads/" . $logged_user['S_ID'] . "/";
                
                if (!is_dir($folder)) 
                {
                    mkdir($folder, 0777, true);
                }
    
                $filename = basename($_FILES['file']['name']);
                $target_path = $folder . $filename;
                $relative_url = "/dashboard/student/uploads/" . $logged_user['S_ID'] . "/" . $filename;
    
                if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) 
                {
                    $query = "UPDATE students SET profile_image = :path WHERE S_ID = :id";
                    $params = [':id' => $login_user_id, ':path' => $relative_url];
                    $db->write($query, $params);
    
                    header("Location: profile.php?id=$login_user_id");
                    exit;
                } else 
                {
                    echo "❌ Failed to upload image file.";
                }
            }
    
            // 🔐 Handle password change
            if (isset($_POST['update_password'])) 
            {
                $old_password = $_POST['old_password'] ?? '';
                $new_password = $_POST['new_password'] ?? '';
    
                if ($old_password && $new_password) 
                {
                    $current_password = $logged_user['S_Password'];
    
                    if ($current_password && password_verify($old_password, $current_password)) 
                    {
                        $hashed_new = password_hash($new_password, PASSWORD_DEFAULT);
                        $query = "UPDATE students SET S_Password = :pass WHERE S_ID = :id";
                        $params = [':id' => $login_user_id, ':pass' => $hashed_new];
                        $db->write($query, $params);
                    } else 
                    {
                        echo "❌ Old password is incorrect.";
                    }
                }
            }
        }
    
        // 🔎 Get current profile image
        $img = $image->get_profile_img($logged_user);
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

        <div style="text-align: center; margin-top: 30px;">
            <div style="display: inline-block; padding: 20px; background-color: #fff; border: solid thin #ccc;">
                <img id="profile_pic" src="<?php echo $img; ?>"><br><br>

                <!-- 🔸 Upload File Form -->
                <form method="POST" enctype="multipart/form-data" style="margin-bottom: 20px;">
                    <label>Upload a new profile image:</label><br>
                    <input type="file" name="file"><br><br>
                    <input type="submit" name="upload_image" value="Upload">
                </form>

                <!-- 🔹 Upload via URL Form -->
                <form method="POST" action="">
                    <label>Paste image URL:</label><br>
                    <input type="text" name="profile_url" style="width: 300px; padding: 8px;"><br><br>
                    <input type="submit" name="upload_url" value="Use URL">
                </form>

                <!-- 🔐 Change Password -->
                <hr><br>
                <form method="POST" action="">
                    <label>Old Password:</label><br>
                    <input type="password" name="old_password" style="width: 300px;"><br><br>

                    <label>New Password:</label><br>
                    <input type="password" name="new_password" style="width: 300px;"><br><br>

                    <input type="submit" name="update_password" value="Change Password" style="padding: 10px 20px;">
                </form>
            </div>
        </div>
    </body>
</html>
