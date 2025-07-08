<?php
    class Database
    {
        private $host = 'localhost';
        private $user = 'tuan';
        private $pass = 'Tuannguyen5!';
        private $db   = 'myapp';
        private $pdo;

        public function __construct()
        {
            $dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";

            try 
            {
                $this->pdo = new PDO($dsn, $this->user, $this->pass);
                $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) 
            {
                die("Database connection failed: " . $e->getMessage());
            }
        }

        public function read($query, $params = [])
        {
            try 
            {
                $stmt = $this->pdo->prepare($query);
                $stmt->execute($params);
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) 
            {
                die("Read query failed: " . $e->getMessage());
            }
        }

        public function write($query, $params = [])
        {
            try 
            {
                $stmt = $this->pdo->prepare($query);
                return $stmt->execute($params);
            } catch (PDOException $e) 
            {
                die("Write query failed: " . $e->getMessage());
            }
        }

        public function hasPermission($userid, $perm_name)
        {
            $query =    "SELECT 1
                        FROM user_role u
                        JOIN role_permission rp ON u.role_id = rp.role_id
                        JOIN permissions p ON rp.permission_id = p.id
                        WHERE u.user_id = ? AND p.permission_name = ? LIMIT 1;";

            $stmt = $this->pdo->prepare($query);
            $stmt->execute([$userid, $perm_name]);
            
            return $stmt->fetch() !== false;
        }
    }
?>
