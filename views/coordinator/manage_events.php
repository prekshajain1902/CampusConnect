<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Ensure only the admin or coordinator can access this page
if (!isset($_SESSION['user_role']) || ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'coordinator')) {
    header("Location: ../../views/auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Check if the 'users' table has a 'username' column
$sql_check = "SHOW COLUMNS FROM users LIKE 'username'";
$result_check = $conn->query($sql_check);
$has_username = $result_check->num_rows > 0;

// Use 'email' if 'username' does not exist
$creator_column = $has_username ? 'users.username' : 'users.email';

if ($user_role === 'admin') {
    $sql = "SELECT events.*, clubs.name AS club_name, $creator_column AS created_by 
            FROM events 
            JOIN clubs ON events.club_id = clubs.id 
            JOIN users ON events.created_by = users.id";
    $stmt = $conn->prepare($sql);
} else {
    // Coordinator sees only their club's events
    $sql = "SELECT events.*, clubs.name AS club_name, $creator_column AS created_by 
            FROM events 
            JOIN clubs ON events.club_id = clubs.id 
            JOIN users ON events.created_by = users.id 
            WHERE clubs.coordinator_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
}

if (!$stmt) {
    die("Query Preparation Failed: " . $conn->error);
}

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
            background-color: #f4f4f9;
        }
        .navbar {
            background-color: #0d6efd;
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
        .manage-events-heading {
            background: linear-gradient(to right, #007bff, #6f42c1);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            font-size: 2rem;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }
        .container {
            max-width: 1200px;
        }
        .event-image {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .table thead {
            background-color: #0d6efd;
            color: white;
        }
        .btn-primary, .btn-success {
            background-color: #0d6efd;
            border: none;
        }
        .btn-primary:hover, .btn-success:hover {
            background-color: #084298;
        }
        .btn-warning {
            background-color: #ffc107;
            color: #212529;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-info {
            background-color: #17a2b8;
        }
        .price-badge {
            padding: 5px 8px;
            border-radius: 4px;
            font-size: 14px;
        }
        .price-free {
            background-color: #20c997;
            color: white;
        }
        .price-paid {
            background-color: #dc3545;
            color: white;
        }
        .hover-effect:hover {
            transform: scale(1.05);
            transition: 0.3s ease-in-out;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        $(".delete-event-form").on("submit", function(e) {
            e.preventDefault(); // Prevent the default form submission

            let form = $(this);
            let eventId = form.find("input[name='event_id']").val();

            $.ajax({
                type: "POST",
                url: "../../controller/delete_event.php",
                data: { event_id: eventId },
                dataType: "json",
                success: function(response) {
                    if (response.status === "success") {
                        alert("Event deleted successfully!");
                        form.closest("tr").fadeOut(500, function() {
                            $(this).remove(); // Remove row from table
                        });
                    } else {
                        alert("Error: " + response.message);
                    }
                },
                error: function() {
                    alert("Failed to delete event. Please try again.");
                }
            });
        });
    });
</script>

</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">CampusConnect</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
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
    <h2 class="manage-events-heading">Manage Events</h2>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="create_event.php" class="btn btn-success">
            <i class="bi bi-plus-circle"></i> Create New Event
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover shadow-sm">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Event Name</th>
                    <th>Club</th>
                    <th>Description</th>
                    <th>Event Date</th>
                    <th>Location</th>
                    <th>Price</th>
                    <th>Created By</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($event = $result->fetch_assoc()) { ?>
                <tr class="align-middle">
                    <td>
                        <?php if (!empty($event['image_path'])) { ?>
                            <img src="../../<?php echo htmlspecialchars($event['image_path']); ?>" class="event-image hover-effect" alt="Event Image">
                        <?php } else { ?>
                            <span class="text-muted">No Image</span>
                        <?php } ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($event['name']); ?></strong></td>
                    <td><?php echo htmlspecialchars($event['club_name']); ?></td>
                    <td><?php echo htmlspecialchars($event['description']); ?></td>
                    <td><?php echo date("d M Y, h:i A", strtotime($event['event_date'])); ?></td>
                    <td><?php echo htmlspecialchars($event['location']); ?></td>
                    <td>
                        <?php 
                            if ($event['price'] == 0) {
                                echo '<span class="price-badge price-free">Free</span>';
                            } else {
                                echo '<span class="price-badge price-paid">₹' . number_format($event['price'], 2) . '</span>';
                            }
                        ?>
                    </td>
                    <td><?php echo htmlspecialchars($event['created_by']); ?></td>
                    <td>
                        <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
                        <a href="event_details.php?id=<?php echo $event['id']; ?>" class="btn btn-info btn-sm"><i class="bi bi-eye-fill"></i> View</a>
                        <form action="../../controller/delete_event.php" method="post" style="display:inline;">
                            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this event?');">
                                <i class="bi bi-trash-fill"></i> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
