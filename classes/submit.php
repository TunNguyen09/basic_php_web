<?php

require_once("db.php");

class Submit
{
        private $error = "";

        // function for teacher to upload homework 
        public function upload_assignment($title, $description, $file, $class_id)
        {
            $db = new Database();

            if (!$file || $file['error'] !== 0) {
                return "File upload error.";
            }

            $fileName = time() . '_' . $file['name'];
            $relative_path = "/assignments/" . $fileName;
            $full_path = "/var/www/Intern/assignments/" . $fileName;

            if (move_uploaded_file($file["tmp_name"], $full_path)) {
                // Lưu thông tin vào DB
                $title = trim($title);
                $description = trim($description);

                $query = "INSERT INTO upload_assignment (Title, Description, FilePath, CreatedAt, class_id)
                        VALUES (:title, :description, :path, NOW(), :class_id)";
                $params = [ ':title' => $title, ':description' => $description, ':path' => $relative_path, ':class_id' => $class_id ];

                $db->write($query, $params);

                return "Assignment uploaded successfully.";

            } else {
                return "Failed to move file.";
            }
        }

        // function for teacher to upload challenges
        public function upload_challenges($title, $description, $file, $hint, $class_id)
        {
            $db = new Database();

            if (!$file || $file['error'] !== 0) {
                return "File upload error.";
            }

            $fileName = time() . '_' . $file['name'];
            $relative_path = "/challenges/" . $fileName;
            $full_path = "/var/www/Intern/challenges/" . $fileName;

            if (move_uploaded_file($file["tmp_name"], $full_path)) {
                // Lưu thông tin vào DB
                $title = trim($title);
                $description = trim($description);

                $query = "INSERT INTO challenges (Title, Description, FilePath, CreatedAt, Hint, class_id)
                        VALUES (:title, :description, :path, NOW(), :hint, :class_id)";
                $params = [
                    ':title' => $title,
                    ':description' => $description,
                    ':path' => $relative_path,
                    ':hint' => $hint,
                    ':class_id' => $class_id
                ];

                $db->write($query, $params);

                return "Challenge uploaded successfully.";

            } else {
                return "Failed to move file.";
            }
        }
    }
?>
