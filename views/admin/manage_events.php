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
            font-family: 'Arial', sans-serif;
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
        .content {
            margin-left: 270px;
            padding: 20px;
        }
        .table {
            background: #fff;
            border-radius: 8px;
        }
        .table thead {
            background: #007bff;
            color: white;
        }
        .table th, .table td {
            text-align: center;
            vertical-align: middle;
        }
        .btn-custom {
            background: #007bff;
            color: white;
            transition: 0.3s;
        }
        .btn-custom:hover {
            background: #0056b3;
        }
        .event-image {
            width: 80px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
            border: 1px solid #ddd;
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

<!-- Sidebar Navigation -->
<div class="sidebar">
    <h3 class="text-center text-white">Admin Panel</h3>
    <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="manage_clubs.php"><i class="fas fa-users"></i> Manage Clubs</a>
    <a href="manage_users.php"><i class="fas fa-user"></i> Manage Users</a>
    <a href="create_event.php"><i class="fas fa-calendar-plus"></i> Create Event</a>
    <a href="manage_events.php"><i class="fas fa-calendar"></i> Manage Events</a>
    <a href="../../controller/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<!-- Main Content -->
<div class="content">
<h1 class="mb-4">Manage Events</h1>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="create_event" class="btn btn-custom">
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
                      <img src="/Campus_connect/<?php echo htmlspecialchars($event['image_path']); ?>" class="event-image" alt="Event Image">

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
                        <a href="edit_event?id=<?php echo $event['id']; ?>" class="btn btn-warning btn-sm"><i class="bi bi-pencil-square"></i> Edit</a>
                        <a href="event_details?id=<?php echo $event['id']; ?>" class="btn btn-info btn-sm"><i class="bi bi-eye-fill"></i> View</a>
                        <form action="../../controller/delete_event" method="post" style="display:inline;">
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
