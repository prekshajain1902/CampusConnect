<?php
session_start();
include(__DIR__ . "/../config/db.php");
require(__DIR__ . "/../libs/phpqrcode/qrlib.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$event_id = $_POST['event_id'];

// Check if user is already registered
$check_query = $conn->prepare("SELECT ticket_path, ticket_code FROM event_registrations WHERE user_id = ? AND event_id = ?");
$check_query->bind_param("ii", $user_id, $event_id);
$check_query->execute();
$result = $check_query->get_result();
$existing_ticket = $result->fetch_assoc();

if ($existing_ticket && !empty($existing_ticket['ticket_path'])) {
    echo "You are already registered for this event.";
    exit();
}

// Generate a unique ticket_code (Format: TKT_UserID_EventID_RandomString)
$unique_hash = substr(md5(uniqid(rand(), true)), 0, 8);
$ticket_code = "TKT_" . $user_id . "_" . $event_id . "_" . $unique_hash;

// Register user for the event
$insert_query = $conn->prepare("INSERT INTO event_registrations (user_id, event_id, ticket_code) VALUES (?, ?, ?)");
$insert_query->bind_param("iis", $user_id, $event_id, $ticket_code);
$insert_query->execute();

// Ensure the QR code folder exists
$ticket_folder = __DIR__ . "/../uploads/qrcodes/";
if (!file_exists($ticket_folder)) {
    mkdir($ticket_folder, 0777, true);
}

// Generate QR Code using `ticket_code`
$qr_code_filename = "ticket_" . $user_id . "_" . $event_id . ".jpg"; 
$qr_code_file = $ticket_folder . $qr_code_filename;
QRcode::png($ticket_code, $qr_code_file, QR_ECLEVEL_L, 5);

// Store ticket_path in the database
$ticket_path = "uploads/qrcodes/" . $qr_code_filename;
$update_query = $conn->prepare("UPDATE event_registrations SET ticket_code = ?, ticket_path = ? WHERE user_id = ? AND event_id = ?");
$update_query->bind_param("ssii", $ticket_code, $ticket_path, $user_id, $event_id);
$update_query->execute();


//$_SESSION['success'] = "Registration successful!";
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();

$insert_query->close();
$update_query->close();
$conn->close();

?>
