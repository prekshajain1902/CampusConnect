<?php
session_start();
include(__DIR__ . "/../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        session_regenerate_id(true); 

        // Redirect based on role
        if ($user['role'] == 'admin') {
            header("Location: ../views/admin/dashboard.php");
        } elseif ($user['role'] == 'coordinator') {
            header("Location: ../views/coordinator/dashboard.php");
        } else {
            header("Location: ../views/user/dashboard.php");
        }
    } else {
        echo "Invalid credentials!";
    }
}
?>
