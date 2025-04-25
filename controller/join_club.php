<?php
session_start();
include(__DIR__ . "/../config/db.php");

if ($_SESSION['user_role'] !== 'user') {
    header("Location: ../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$club_id = $_POST['club_id'];

// Check if user is already a member
$check_query = "SELECT * FROM club_members WHERE user_id = '$user_id' AND club_id = '$club_id'";
$check_result = $conn->query($check_query);

if ($check_result->num_rows == 0) {
    $sql = "INSERT INTO club_members (user_id, club_id) VALUES ('$user_id', '$club_id')";
    if ($conn->query($sql)) {
        $_SESSION['message'] = "Joined club successfully!";
    } else {
        $_SESSION['message'] = "Error: " . $conn->error;
    }
}

// Redirect back to the page
header("Location: " . $_SERVER['HTTP_REFERER']);
exit();
?>
