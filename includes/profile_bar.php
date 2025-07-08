<?php
    $login = new Login();
    $logged_user = $login->check_login($_SESSION['myapp_ID']);
    
    $image = new Image();
    $img = $image->get_profile_img($logged_user);

    $db = new Database();
    $result = $db->hasPermission($logged_user['S_ID'], "view_teacher_profile");
?>

<div id="profile_bar">
    <a href="<?= $result ? '/dashboard/teacher/teacher_dashboard.php' : '/dashboard/student/profile.php' ?>" class="nav-link">Myapp</a>

    <a href="/dashboard/user_list.php" class="nav-link">Students</a>

    <?php if ($result): ?>
        <a href="/dashboard/teacher/list_assignments.php" class="nav-link">Assignments</a>

        <a href="/dashboard/teacher/list_challenges.php" class="nav-link">Challenges</a>
    <?php else: ?>
        <a href="/dashboard/student/view_assignment.php" class="nav-link">Assignments</a>

        <a href="/dashboard/student/view_challenges.php" class="nav-link">Challenges</a>
    <?php endif; ?>

    <?php if ($result): ?>
        <img src="<?= $img ?>">
    <?php else: ?>
        <a href="/dashboard/setting.php?id=<?= $logged_user['S_ID'] ?>">
            <img src="<?= $img ?>" id="profile_image" alt="Profile">
        </a>
    <?php endif; ?>

    <a href="/auth/logout.php" id="profile_logout">Logout</a>
</div>
