<?php
session_start();
include(__DIR__ . "/../config/db.php");
require(__DIR__ . "/../libs/phpqrcode/qrlib.php"); // Make sure this is correct

if (!isset($_GET['user_id']) || !isset($_GET['event_id'])) {
    die("Invalid request");
}

$user_id = intval($_GET['user_id']);
$event_id = intval($_GET['event_id']);

// Generate unique ticket code
$unique_hash = substr(md5(uniqid(rand(), true)), 0, 8); // Generate a unique 8-character hash
$ticket_code = "TKT_" . $user_id . "_" . $event_id . "_" . $unique_hash;

// Ensure the QR code folder exists
$ticket_folder = __DIR__ . "/../uploads/qrcodes/";
if (!file_exists($ticket_folder)) {
    mkdir($ticket_folder, 0777, true);
}

// Define QR Code file name
$qr_code_filename = "ticket_" . $user_id . "_" . $event_id . ".jpg"; 
$qr_code_file = $ticket_folder . $qr_code_filename;

// Generate QR code containing `ticket_code`
QRcode::png($ticket_code, $qr_code_file, QR_ECLEVEL_L, 5);

// Store `ticket_code` and `ticket_path` in the database
$ticket_path = "uploads/qrcodes/" . $qr_code_filename;
$update_query = $conn->prepare("UPDATE event_registrations SET ticket_code = ?, ticket_path = ? WHERE user_id = ? AND event_id = ?");
$update_query->bind_param("ssii", $ticket_code, $ticket_path, $user_id, $event_id);
$update_query->execute();

if ($update_query->affected_rows <= 0) {
    die("Error: Ticket details not updated.");
}

// Redirect user to the ticket page
header("Location: ../views/user/ticket.php");
exit();
?>
