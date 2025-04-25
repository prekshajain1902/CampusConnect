<?php
if (isset($_GET['file'])) {
    $requested_file = $_GET['file']; // Example: uploads/qrcodes/ticket_4_2.jpg

    // Prevent directory traversal attacks
    $requested_file = basename($requested_file); // Ensures only filename is used

    // Define the correct file path (relative to the project root)
    $base_dir = realpath(__DIR__ . '/../../uploads/qrcodes/');
    $file_path = $base_dir . DIRECTORY_SEPARATOR . $requested_file;

    // Check if base directory is resolved correctly
    if ($base_dir === false) {
        die("Error: Base directory not found.");
    }

    // Ensure the file exists and is within the intended directory
    if (file_exists($file_path) && strpos(realpath($file_path), $base_dir) === 0) {
        // Set headers for download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . basename($file_path) . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file_path));
        readfile($file_path);
        exit;
    } else {
        die("Error: File does not exist at " . htmlspecialchars($file_path));
    }
} else {
    die("Error: No file specified.");
}
?>
