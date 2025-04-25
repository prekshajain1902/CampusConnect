<?php
session_start();
include(__DIR__ . "/../config/db.php");

// Ensure only admin or coordinator can delete
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'coordinator')) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access."]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    $event_id = intval($_POST['event_id']);

    // Step 1: Get event image filename
    $image_query = $conn->prepare("SELECT image_path FROM events WHERE id = ?");
    $image_query->bind_param("i", $event_id);
    $image_query->execute();
    $image_result = $image_query->get_result();
    $event = $image_result->fetch_assoc();

    if (!$event) {
        echo json_encode(["status" => "error", "message" => "Event not found."]);
        exit();
    }

    $image_path = __DIR__ . "/../uploads/events/" . $event['image_path'];

    // Step 2: Delete the image file if it exists
    if (file_exists($image_path)) {
        unlink($image_path);
    }

    // Step 3: Delete related data
    $conn->query("DELETE FROM event_registrations WHERE event_id = $event_id");
    $conn->query("DELETE FROM tickets WHERE event_id = $event_id");

    // Step 4: Delete the event itself
    $delete_query = "DELETE FROM events WHERE id = ?";
    $stmt = $conn->prepare($delete_query);
    $stmt->bind_param("i", $event_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(["status" => "success", "message" => "Event deleted successfully."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Failed to delete event."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>
