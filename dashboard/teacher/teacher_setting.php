<?php
ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();

    include("../../classes/db.php");
    include("../../classes/login.php");
    include("../../classes/user.php");
    include("../../classes/submit.php");
    include("../../classes/image.php");

    $db = new Database();
    $login = new Login();
    $logged_user = $login->check_login($_SESSION['myapp_ID']);
    
    if ($logged_user['S_Role'] !== 'teacher') {
        die("You don't have permission for this website");
    }

    // Get student id
    $target_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

    // Get student info
    $query = "SELECT * FROM users WHERE S_ID = :id LIMIT 1";
    $params = [ ':id' => $target_id];
    $target_data = $db->read($query, $params);
    
    // Check if student exit
    if (!$target_data || count($target_data) == 0) {
        die("Student doesn't exist");
    }
    $target_user = $target_data[0];

    // get img location of user being viewed
    $image = new Image();
    $img = $image->get_profile_img($logged_user);

    $teacher_edit_setting = $db->hasPermission($_SESSION['myapp_ID'], "edit_student_profile");
    if ($teacher_edit_setting)
    {
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $db = new Database();
    
            // check file name
            if (isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") {
                $allowed_image_types = ['image/jpeg', 'image/png', 'image/gif'];
                $mime_type = mime_content_type($_FILES['file']['tmp_name']);
    
                if (in_array($mime_type, $allowed_image_types)) {
                    $folder = __DIR__ . "/../student/uploads/" . $target_id . "/";
    
                    if (!is_dir($folder)) {
                        mkdir($folder, 0777, true);
                    }
    
                    $extension = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
                    $safe_filename = "avatar." . strtolower($extension);
    
                    $target_path = $folder . $safe_filename;
                    $relative_url = "/dashboard/student/uploads/" . $target_id . "/" . $safe_filename;
    
                    if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
                        $query = "UPDATE users SET profile_image = :path WHERE S_ID = :id";
                        $params = [ ':path' => $relative_url, ':id' => $target_id ];
                        $db->write($query, $params);
    
                        header("Location: /dashboard/student/profile.php?id=$target_id");
                        exit;
                    }
                } else {
                    echo "Only accept jpeg, png, gif.";
                }
            }
    
            // update name when post got new names
            if(isset($_POST['update_name'])) {
                $first_name = trim($_POST['first_name']);
                $last_name = trim($_POST['last_name']);
    
                if ($first_name !== "" && $last_name !== "") {
                    $query = "UPDATE users SET S_Fname = :fname, S_Lname = :lname WHERE S_ID = :id;";
                    $params = [ ':fname'=> $first_name, ':lname' => $last_name, ':id' => $target_id ];
                    $db->write($query, $params);
                }
    
                header("Location: teacher_setting.php?id=$target_id");
            }
    
            // check user login details again
            $target_user = $login->check_login($target_id);
        }
    } else
    {
        die("Can't access");
    }
?>

<!DOCTYPE html>
<html>
    <head>
        <title>
            Profile Page
        </title>
        <link rel="stylesheet" href="/asset/css/base.css">
        <link rel="stylesheet" href="/asset/css/layout.css">
        <link rel="stylesheet" href="/asset/css/forms.css">
        <link rel="stylesheet" href="/asset/css/components.css">
    </head>

    <body style="font-family:Tahoma; background-color: #e9ebee;">

        <?php include("../../includes/profile_bar.php"); ?>

        <!-- cover -->
        <div id="" style="background-color: #e9ebee; text-align: center;">

            <div style="margin-top: 30px; padding: 20px; background-color: white; display: inline-block; border: solid thin #ccc;">
                <form method="post" enctype="multipart/form-data">
                    <img id="profile_pic" src="<?php echo $img; ?>">
            
                    <br><br>
                    Change image
                    <br>

                    <input type="file" name="file">
                    <input type="submit" name="upload_image" value="Upload">
                    
                    <br><br>
                </form>
                
                <form method="post" action="">
                    <label for="first_name">First Name:</label><br>
                    <input type="text" name="first_name" placeholder="First name" style="width: 300px; padding: 8px;"><br><br>

                    <label for="last_name">Last Name:</label><br>
                    <input type="text" name="last_name" placeholder="Last name" style="width: 300px; padding: 8px;"><br><br>

                    <input type="submit" name="update_name" value="Update Name" style="padding: 10px 20px; font-size: 16px;">

                    <br>
                </form>
            </div>
        </div>
    </body>
</html>