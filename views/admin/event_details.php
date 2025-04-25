<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit();
}
$event_id = intval($_GET['id']);

$sql = "SELECT events.*, clubs.name AS club_name, users.name AS created_by 
        FROM events 
        JOIN clubs ON events.club_id = clubs.id 
        JOIN users ON events.created_by = users.id 
        WHERE events.id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Query preparation failed: " . $conn->error); // Debugging line
}

$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Event not found.");
}
$event = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f4f4f9;
        }
        .container {
            max-width: 800px;
        }
        .event-image {
            width: 100%;
            max-height: 300px;
            object-fit: cover;
            border-radius: 10px;
        }
        .back-btn {
            margin-bottom: 20px;
        }
        .price-badge {
            padding: 8px 12px;
            border-radius: 5px;
            font-size: 16px;
            font-weight: bold;
        }
        .price-free {
            background-color: #20c997;
            color: white;
        }
        .price-paid {
            background-color: #dc3545;
            color: white;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <a href="manage_events" class="btn btn-secondary back-btn"><i class="bi bi-arrow-left"></i> Back to Events</a>
    <div class="card shadow">
        <div class="card-body">
            <h2 class="text-center"><?php echo htmlspecialchars($event['name']); ?></h2>

            <?php if (!empty($event['image_path'])) { ?>
                <img src="../../<?php echo htmlspecialchars($event['image_path']); ?>" class="event-image" alt="Event Image">
            <?php } else { ?>
                <p class="text-muted text-center">No Image Available</p>
            <?php } ?>

            <ul class="list-group list-group-flush mt-3">
                <li class="list-group-item"><strong>Club:</strong> <?php echo htmlspecialchars($event['club_name']); ?></li>
                <li class="list-group-item"><strong>Date & Time:</strong> <?php echo date("d M Y, h:i A", strtotime($event['event_date'])); ?></li>
                <li class="list-group-item"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></li>
                <li class="list-group-item"><strong>Description:</strong> <?php echo nl2br(htmlspecialchars($event['description'])); ?></li>
                <li class="list-group-item"><strong>Created By:</strong> <?php echo htmlspecialchars($event['created_by']); ?></li>
                <li class="list-group-item">
                    <strong>Price:</strong> 
                    <?php if ($event['price'] == 0) { ?>
                        <span class="price-badge price-free">Free</span>
                    <?php } else { ?>
                        <span class="price-badge price-paid">₹<?php echo number_format($event['price'], 2); ?></span>
                    <?php } ?>
                </li>
            </ul>
        </div>
    </div>
</div>

</body>
</html>
