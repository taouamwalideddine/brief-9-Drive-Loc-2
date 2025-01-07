<?php
session_start(); 
require_once "Database.php";

class Reservation {
    private $db;

    public function __construct($dbConnection) {
        $this->db = $dbConnection;
    }

    public function isVehicleReserved($vehicleId, $startDate, $endDate) {
        $query = "
            SELECT 1 
            FROM reservations 
            WHERE vehicleId = :vehicleId 
              AND status = 'reserved'
              AND (
                (startDate <= :endDate AND endDate >= :startDate)
              )
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':vehicleId' => $vehicleId,
            ':startDate' => $startDate,
            ':endDate' => $endDate,
        ]);
        return $stmt->fetch() !== false;
    }

    public function createReservation($userId, $vehicleId, $startDate, $endDate) {
        if ($this->isVehicleReserved($vehicleId, $startDate, $endDate)) {
            throw new Exception("This vehicle is already reserved for the selected dates.");
        }

        $query = "
            INSERT INTO reservations (userId, vehicleId, startDate, endDate, status)
            VALUES (:userId, :vehicleId, :startDate, :endDate, 'reserved')
        ";
        $stmt = $this->db->prepare($query);
        $stmt->execute([
            ':userId' => $userId,
            ':vehicleId' => $vehicleId,
            ':startDate' => $startDate,
            ':endDate' => $endDate,
        ]);
        return true;
    }
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Ensure vehicle ID is passed via GET
if (!isset($_GET['vehicle_id'])) {
    die("Invalid access - vehicle ID is missing.");
}

// Initialize database and reservation class
$database = new Database();
$db = $database->getConnection();
$reservation = new Reservation($db);

// Redirect to booking.php with vehicle details
$vehicleId = $_GET['vehicle_id'];
header("Location: booking.php?vehicle_id=" . urlencode($vehicleId));
exit();
?>
