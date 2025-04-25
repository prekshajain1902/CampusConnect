<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Check if the user is a coordinator
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'coordinator') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Get event ID
if (!isset($_GET['id'])) {
    die("Event ID is missing.");
}
$event_id = $_GET['id'];

// Fetch event details
$stmt = $conn->prepare("SELECT * FROM events WHERE id = ?");
$stmt->bind_param("i", $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

// Fetch all clubs for dropdown selection
$clubs = $conn->query("SELECT id, name FROM clubs");

// Update event
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $price = $_POST['price'];
    $club_id = $_POST['club_id'];

    // Handle image upload
    if (!empty($_FILES['event_image']['name'])) {
        $target_dir = "../../uploads/events/";
        $file_name = time() . "_" . basename($_FILES["event_image"]["name"]);
        $target_file = $target_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = array("jpg", "jpeg", "png", "gif");

        if (!in_array($imageFileType, $allowed_types)) {
            die("Invalid file type. Only JPG, JPEG, PNG, and GIF are allowed.");
        }

        if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $target_file)) {
            $image_path = "uploads/events/" . $file_name; // Save relative path
        } else {
            die("Error uploading file.");
        }
    } else {
        $image_path = $event['image_path']; // Keep existing image if no new upload
    }

    $update_sql = "UPDATE events SET name=?, event_date=?, location=?, price=?, club_id=?, image_path=? WHERE id=?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssissi", $name, $event_date, $location, $price, $club_id, $image_path, $event_id);

    if ($stmt->execute()) {
        header("Location: manage_events.php?success=Event updated successfully");
        exit();
    } else {
        echo "Error updating event.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .card {
            max-width: 600px;
            margin: auto;
            border-radius: 10px;
        }
        .event-image {
            width: 100%;
            max-height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="card p-4 shadow">
            <h2 class="text-center text-primary mb-4">Edit Event</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold">Event Name</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($event['name']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Event Date & Time</label>
                    <input type="datetime-local" name="event_date" class="form-control" 
                        value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Location</label>
                    <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($event['location']); ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Price (0 for Free)</label>
                    <input type="number" name="price" class="form-control" value="<?php echo $event['price']; ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Select Club</label>
                    <select name="club_id" class="form-control" required>
                        <?php while ($club = $clubs->fetch_assoc()) { ?>
                            <option value="<?php echo $club['id']; ?>" <?php echo ($club['id'] == $event['club_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($club['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Event Image</label>
                    <?php if (!empty($event['image_path'])) { ?>
                        <img src="../../<?php echo htmlspecialchars($event['image_path']); ?>" class="event-image" alt="Event Image">
                    <?php } ?>
                    <input type="file" name="event_image" class="form-control">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-success">Update Event</button>
                    <a href="manage_events.php" class="btn btn-secondary">Back to Events</a>
                    <a href="dashboard.php" class="btn btn-outline-primary">Go to Dashboard</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>
