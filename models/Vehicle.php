<?php
class Vehicle {
    private $conn;
    private $table = 'listevehicules'; 

    public function __construct($db) {
        $this->conn = $db;
    }
    public function getVehicleById($vehicleId) {
        $query = "SELECT * FROM vehicles WHERE id = :vehicleId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':vehicleId', $vehicleId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getVehicles($search = "", $category = "") {
        $query = "SELECT 
                    v.*, 
                    (SELECT AVG(rating) FROM reviews WHERE vehicleId = v.vehicleId AND isDeleted = 0) AS avg_rating,
                    (SELECT COUNT(*) FROM reviews WHERE vehicleId = v.vehicleId AND isDeleted = 0) AS review_count
                  FROM " . $this->table . " v WHERE 1";

        if (!empty($search)) {
            $query .= " AND (v.model LIKE :search OR v.brand LIKE :search)";
        }

        if (!empty($category)) {
            $query .= " AND v.category = :category";
        }

        try {
            $stmt = $this->conn->prepare($query);

            if (!empty($search)) {
                $stmt->bindValue(':search', "%$search%");
            }
            if (!empty($category)) {
                $stmt->bindValue(':category', $category);
            }

            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Vehicles Error: " . $e->getMessage());
            return [];
        }
    }

    public function getCategories() {
        $query = "SELECT DISTINCT category FROM " . $this->table;
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Categories Error: " . $e->getMessage());
            return [];
        }
    }

    public function readOne($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE vehicleId = ?";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Read Single Vehicle Error: " . $e->getMessage());
            return false;
        }
    }

    public function getReviews($vehicleId) {
        $query = "SELECT r.*, u.name as reviewer_name 
                  FROM reviews r 
                  JOIN users u ON r.userId = u.id 
                  WHERE r.vehicleId = ? AND r.isDeleted = 0";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $vehicleId);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Reviews Error: " . $e->getMessage());
            return [];
        }
    }

    public function getAverageRating($vehicleId) {
        $query = "SELECT AVG(rating) as average_rating 
                  FROM reviews 
                  WHERE vehicleId = ? AND isDeleted = 0";
        try {
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $vehicleId);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row['average_rating'];
        } catch (PDOException $e) {
            error_log("Get Average Rating Error: " . $e->getMessage());
            return 0;
        }
    }   
}

?>
