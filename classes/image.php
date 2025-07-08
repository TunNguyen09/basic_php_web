<?php

    require_once("db.php");

    class Image
    {
        public function get_profile_img($user) 
        {
            if (!empty($user['profile_image']) && file_exists($_SERVER['DOCUMENT_ROOT'] . $user['profile_image'])) 
            {
                return $user['profile_image'];
            } elseif (filter_var($user['profile_image'], FILTER_VALIDATE_URL))
            {
                return $user['profile_image'];
            }

            return "/asset/9334243.jpg";
        }
    }
?>