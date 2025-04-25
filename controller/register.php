<?php
include(__DIR__ . "/../config/db.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $student_stream = $_POST['student_stream'];
    $semester = $_POST['semester'];
    $enrollment_no = $_POST['enrollment_no'];
    $role = 'user'; // Default role

    if ($password === $confirm_password) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Check if the user exists in the pre-registered users table
        $check_query = "SELECT * FROM enrolled_users WHERE email = ? AND enrollment_no = ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("ss", $email, $enrollment_no);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            echo "You are not authorized to register!";
        } else {
            // Check if the email is already registered in the users table
            $check_user = "SELECT * FROM users WHERE email = ?";
            $stmt = $conn->prepare($check_user);
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $user_result = $stmt->get_result();

            if ($user_result->num_rows > 0) {
                echo "Email is already registered!";
            } else {
                // Insert into users table
                $insert_query = "INSERT INTO users (name, email, password, student_stream, semester, enrollment_no, role) VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($insert_query);

                if (!$stmt) {
                    die("SQL Error: " . $conn->error);
                }

                $stmt->bind_param("ssssiss", $name, $email, $hashed_password, $student_stream, $semester, $enrollment_no, $role);

                if ($stmt->execute()) {
                    echo "Registration successful! <a href='../views/auth/login.php'>Login here</a>";
                } else {
                    echo "Error: " . $stmt->error;
                }
            }
        }
    } else {
        echo "Passwords do not match!";
    }
}
?>
