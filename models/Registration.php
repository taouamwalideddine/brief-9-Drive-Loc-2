<?php
class Registration {
    private $conn;
    private $table_name = "users"; 
    public function __construct($db) {
        $this->conn = $db;
    }

    public function registerUser($name, $email, $password) {
        try {
            $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return "Email already exists!";
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $query = "INSERT INTO " . $this->table_name . " 
                    (name, email, password, role) 
                    VALUES (:name, :email, :password, 'client')";

            $stmt = $this->conn->prepare($query);

            $name = htmlspecialchars(strip_tags($name));
            $email = htmlspecialchars(strip_tags($email));

            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $hashed_password);

            if ($stmt->execute()) {
                return true;
            }

            error_log("SQL Error: " . implode(" | ", $stmt->errorInfo()));
            return "Registration failed. Please try again.";

        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            return "An error occurred while processing your request.";
        }
    }
}
