<?php
    include("../classes/db.php");
    include("../classes/signup.php");

    $firstname = "";
    $lastname = "";
    $email = "";

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $signup = new Signup();

        if ($_POST['password'] !== $_POST['password2']) {
            $result = "Passwords do not match!<br>";
        } else {
            $result = $signup->evaluate($_POST);
        }

        if ($result != "") {
            echo "<div id='error'>";
            echo "The following errors occurred:<br>";
            echo $result;
            echo "</div>";
        } else {
            header("Location: login.php");
            die;
        }
    }

    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];

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

    <body style="font-family:Tahoma; background-color: #e9ebee">
        <div id="signup_details">
            Sign up a new account
            <br><br>
            <?php if (!empty($result)) : ?>
                <div id="error">
                    The following errors occurred:<br>
                    <?php echo $result; ?>
                </div>
            <?php endif; ?>


            <form method="post" action="">

                <input value="<?php echo $firstname ?>" name="firstname" type="text" id="text" placeholder="First name"><br><br>
                <input value="<?php echo $lastname ?>" name="lastname" type="text" id="text" placeholder="Last name"><br><br>

                <input value="<?php echo $email ?>" name="email" type="text" id="text" placeholder="Email"><br><br>

                <input name="password" type="password" id="text" placeholder="Password"><br><br>
                <input name="password2" type="password" id="text" placeholder="Retype your password"><br><br>

                <input type="submit" id="signup_button" value="Sign up">
            </form>
        </div>
    </body>
</html>