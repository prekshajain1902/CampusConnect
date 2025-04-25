<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Ensure database connection is successful
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Ensure only authenticated coordinators can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinator') {
    header("Location: ../../views/auth/login.php");
    exit();
}

if (!isset($_GET['event_id']) || !ctype_digit($_GET['event_id'])) {
    die("Invalid event.");
}

$event_id = $_GET['event_id'];
$coordinator_id = $_SESSION['user_id'];

// Fetch event name and club name
$event_query = "
    SELECT e.name AS event_name, c.name AS club_name 
    FROM events e
    JOIN clubs c ON e.club_id = c.id
    WHERE e.id = ?";
    
$event_stmt = $conn->prepare($event_query);
$event_stmt->bind_param("i", $event_id);
$event_stmt->execute();
$event_result = $event_stmt->get_result();
$event = $event_result->fetch_assoc();

if (!$event) {
    die("Event not found.");
}

$event_name = htmlspecialchars($event['event_name']);
$club_name = htmlspecialchars($event['club_name']);

// Fetch registered users
$query = "
    SELECT u.id, u.name, u.email, u.student_stream, u.semester, u.enrollment_no
    FROM event_registrations er
    JOIN users u ON er.user_id = u.id
    WHERE er.event_id = ?";


$stmt = $conn->prepare($query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();
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
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #007bff;
            padding: 10px;
        }
        .navbar-brand {
            font-weight: bold;
            color: white !important;
            font-size: 1.5rem;
        }
        .navbar-nav .nav-link {
            color: white !important;
        }
        .heading {
            background: linear-gradient(to right, #007bff, #6f42c1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .table-responsive {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
            transition: 0.3s;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">CampusConnect</a>
       <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger" href="../../controller/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<!-- Page Content -->
<div class="container mt-5">
    <h2 class="heading">Registered Users</h2>

    <div class="text-center mb-4">
        <h4><i class="bi bi-calendar-event"></i> Event: <span class="text-primary"><?php echo $event_name; ?></span></h4>
        <h5><i class="bi bi-people"></i> Club: <span class="text-secondary"><?php echo $club_name; ?></span></h5>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover">
           <thead>
    <tr>
        <th>User ID</th>
        <th>Name</th>
        <th>Email</th>
        <th>Stream</th>
        <th>Semester</th>
        <th>Enrollment No</th>
    </tr>
</thead>

           <tbody>
    <?php while ($user = $result->fetch_assoc()) { ?>
    <tr>
        <td><?= htmlspecialchars($user['id']); ?></td>
        <td><?= htmlspecialchars($user['name']); ?></td>
        <td><?= htmlspecialchars($user['email']); ?></td>
        <td><?= htmlspecialchars($user['student_stream']); ?></td>
        <td><?= htmlspecialchars($user['semester']); ?></td>
        <td><?= htmlspecialchars($user['enrollment_no']); ?></td>
    </tr>
    <?php } ?>
</tbody>

        </table>
    </div>
</div>

</body>
</html>
