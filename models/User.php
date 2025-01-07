<?php
class User {
    private $conn;
    private $table_name = "Users";

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $created_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $password) {
        $query = "SELECT id, name, email, password, role 
                FROM " . $this->table_name . " 
                WHERE email = :email";

        $stmt = $this->conn->prepare($query);

        $email = htmlspecialchars(strip_tags($email));

        $stmt->bindParam(":email", $email);

        $stmt->execute();

        $num = $stmt->rowCount();

        if($num > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if(password_verify($password, $row['password'])) {
                $this->id = $row['id'];
                $this->name = $row['name'];
                $this->email = $row['email'];
                $this->role = $row['role'];
                
                return true;
            }
        }
        
        return false;
    }

    public function startSession() {
        $_SESSION['user_id'] = $this->id;
        $_SESSION['user_name'] = $this->name;
        $_SESSION['user_email'] = $this->email;
        $_SESSION['user_role'] = $this->role;
    }

    public static function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    public static function logout() {
        session_destroy();
        session_unset();
        
        header("Location: login.php");
        exit();
    }
}
