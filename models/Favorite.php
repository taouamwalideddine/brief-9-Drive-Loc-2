<?php
class Favorite {
    private $conn;
    private $table = 'favorites';

    public $user_id;
    public $article_id;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function exists() {
        $query = "SELECT COUNT(*) FROM " . $this->table . " WHERE user_id = :user_id AND article_id = :article_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":article_id", $this->article_id);
        $stmt->execute();
        return $stmt->fetchColumn() > 0;
    }

    public function add() {
        $query = "INSERT INTO " . $this->table . " (user_id, article_id) VALUES (:user_id, :article_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":article_id", $this->article_id);
        return $stmt->execute();
    }

    public function remove() {
        $query = "DELETE FROM " . $this->table . " WHERE user_id = :user_id AND article_id = :article_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":article_id", $this->article_id);
        return $stmt->execute();
    }
}

