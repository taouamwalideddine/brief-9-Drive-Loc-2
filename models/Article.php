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

    public function read($page = 1, $per_page = 10, $theme_id = null) {
        $offset = ($page - 1) * $per_page;
        $query = "SELECT a.*, u.name as author, t.name as theme 
                  FROM " . $this->table . " a
                  LEFT JOIN Users u ON a.user_id = u.id
                  LEFT JOIN themes t ON a.theme_id = t.id
                  WHERE a.status = 'published'";
        
        if ($theme_id) {
            $query .= " AND a.theme_id = :theme_id";
        }
        
        $query .= " ORDER BY a.created_at DESC
                    LIMIT :limit OFFSET :offset";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        
        if ($theme_id) {
            $stmt->bindParam(':theme_id', $theme_id, PDO::PARAM_INT);
        }
        
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT a.*, u.name as author, t.name as theme 
                  FROM " . $this->table . " a
                  LEFT JOIN Users u ON a.user_id = u.id
                  LEFT JOIN themes t ON a.theme_id = t.id
                  WHERE a.id = :id
                  LIMIT 0,1";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $this->id);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
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

        try {
            if($stmt->execute()) {
                return $this->conn->lastInsertId();
            }
        } catch(PDOException $e) {
            error_log("Error creating article: " . $e->getMessage());
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

    public function delete() {
        $query = "DELETE FROM comments WHERE article_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $query = "DELETE FROM favorites WHERE article_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $query = "DELETE FROM article_tags WHERE article_id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();

        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getArticleTags($article_id) {
        $query = "SELECT t.id, t.name 
                  FROM tags t
                  INNER JOIN article_tags at ON t.id = at.tag_id
                  WHERE at.article_id = :article_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function addTag($article_id, $tag_id) {
        $query = "INSERT INTO article_tags (article_id, tag_id) VALUES (:article_id, :tag_id)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->bindParam(':tag_id', $tag_id);
        return $stmt->execute();
    }

    public function removeTag($article_id, $tag_id) {
        $query = "DELETE FROM article_tags WHERE article_id = :article_id AND tag_id = :tag_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $article_id);
        $stmt->bindParam(':tag_id', $tag_id);
        return $stmt->execute();
    }

    public function getFavoritesCount() {
        $query = "SELECT COUNT(*) as count FROM favorites WHERE article_id = :article_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':article_id', $this->id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['count'];
    }
}

