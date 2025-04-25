<?php
session_start();
include(__DIR__ . "/../config/db.php");

if ($_SESSION['user_role'] !== 'user') {
    die("Unauthorized");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $event_id = $_POST['event_id'];
    $user_id = $_SESSION['user_id'];

    // Generate unique ticket code
    $ticket_code = uniqid("TICKET_");

    // Fetch event price
    $event_query = $conn->prepare("SELECT price FROM events WHERE id = ?");
    $event_query->bind_param("i", $event_id);
    $event_query->execute();
    $event_result = $event_query->get_result()->fetch_assoc();
    $price = $event_result['price'];

    $payment_status = ($price > 0) ? 'pending' : 'free';

    // Insert ticket
    $stmt = $conn->prepare("INSERT INTO event_tickets (event_id, user_id, ticket_code, payment_status) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $event_id, $user_id, $ticket_code, $payment_status);

    if ($stmt->execute()) {
        header("Location: ../views/user/events.php?joined=success");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
