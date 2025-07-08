<?php
    require_once("db.php");

    class Assignment
    {
        public function get_assignment_by_id($assignment_id)
        {
            $db = new Database();

            $assignment_id = (int)$assignment_id;
            $query = "SELECT Title, Description, CreatedAt FROM upload_assignment 
                  WHERE ID = :id LIMIT 1";
            $params = [ ':id' => $assignment_id ];

            $assignments = $db->read($query, $params);

            if ($assignments && count($assignments) > 0) {
                return $assignments[0];
            }

            return null;
        }

        public function get_assignment_sorted()
        {
            $db = new Database();
            $query = "SELECT * FROM upload_assignment ORDER BY CreatedAt DESC";
            
            $result = $db->read($query);
            return $result; 
        }

    }
?>
