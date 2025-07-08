<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();

    include("../../classes/db.php");
    include("../../classes/login.php");
    include("../../classes/submit.php");
    include("../../classes/image.php");
    include("../../classes/assignment.php");

    $login = new Login();
    $db = new Database();

    $userid = $_SESSION['myapp_ID'];
    $target_id = isset($_GET['id']) ? (int)$_GET['id'] : $userid;
    $target_data = $login->check_login($target_id); // người đang được xem
    $logged_user = $login->check_login($userid);   // người đăng nhập

    $student_permission = $db->hasPermission($userid, 'edit_own_profile');
    $teacher_permission = $db->hasPermission($userid, 'view_student_profile');

    if($student_permission || $teacher_permission)
    {
        // get comment to display
        if ($target_id == $userid) 
        {
            // current user
            $query = "SELECT messages.*, users.Fname, users.Lname
                    FROM messages
                    JOIN users ON messages.sender_id = users.S_ID
                    WHERE receiver_id = :message_id
                    ORDER BY time_sent DESC";
            $read_params = [ ':message_id' => $userid ];     
        } else 
        {
            // other user
            $query = "SELECT messages.*, users.Fname, users.Lname
                    FROM messages
                    JOIN users ON messages.sender_id = users.S_ID
                    WHERE receiver_id = :message_id
                    ORDER BY time_sent DESC";
            $read_params = [ ':message_id' => $target_id ];  
        }
    
        $messages = $db->read($query, $read_params);
    
        // uploading file
        if ($_SERVER['REQUEST_METHOD'] == "POST") 
        {
            if(isset($_FILES['file']['name']) && $_FILES['file']['name'] != "") 
            {
                $folder = __DIR__ . "/submissions/" . $logged_user['S_ID'] . "/";
                if (!file_exists($folder)) 
                {
                    mkdir($folder, 0777, true);
                }
    
                $filename = "/submissions/" . $logged_user['S_ID'] . '/' . $_FILES['file']['name'];
                if (move_uploaded_file($_FILES['file']['tmp_name'], __DIR__ . $filename)) 
                {
                    $query = "INSERT INTO work_submission (Post, UserID) VALUES (:filename, :userid)";
                    $params = [ ':userid' => $userid, ':filename' => $filename ];
    
                    $db->write($query, $params);
                    header("Location: profile.php?id=$target_id");
                    die;
                }
            } elseif (isset($_POST['comment']) && !empty($_POST['comment']))
            {
                $comment = addslashes($_POST['comment']);
    
                $query = "INSERT INTO messages (sender_id, receiver_id, message_text, time_sent)
                        VALUES (:userid, :target_id, :comment, NOW())";
                $params = [ ':userid' => $userid, ':target_id' => $target_id, ':comment' => $comment ];
    
                $db->write($query, $params);
                header("Location: profile.php?id=$target_id");
                die;
            }
        }

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

    <!-- bar -->
    <?php include("../../includes/profile_bar.php"); ?>

    <!-- profile content -->
    <div style="background-color: #e9ebee; text-align: center;">

        <img id="profile_pic" src="<?php echo $target_data['profile_image']; ?>">
        <br>
        
        <div style="font-size: 30px; color: black;">
            <a href="../setting.php?id=<?= $target_data['S_ID'] ?>" style="color: black; text-decoration: none">
                <?php echo $target_data['Fname'] . " " . $target_data['Lname']; ?>
            </a>
        </div>

        <br><br>

        <?php if($target_id !== $userid): ?>
            <div style="border:solid thin #aaa; padding: 10px; background-color: white; min-height: 100px;">
                <form method="post" action="">
                    <textarea name="comment" placeholder="Comment" rows="5" cols="50" required></textarea>
                    <br><br>
                    <input id="submit_button" type="submit" value="Post">
                </form>
            </div>
        <?php endif ?>

        <br>

        <div>Comments</div>

        <div style="margin: 20px auto; width: 80%; max-width: 600px; height: 400px; overflow-y: auto; padding: 10px; background-color: #f7f7f7; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1);">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message): ?>
                    <?php $isMe = $message['sender_id'] == $userid;
                            $senderName = $isMe ? "You" : ($message['Fname'] . " " . $message['Lname']);?>

                    <div style="margin-bottom: 15px;
                                padding: 12px 15px;
                                background-color: <?php echo $isMe ? '#dcf8c6' : '#dbd9d9'; ?>;
                                border: 1px solid #ccc;
                                border-radius: 15px;
                                max-width: 75%;
                                word-wrap: break-word;
                                <?php echo $isMe ? 'margin-left:auto; text-align:right;' : 'margin-right:auto; text-align:left;'; ?>">
                        
                        <?php if ($isMe || $db->hasPermission($userid, "edit_any_comment")): ?>
                            <form action="edit_message.php" method="GET">
                                <input type="hidden" name="message_id" value="<?php echo $message['message_id']; ?>">

                                <button type="submit" style="font-size: 15px; background: none; border: none; color: #007bff; cursor: pointer; text-decoration: underline;">
                                    Edit / Update
                                </button>
                            </form>
                        <?php endif; ?>

                        <div style="font-weight: bold; color: #333; padding-top: 10px;"><?php echo htmlspecialchars($senderName); ?></div>
                        
                        <div style="margin: 8px 0;"><?php echo nl2br(htmlspecialchars($message['message_text'])); ?></div>
                        
                        <div style="font-size: 11px; color: #888;">
                            <?php echo date("d M Y, H:i", strtotime($message['time_sent'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="text-align: center; color: gray;">No messages yet.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
