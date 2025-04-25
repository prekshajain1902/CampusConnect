<?php
session_start();
include(__DIR__ . "/../config/db.php");

// Only admin can perform this
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'];
    $current_role = $_POST['current_role'];

    // Determine new role
    $new_role = $current_role === 'user' ? 'coordinator' : 'user';

    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id = ?");
    $stmt->bind_param("si", $new_role, $user_id);

    if ($stmt->execute()) {
        header("Location: ../views/admin/manage_users.php?status=success");
    } else {
        header("Location: ../views/admin/manage_users.php?status=error");
    }
} else {
    header("Location: ../views/admin/manage_users.php");
    exit();
}
?>
