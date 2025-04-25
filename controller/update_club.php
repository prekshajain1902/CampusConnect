<?php
session_start();
include(__DIR__ . "/../config/db.php");

// Ensure only the admin can access this page
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../views/auth/login.php");
    exit();
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_id = $_POST['club_id'];
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Check if a new image is uploaded
    if (!empty($_FILES['club_image']['name'])) {
        $target_dir = "../uploads/clubs/";
        $file_name = time() . "_" . basename($_FILES["club_image"]["name"]);
        $target_file = $target_dir . $file_name;

        // Move uploaded file to the target directory
        if (move_uploaded_file($_FILES["club_image"]["tmp_name"], $target_file)) {
            // Update club with new image
            $stmt = $conn->prepare("UPDATE clubs SET name=?, description=?, image_path=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $description, $target_file, $club_id);
        } else {
            die("Error uploading file.");
        }
    } else {
        // Update club without changing the image
        $stmt = $conn->prepare("UPDATE clubs SET name=?, description=? WHERE id=?");
        $stmt->bind_param("ssi", $name, $description, $club_id);
    }

    if ($stmt->execute()) {
        header("Location: ../views/admin/manage_clubs.php?success=Club updated successfully");
    } else {
        die("Error updating club.");
    }
}
?>
