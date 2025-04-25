<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Ensure only the coordinator can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinator') {
    header("Location: ../../views/auth/login.php");
    exit();
}

$coordinator_id = $_SESSION['user_id'];

// Fetch clubs assigned to this coordinator
$clubs_query = $conn->prepare("SELECT id, name, description, image_path FROM clubs WHERE coordinator_id = ?");
$clubs_query->bind_param("i", $coordinator_id);
$clubs_query->execute();
$clubs_result = $clubs_query->get_result();

// Fetch events assigned to the coordinator's clubs & count attended students
$events_query = $conn->prepare("
    SELECT e.id, e.name AS event_name, e.event_date, e.location, e.price, e.image_path, c.name AS club_name,
           (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id) AS total_registered, 
           (SELECT COUNT(*) FROM event_registrations WHERE event_id = e.id AND is_scanned = 1) AS attended_count
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    WHERE c.coordinator_id = ?");
$events_query->bind_param("i", $coordinator_id);
$events_query->execute();
$events_result = $events_query->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f6f9;
        }

        .navbar {
            background-color: #007bff;
            padding: 15px 0;
        }

        .navbar-brand {
            font-weight: bold;
            color: white !important;
            font-size: 1.8rem;
        }

        .navbar-nav .nav-link {
            color: white !important;
            font-size: 18px;
        }

        .container {
            max-width: 1200px;
        }

        .btn-custom {
            background-color: #007bff;
            color: white;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .btn-custom:hover {
            background-color: #0056b3;
        }

        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .card-title {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .card-text {
            font-size: 0.95rem;
            color: #6c757d;
        }

        .card-img-top {
            height: 200px;
            object-fit: cover;
        }

        .text-danger {
            font-weight: bold;
        }

        .section-title {
            font-size: 1.8rem;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .row {
            margin-bottom: 30px;
        }

        .d-flex .btn {
            margin: 0 10px 10px 0;
        }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">CampusConnect</a>
       <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../../controller/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Main Container -->
<div class="container mt-4">
    <h2 class="text-center section-title">Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?> (Coordinator)</h2>
    
    <!-- Dashboard Options -->
<div class="row mt-4 justify-content-center">
    <div class="col-md-4 mb-2">
        <a href="create_event.php" class="btn btn-custom w-100">Create Event</a>
    </div>
    <div class="col-md-4 mb-2">
        <a href="manage_events.php" class="btn btn-secondary w-100">Manage Events</a>
    </div>
</div>


    <!-- Clubs Section -->
    <section class="clubs mt-5">
        <h3 class="text-center mb-4 section-title">My Clubs</h3>
        <div class="row">
            <?php if ($clubs_result->num_rows > 0) { ?>
                <?php while ($club = $clubs_result->fetch_assoc()) { ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow">
                            <img src="<?php echo '/Campus_connect/uploads/clubs/' . $club['image_path']; ?>" class="card-img-top" alt="Event Image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($club['name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($club['description']); ?></p>
                                <a href="view_joined_users.php?club_id=<?php echo $club['id']; ?>" class="btn btn-info w-100">View Members</a>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p class="alert alert-warning text-center">No clubs assigned to you.</p>
            <?php } ?>
        </div>
    </section>

    <!-- Events Section -->
    <section class="events mt-5">
        <h3 class="text-center mb-4 section-title">My Club Events</h3>
        <div class="row">
            <?php if ($events_result->num_rows > 0) { ?>
                <?php while ($event = $events_result->fetch_assoc()) { ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow">
                           <img src="<?php echo '/Campus_connect/' . $event['image_path']; ?>" class="card-img-top" alt="Event Image">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($event['event_name']); ?></h5>
                                <p class="card-text"><strong>Club:</strong> <?php echo htmlspecialchars($event['club_name']); ?></p>
                                <p class="card-text"><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                <p class="card-text"><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                <p class="card-text">
                                    <strong>Price:</strong> 
                                    <?php echo ($event['price'] == 0) ? '<span class="badge bg-success">Free</span>' : '$' . htmlspecialchars($event['price']); ?>
                                </p>
                                <p class="card-text"><strong>Registered:</strong> <?php echo $event['total_registered']; ?></p>
                                <p class="card-text"><strong>Attended:</strong> <?php echo $event['attended_count']; ?></p>
                                <div class="d-flex">
                                    <a href="registered_users.php?event_id=<?php echo $event['id']; ?>" class="btn btn-primary w-50 me-2">View Users</a>
                                    <a href="scan_ticket.php?event_id=<?php echo $event['id']; ?>" class="btn btn-success w-50">Scan Tickets</a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <p class="alert alert-info text-center">No events available.</p>
            <?php } ?>
        </div>
    </section>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
