<?php
class Comment {
    private $conn;
    private $table = 'comments';

    public $id;
    public $user_id;
    public $article_id;
    public $content;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($article_id) {
        $query = "SELECT c.*, u.name as user_name 
                  FROM " . $this->table . " c
                  LEFT JOIN users u ON c.user_id = u.id
                  WHERE c.article_id = :article_id
                  ORDER BY c.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        return $stmt;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, article_id=:article_id, content=:content";
        
        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->article_id = htmlspecialchars(strip_tags($this->article_id));
        $this->content = htmlspecialchars(strip_tags($this->content));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":article_id", $this->article_id);
        $stmt->bindParam(":content", $this->content);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET content=:content
                  WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);

        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id=:id AND user_id=:user_id";
        
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));

        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

