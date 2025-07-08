<?php
    session_start();

    include("../../classes/db.php");
    include("../../classes/login.php");
    include("../../classes/image.php");

    $db = new Database();
    $login = new Login();

    $userid = $_SESSION['myapp_ID'];
    $current_user = $login->check_login($userid);   // the user that logged in

    // get img location of user being viewed
    $image = new Image();
    $img = $image->get_profile_img($current_user);

    // student permission
    $s_Permission = $db->hasPermission($userid, 'edit_own_comment');
    // teacher permission
    $t_Permission = $db->hasPermission($userid, 'edit_any_comment');
    
    // check if message exist
    if (isset($_GET['message_id']) && is_numeric($_GET['message_id'])) {
        $message_id = (int)$_GET['message_id']; // get message id
    } else {
        die("Invalid message ID.");
    }
    
    $read_params = [':message_id' => $message_id ];
    $query = "SELECT * FROM messages WHERE message_id = :message_id";

    $results = $db->read($query, $read_params);
    $result= $results[0];
    $receiver_id = $result['receiver_id'];
    $message = $result['message_text'];
    
    if (isset($_POST['comment']) && !empty($_POST['comment'])) {
        $canEditOwn = $result['sender_id'] == $userid && $s_Permission;
        $canEditAny = $t_Permission;

        if ($canEditOwn || $canEditAny) {
            $comment = addslashes($_POST['comment']);

            $query = "UPDATE messages SET message_text = :comment, time_sent = NOW() WHERE message_id = :message_id";
            $params = [ ':message_id' => $message_id, ':comment' => $comment ];
            
            if ($canEditOwn) {
                $query .= " AND sender_id = :userid";
                $params[':userid'] = $userid;
            }

            $db->write($query, $params);
            header("Location: profile.php?id=$receiver_id");
            exit;
        } else {
            echo "<p style='color:red;'>Bạn không có quyền chỉnh sửa comment này.</p>";
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['delete'] === 'yes') {
        // Nếu là người gửi comment và có quyền delete_own_comment
        if ($result['sender_id'] == $userid && $db->hasPermission($userid, 'delete_own_comment')) {
            $delete_query = "DELETE FROM messages WHERE message_id = :message_id AND sender_id = :userid";
            $params = [ ':message_id' => $message_id, ':userid' => $userid ];
            $db->write($delete_query, $params);
            header("Location: profile.php?id=$receiver_id");
            exit;
        }

        // Nếu là giáo viên có quyền delete_any_comment
        if ($db->hasPermission($userid, 'delete_any_comment')) {
            $delete_query = "DELETE FROM messages WHERE message_id = :message_id";
            $params = [ ':message_id' => $message_id ];
            $db->write($delete_query, $params);
            header("Location: profile.php?id=$receiver_id");
            exit;
        }

        // Không có quyền
        echo "<p style='color:red;'>Dont have permission to delete this comment.</p>";
    }

    $view_teacher = $db->hasPermission($userid, "view_teacher_profile");

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

    <body>
        <!-- bar -->
        <div id="profile_bar">
            <a href="<?= $view_teacher ? '../../dashboard/teacher/teacher_dashboard.php' : 'profile.php' ?>" class="nav-link">
                Myapp
            </a>

            <a href="../user_list.php" class="nav-link" style="margin-left: 20px;">Students</a>

            <a href="/setting.php?id=<?= $current_user['S_ID'] ?>">
                <img src="<?= $img ?>" id="profile_image">
            </a>

            <a href="/auth/logout.php" id="profile_logout">Logout</a>
        </div>

        <br><br>

        <div class="comment-box">
            <form method="post">
                <textarea name="comment" placeholder="Comment" rows="5" required> <?php echo htmlspecialchars($message); ?> </textarea>
                <br>
                <button type="submit" name="Post">Edit Comment</button>
            </form>

            <form method="post" style="margin-top: -10px;">
                <input type="hidden" name="message_id" value="<?= $message_id ?>">
                <button type="submit" name="delete" value="yes">Delete</button>
            </form>
        </div>
    </body>
</html>