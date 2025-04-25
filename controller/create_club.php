<?php
include(__DIR__ . '/../config/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];

    // Ensure the upload directory exists (relative to project root)
    $relative_upload_dir = '../uploads/clubs/';
    $absolute_upload_dir = __DIR__ . '/../uploads/clubs/';
    
    if (!is_dir($absolute_upload_dir)) {
        mkdir($absolute_upload_dir, 0777, true);
    }

    // Check if file was uploaded without errors
    if (isset($_FILES['club_image']) && $_FILES['club_image']['error'] === UPLOAD_ERR_OK) {
        $original_filename = basename($_FILES["club_image"]["name"]);
        $file_name = time() . "_" . $original_filename;
        $target_path = $absolute_upload_dir . $file_name;

        // Move the uploaded file to the upload directory
        if (move_uploaded_file($_FILES["club_image"]["tmp_name"], $target_path)) {
            // Save only the filename (or relative path if needed) in DB
            $image_path = $file_name; // or $relative_upload_dir . $file_name if needed

            // Insert club info into the database
            $stmt = $conn->prepare("INSERT INTO clubs (name, description, image_path) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $description, $image_path);
            $stmt->execute();

            header("Location: ../views/admin/dashboard.php");
            exit();
        } else {
            echo "❌ Failed to move uploaded file.";
        }
    } else {
        echo "❌ No file uploaded or upload error: " . $_FILES['club_image']['error'];
    }
}
?>
