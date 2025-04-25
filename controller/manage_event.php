<?php
session_start();
include(__DIR__ . "/../config/db.php");

if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'coordinator')) {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Update event
if (isset($_POST['update_event'])) {
    $event_id = $_POST['event_id'];
    $title = $_POST['title'];
    $description = $_POST['description'];
    $event_date = $_POST['event_date'];

    $stmt = $conn->prepare("UPDATE events SET title=?, description=?, event_date=? WHERE id=?");
    $stmt->bind_param("sssi", $title, $description, $event_date, $event_id);

    if ($stmt->execute()) {
        header("Location: ../views/admin/manage_events.php?success=Event updated successfully");
    } else {
        echo "Error: " . $stmt->error;
    }
}

// Delete event
if (isset($_POST['delete_event'])) {
    $event_id = $_POST['event_id'];

    $stmt = $conn->prepare("DELETE FROM events WHERE id=?");
    $stmt->bind_param("i", $event_id);

    if ($stmt->execute()) {
        header("Location: ../views/admin/manage_events.php?success=Event deleted successfully");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
