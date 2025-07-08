<?php
class Signup 
{
    private $error = "";

    public function evaluate($data) 
    {
        foreach ($data as $key => $value)
        {
            // check if boxes are filled
            if(empty($value))
            {
                $this->error .= $key . " is empty!<br>";
            }

            // check if email is correct
            if (isset($data['email'])) {
                $email = trim($data['email']);
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->error .= "Invalid email!<br>";
                }
            }

            // check if name is alphebet only
            if($key == "firstname" || $key == "lastname")
            {
                if (is_numeric($value))
                {
                    $this->error .= "Can't have number in name!<br>";
                }
                if (strstr($value, " "))
                {
                    $this->error .= "Can't have spaces in name!<br>";
                }
            }
        }

        if($this->error == "")
        {
            $this->create_user($data);
        } else 
        {
            return $this->error;
        }
    }

    public function create_user($data)
    {
        $firstname = ucfirst(trim($data['firstname']));
        $lastname = ucfirst(trim($data['lastname']));
        $email = trim($data['email']);

        $hash_password = password_hash($data['password'], PASSWORD_DEFAULT); 

        $query = "INSERT INTO `users` (Fname, Lname, Email, Password) 
                VALUES (:firstname, :lastname, :email, :hash_password);";
        
        $params = [ ':firstname' => $firstname, ':lastname' => $lastname, 
                ':email' => $email, ':hash_password' => $hash_password ];

        $db = new Database();
        $db->write($query, $params);
    }
}

?>