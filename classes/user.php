<?php
    class User
    {
        public function can_submit($user_class, $assignment_class)
        {
            if ($user_class == $assignment_class)
            {
                return true;
            } else
            {
                return false;
            }
        }
    }

?>