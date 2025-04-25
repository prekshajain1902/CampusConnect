<?php
session_start();
include(__DIR__ . "/../config/db.php");

header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["status" => "error", "message" => "Invalid request method"]);
    exit();
}

// Check if ticket_code is received
if (!isset($_POST['ticket_code']) || empty($_POST['ticket_code'])) {
    echo json_encode(["status" => "error", "message" => "No QR data received."]);
    exit();
}

// Get the scanned ticket code
$ticket_code = trim($_POST['ticket_code']);

// Debugging: Log the scanned ticket code
file_put_contents("debug_log.txt", "Scanned Ticket Code: " . $ticket_code . "\n", FILE_APPEND);

// Validate format
if (!preg_match('/^TKT_\d+_\d+_[a-z0-9]{8}$/i', $ticket_code)) {
    echo json_encode(["status" => "error", "message" => "Invalid Ticket Format! Code: " . htmlspecialchars($ticket_code)]);
    exit();
}

// Check if the ticket exists
$query = $conn->prepare("SELECT id, is_scanned FROM event_registrations WHERE BINARY ticket_code = ?");
$query->bind_param("s", $ticket_code);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Invalid Ticket! Code: " . htmlspecialchars($ticket_code)]);
    exit();
}

$ticket = $result->fetch_assoc();

// Prevent duplicate scans
if ($ticket['is_scanned'] == 1) {
    echo json_encode(["status" => "error", "message" => "This ticket has already been scanned!"]);
    exit();
}

// Mark ticket as scanned
$update = $conn->prepare("UPDATE event_registrations SET is_scanned = 1 WHERE id = ?");
$update->bind_param("i", $ticket['id']);

if ($update->execute()) {
    echo json_encode(["status" => "success", "message" => "Ticket validated successfully!"]);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to update ticket."]);
}

$query->close();
$update->close();
$conn->close();
?>
