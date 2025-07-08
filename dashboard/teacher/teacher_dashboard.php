<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    session_start();
    
    include("../../classes/db.php");
    include("../../classes/login.php");
    include("../../classes/submit.php");
    include("../../classes/image.php");

    $db = new Database();
    $login = new Login();
    $submit = new Submit();

    $userid = $_SESSION['myapp_ID'];
    $logged_user = $login->check_login($userid);

    $result = $db->hasPermission($userid, 'view_student_list');
    $view_teacher = $db->hasPermission($userid, "view_teacher_profile");

    if($result)
    {
        // Student list
        $query_students = "
        SELECT u.S_ID, u.Fname, u.Lname, u.Email, u.class_id 
        FROM users u
        JOIN user_role ur ON u.S_ID = ur.user_id
        JOIN roles r ON ur.role_id = r.Role_ID
        WHERE r.Role_name = 'student'";
        $students = $db->read($query_students);
    
        // Display all homeworks
        // $query_assignments = "SELECT * FROM work_submission WHERE UserID = :id";
        // $assignment_params = [ ':id' => $userid ];
        // $assignments = $db->read($query_assignments, $assignment_params);
        
        if ($_SERVER['REQUEST_METHOD'] == "POST")
        {
            // upload homework
            if ($_POST['type'] === 'homework') {
                $hw_title = addslashes($_POST['hw_title']);
                $hw_description = addslashes($_POST['hw_detail']);
                $hw_file = $_FILES['assignment_file'];
    
                $submit->upload_assignment($hw_title, $hw_description, $hw_file, $logged_user['class_id']);
            }
    
            // upload challenges
            else if ($_POST['type'] === 'challenge') {
                $challenge_title = addslashes($_POST['challenge_title']);
                $challenge_description = addslashes($_POST['challenge_description']);
                $challenge_file = $_FILES['challenges_file'];
                $challenge_hint = addslashes($_POST['challenge_hint']);
    
                $submit->upload_challenges($challenge_title, $challenge_description, $challenge_file, $challenge_hint, $logged_user['class_id']);
            }
        }    
    
        // get img location of user being viewed
        $image = new Image();
        $img = $image->get_profile_img($logged_user);
    } else 
    {
        die("You don't have access to this page");
    }

?>

<!DOCTYPE html>
<html>
    <head>
        <title>Teacher Dashboard</title>
        <link rel="stylesheet" href="/asset/css/base.css">
        <link rel="stylesheet" href="/asset/css/layout.css">
        <link rel="stylesheet" href="/asset/css/forms.css">
        <link rel="stylesheet" href="/asset/css/components.css">
    </head>

    <body style="font-family: Tahoma; background-color: #e9ebee;">
        
        <?php include("../../includes/profile_bar.php"); ?>

        <div style="max-width: 900px; margin: auto; padding: 20px;">
            <h2>
                Teacher: <?php echo $logged_user['Fname'] . ' ' . $logged_user['Lname'] ?>
            </h2>

            <h3>📋 List of students</h3>
            <ul>
                <?php foreach($students as $student): ?>
                    <?php if ($student['class_id'] == $logged_user['class_id']): ?>
                        <li>
                            <a href="/dashboard/student/profile.php?id=<?= $student['S_ID'] ?>">
                                <?php echo $student['Fname'] . ' ' . $student['Lname'] ?> (<?php echo $student['Email'] ?>)
                            </a>
                        </li>
                    <?php endif ?>
                <?php endforeach; ?>
            </ul>

            <h3>📝 Upload new homework</h3>
            <form method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="type" value="homework">
                <label for="hw_title">Homework title:</label><br>
                <input type="text" name="hw_title" required><br><br>

                <textarea name="hw_detail" placeholder="Homework details" required></textarea><br><br>
                
                <input type="file" name="assignment_file" required><br><br>
                <input type="submit" value="Upload">
            </form>

            <h3>🧠 Create Challenge</h3>
            <form method="post" action="" enctype="multipart/form-data">
                <input type="hidden" name="type" value="challenge">
                <label for="challenge_title">Challenge title:</label><br>
                <input type="text" name="challenge_title" required><br><br>
                
                <textarea name="challenge_description" placeholder="Description for challenge" required></textarea><br><br>

                <textarea name="challenge_hint" placeholder="Hint for challenge" required></textarea><br><br>
                
                <input type="file" name="challenges_file" accept=".txt" required><br><br>
                <input type="submit" value="Create challenge">
            </form>
        </div>
    </body>
</html>
