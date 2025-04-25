<?php
include(__DIR__ . "/../config/db.php");
session_start();

if ($_SESSION['user_role'] !== 'admin') {
    echo json_encode(["status" => "error", "message" => "Unauthorized Access"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_id = $_POST['club_id'];
    $coordinator_id = $_POST['coordinator_id'];

    $stmt = $conn->prepare("UPDATE clubs SET coordinator_id = ? WHERE id = ?");
    $stmt->bind_param("ii", $coordinator_id, $club_id);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Coordinator assigned successfully!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error: " . $stmt->error]);
    }
}
?>
