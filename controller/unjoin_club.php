<?php
session_start();
include(__DIR__ . "/../config/db.php");

if ($_SESSION['user_role'] !== 'user') {
    header("Location: ../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$club_id = $_POST['club_id'];

$sql = "DELETE FROM club_members WHERE user_id = '$user_id' AND club_id = '$club_id'";
if ($conn->query($sql)) {
    $_SESSION['message'] = "Left club successfully!";
} else {
    $_SESSION['message'] = "Error: " . $conn->error;
}

// Redirect back to the page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
