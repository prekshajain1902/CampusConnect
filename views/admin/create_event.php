<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Ensure only admin or coordinator can access this page
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'coordinator')) {
    header("Location: ../../views/auth/login.php");
    exit();
}

$error_message = "";
$success_message = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $club_id = $_POST['club_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);
    $price = !empty($_POST['price']) ? (float)$_POST['price'] : 0.0;
    $created_by = $_SESSION['user_id'];

    // IMAGE UPLOAD HANDLING
    $image_path = "";
    if (!empty($_FILES['image']['name'])) {
        $upload_dir = "../../uploads/events/";  

// Ensure directory exists
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// Clean file name (remove spaces)
$file_name = time() . "_" . str_replace(' ', '_', basename($_FILES["image"]["name"])); 
$target_path = $upload_dir . $file_name;

// Debug: Print upload path
// echo "Target Path: " . $target_path . "<br>";

if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_path)) {
    $image_path = "uploads/events/" . $file_name; // Save only relative path

    // Debug: Print stored path
    // echo "Image Saved as: " . $image_path . "<br>";
} else {
    die("⚠️ File upload failed. Error Code: " . $_FILES["image"]["error"]);
}

    }

    // Validate inputs
    if (empty($name) || empty($description) || empty($event_date) || empty($location)) {
        $error_message = "⚠️ All fields are required!";
    } else {
        // Insert event into database
        $stmt = $conn->prepare("INSERT INTO events (club_id, name, description, event_date, location, price, created_by, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$image_path) {
    $image_path = ""; // Avoid null errors
}
        $stmt->bind_param("issssdis", $club_id, $name, $description, $event_date, $location, $price, $created_by, $image_path);

        if ($stmt->execute()) {
            $event_id = $conn->insert_id; // Get last inserted event ID

            // Notify all users who joined this club
            $users = $conn->prepare("SELECT user_id FROM club_members WHERE club_id = ?");
            $users->bind_param("i", $club_id);
            $users->execute();
            $users_result = $users->get_result();

            if ($users_result->num_rows > 0) {
                $notification_query = "INSERT INTO notifications (user_id, event_id, message) VALUES ";
                $notification_values = [];

                while ($user = $users_result->fetch_assoc()) {
                    $message = "New event '$name' has been added in your club!";
                    $notification_values[] = "({$user['user_id']}, $event_id, '$message')";
                }

                if (!empty($notification_values)) {
                    $notification_query .= implode(", ", $notification_values);
                    $conn->query($notification_query);
                }
            }

            $success_message = "✅ Event created successfully!";
        } else {
            $error_message = "⚠️ Failed to create event. Try again.";
        }
    }
}

// Fetch clubs for dropdown
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

if ($user_role === 'admin') {
    $club_query = "SELECT * FROM clubs"; // Admin sees all clubs
} else {
    // Coordinator only sees clubs they manage
    $club_query = "SELECT * FROM clubs WHERE coordinator_id = ?";
}

$club_stmt = $conn->prepare($club_query);

if ($user_role === 'coordinator') {
    $club_stmt->bind_param("i", $user_id);
}

$club_stmt->execute();
$clubs = $club_stmt->get_result();
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
            background-color: #f4f4f4;
        }
        .card {
            max-width: 800px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            background-color: #343a40;
            padding-top: 20px;
        }
        .sidebar a {
            padding: 15px;
            text-decoration: none;
            font-size: 18px;
            color: #ffffff;
            display: block;
            transition: 0.3s;
        }
        .sidebar a:hover {
            background-color: #007bff;
        }
        .club-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        .btn-sm {
            font-size: 14px;
            padding: 5px 10px;
        }
        .content {
            margin-left: 260px;
            padding: 20px;
        }
        .btn-custom {
            background: #007bff;
            color: white;
        }
        .btn-custom:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
<div class="sidebar">
    <h3 class="text-center text-white">Admin Panel</h3>
    <a href="dashboard"><i class="fas fa-home"></i> Dashboard</a>
    <a href="manage_clubs"><i class="fas fa-users"></i> Manage Clubs</a>
    <a href="manage_users"><i class="fas fa-user"></i> Manage Users</a>
    <a href="create_event"><i class="fas fa-calendar-plus"></i> Create Event</a>
    <a href="manage_events"><i class="fas fa-calendar"></i> Manage Events</a>
    <a href="../../controller/logout" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>
<!-- Main Container -->
<div class="content">
<div class="container mt-5">
    <h1 class="mb-4">Create Event</h1>
     <div class="card p-3 shadow-sm mb-4">
             <!-- Success Message -->
            <?php if (!empty($success_message)) : ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <!-- Error Message -->
            <?php if (!empty($error_message)) : ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                                <label class="form-label">Event Name</label>
                                <input type="text" name="name" class="form-control" placeholder="Enter event name" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Club</label>
                                <select name="club_id" class="form-control" required>
                                    <option value="">Select Club</option>
                                    <?php while ($club = $clubs->fetch_assoc()) : ?>
                                        <option value="<?php echo $club['id']; ?>"><?php echo $club['name']; ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" placeholder="Describe the event" required></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Event Date & Time</label>
                                <input type="datetime-local" name="event_date" class="form-control" required>
                            </div>  

                            <div class="mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="Enter location" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ticket Price (0 for free)</label>
                                <input type="number" name="price" class="form-control" placeholder="Enter ticket price" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload Event Image</label>
                                <input type="file" name="image" class="form-control">
                            </div>

                             <button type="submit" class="btn btn-custom w-100">Create Event</button>
            </form>
        </div>
</div>
</div>
</body>
</html>
