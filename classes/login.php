<?php
    include_once("db.php"); 

    class Login
    {
        private $error = "";

        public function evaluate($data) 
        {

            $email = strtolower(trim($data['email']));
            $password = $data['password']; 

            $query = "SELECT * FROM `users` WHERE Email = :email LIMIT 1;";
            $params = [ ':email' => $email ];

            $db = new Database();
            $result = $db->read($query, $params);

            if($result)
            {
                $row = $result[0];

                if(password_verify($password, $row['Password']))
                {
                    // create session
                    $_SESSION['myapp_ID'] = $row['S_ID'];

                } else
                {
                    $this->error .= "Wrong password!<br>";
                }
            } else 
            {
                $this->error .= "No such email found!<br>";
            }
            return $this->error;
        }

        public function check_login($userid)
        {
            if(is_numeric($userid))
            {
                $query = "SELECT * FROM users WHERE S_ID = :id LIMIT 1;";
                $params = [ ':id' => $userid ];

                $db = new Database();
                $result = $db->read($query, $params);

                if($result)
                {
                    $user_data = $result[0];
                    return $user_data;
                } else
                {
                    header("Location: ../auth/login.php");
                    die;
                }
            } else
            {
                header("Location: ../auth/login.php");
                die;
            }
        }
    }
?>