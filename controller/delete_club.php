<?php
session_start();
include(__DIR__ . "/../config/db.php");

if ($_SESSION['user_role'] !== 'admin') {
    die("Unauthorized Access");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_id = $_POST['club_id'];

    // Step 1: Get image filename from database
    $image_query = $conn->prepare("SELECT image_path FROM clubs WHERE id = ?");
    $image_query->bind_param("i", $club_id);
    $image_query->execute();
    $image_result = $image_query->get_result();
    $image_data = $image_result->fetch_assoc();

    if ($image_data) {
        $image_path = __DIR__ . "/../uploads/clubs/" . $image_data['image_path'];

        // Step 2: Delete the image file if it exists
        if (file_exists($image_path)) {
            unlink($image_path); // delete the file
        }
    }

    // Step 3: Now delete the club from the database
    $stmt = $conn->prepare("DELETE FROM clubs WHERE id = ?");
    $stmt->bind_param("i", $club_id);

    if ($stmt->execute()) {
        header("Location: ../views/admin/dashboard.php?success=Club Deleted");
    } else {
        echo "Error: " . $stmt->error;
    }
}
?>
