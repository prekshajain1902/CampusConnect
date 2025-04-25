<?php
session_start();
include(__DIR__ . "/../../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Fetch events for clubs the user has joined
$user_id = $_SESSION['user_id'];
$sql = "SELECT e.id, e.name, e.event_date, e.location, e.price, e.image_path, 
               c.name AS club_name, er.ticket_path
        FROM events e
        JOIN clubs c ON e.club_id = c.id
        JOIN club_members cm ON c.id = cm.club_id
        LEFT JOIN event_registrations er ON er.event_id = e.id AND er.user_id = ?
        WHERE cm.user_id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        body {
            background-color: #f4f6f9;
        }
        .container {
            max-width: 1100px;
            margin-top: 50px;
        }
        .event-card {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out;
        }
        .event-card:hover {
            transform: scale(1.03);
        }
        .event-img {
            height: 200px;
            object-fit: cover;
        }
        .event-info {
            padding: 20px;
        }
        .already-registered {
            color: #28a745;
            font-weight: bold;
        }
        .btn-download {
            background-color: #17a2b8;
            color: white;
        }
        .btn-download:hover {
            background-color: #138496;
        }
        .btn-back {
            background-color: #6c757d;
            color: white;
        }
        .btn-back:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="container">
    <h1 class="text-primary"><i class="bi bi-calendar-event"></i> Available Events</h1>
    <hr>
    
    <a href="dashboard.php" class="btn btn-back mb-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>

    <div class="row">
        <?php while ($event = $result->fetch_assoc()) { 
            $event_id = $event['id'];
            $ticket_path = $event['ticket_path'];
            $is_registered = !empty($ticket_path);
        ?>
        <div class="col-md-4 mb-4">
            <div class="card event-card">
                <img src="<?php echo '../../' . $event['image_path']; ?>" class="card-img-top event-img" alt="<?php echo htmlspecialchars($event['name']); ?>">
                <div class="card-body event-info">
                    <h5 class="card-title"><?php echo htmlspecialchars($event['name']); ?></h5>
                    <p class="card-text"><strong>Club:</strong> <?php echo htmlspecialchars($event['club_name']); ?></p>
                    <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                    <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                    <p class="card-text">
                        <strong>Price:</strong> 
                        <?php echo ($event['price'] == 0) ? '<span class="text-success">Free</span>' : '$' . htmlspecialchars($event['price']); ?>
                    </p>
                    
                    <div class="mt-3">
                        <?php if ($is_registered) { ?>
                            <span class="already-registered"><i class="bi bi-check-circle"></i> Already Registered</span>
                            <form action="download_ticket.php" method="GET" class="mt-2">
                                <input type="hidden" name="file" value="<?php echo htmlspecialchars($ticket_path); ?>">
                                <button type="submit" class="btn btn-download btn-sm w-100"><i class="bi bi-file-earmark-arrow-down"></i> Download Ticket</button>
                            </form>
                        <?php } else { ?>
                            <?php if ($event['price'] == 0) { ?>
                                <form action="../../controller/register_event.php" method="POST">
                                    <input type="hidden" name="event_id" value="<?php echo $event_id; ?>">
                                    <button type="submit" class="btn btn-success btn-sm w-100"><i class="bi bi-check2-circle"></i> Register for Free</button>
                                </form>
                            <?php } else { ?>
                                <a href="payment.php?event_id=<?php echo $event_id; ?>" class="btn btn-primary btn-sm w-100"><i class="bi bi-credit-card"></i> Pay & Register</a>
                            <?php } ?>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

</body>
</html>
