<?php
class Article {
    private $conn;
    private $table = 'articles';

    public $id;
    public $user_id;
    public $theme_id;
    public $title;
    public $content;
    public $status;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read($page = 1, $per_page = 10) {
        $offset = ($page - 1) * $per_page;
        $query = "SELECT a.*, u.name as author, t.name as theme 
                  FROM " . $this->table . " a
                  LEFT JOIN users u ON a.user_id = u.id
                  LEFT JOIN themes t ON a.theme_id = t.id
                  WHERE a.status = 'published'
                  ORDER BY a.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT a.*, u.name as author, t.name as theme 
              FROM " . $this->table . " a
              LEFT JOIN users u ON a.user_id = u.id
              LEFT JOIN themes t ON a.theme_id = t.id
              WHERE a.id = :id
              LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            return $row;
        }

        return null;
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  SET user_id=:user_id, theme_id=:theme_id, title=:title, content=:content, status=:status";
        
        $stmt = $this->conn->prepare($query);

        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->theme_id = htmlspecialchars(strip_tags($this->theme_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->status = htmlspecialchars(strip_tags($this->status));

        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":theme_id", $this->theme_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":status", $this->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . "
                  SET theme_id=:theme_id, title=:title, content=:content, status=:status
                  WHERE id=:id";
        
        $stmt = $this->conn->prepare($query);

        $this->theme_id = htmlspecialchars(strip_tags($this->theme_id));
        $this->title = htmlspecialchars(strip_tags($this->title));
        $this->content = htmlspecialchars(strip_tags($this->content));
        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(":theme_id", $this->theme_id);
        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }
}

