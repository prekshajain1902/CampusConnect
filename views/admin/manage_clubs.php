<?php
session_start();
include(__DIR__ . "/../../config/db.php");

if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Fetch all clubs, coordinators, and members
$query = "
    SELECT 
        c.id AS club_id, 
        c.name AS club_name, 
        c.description, 
        c.image_path, 
        u1.name AS coordinator_name, 
        GROUP_CONCAT(u2.name SEPARATOR ', ') AS member_names, 
        GROUP_CONCAT(u2.email SEPARATOR ', ') AS member_emails
    FROM clubs c
    LEFT JOIN users u1 ON c.coordinator_id = u1.id
    LEFT JOIN club_members cm ON c.id = cm.club_id
    LEFT JOIN users u2 ON cm.user_id = u2.id
    GROUP BY c.id, c.name, c.description, c.image_path, u1.name
";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        body {
            background-color: #f4f6f9;
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
            margin-left: 260px;
            padding: 20px;
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
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .table th {
            text-align: center;
        }
        .table td {
            vertical-align: middle;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <h3 class="text-center text-white">Admin Panel</h3>
   <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
    <a href="manage_clubs.php"><i class="fas fa-users"></i> Manage Clubs</a>
    <a href="manage_users.php"><i class="fas fa-user"></i> Manage Users</a>
    <a href="create_event.php"><i class="fas fa-calendar-plus"></i> Create Event</a>
    <a href="manage_events.php"><i class="fas fa-calendar"></i> Manage Events</a>
    <a href="../../controller/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <div class="container mt-4">
        <h1 class="mb-4">Welcome, <span class="text-primary"><?php echo $_SESSION['user_name']; ?></span></h1>

        <!-- Club Coordinators & Members Table -->
        <div class="card p-3 shadow-sm">
            <h4 class="mb-3">Club Coordinators & Members</h4>
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Club Image</th>
                        <th>Club Name</th>
                        <th>Description</th>
                        <th>Coordinator</th>
                        <th>Members</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td>
                             <?php if (!empty($row['image_path'])): ?>
                                    <img src="/campus_connect/uploads/clubs/<?= htmlspecialchars($row['image_path']); ?>" class="club-img" alt="Club Image">
                                <?php else: ?>
                                    <span class="text-muted">No Image</span>
                                <?php endif; ?>
                        </td>
                        <td><?php echo $row['club_name']; ?></td>
                        <td><?php echo $row['description']; ?></td>
                        <td>
                            <span class="badge bg-primary">
                                <?php echo $row['coordinator_name'] ?: "Not Assigned"; ?>
                            </span>
                        </td>
                        <td>
                            <?php
$club_id = $row['club_id'];
$member_query = "
    SELECT u.name, u.email, u.student_stream, u.semester, u.enrollment_no
    FROM club_members cm
    JOIN users u ON cm.user_id = u.id
    WHERE cm.club_id = ?
";
$member_stmt = $conn->prepare($member_query);
$member_stmt->bind_param("i", $club_id);
$member_stmt->execute();
$member_result = $member_stmt->get_result();
?>
<?php if ($member_result->num_rows > 0): ?>
    <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#membersModal<?= $club_id; ?>">
        View Members
    </button>

    <!-- Member Detail Modal -->
    <div class="modal fade" id="membersModal<?= $club_id; ?>" tabindex="-1" aria-labelledby="membersModalLabel<?= $club_id; ?>" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="membersModalLabel<?= $club_id; ?>">Member Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Stream</th>
                                <th>Semester</th>
                                <th>Enrollment No</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($member = $member_result->fetch_assoc()) { ?>
                                <tr>
                                    <td><?= htmlspecialchars($member['name']); ?></td>
                                    <td><?= htmlspecialchars($member['email']); ?></td>
                                    <td><?= htmlspecialchars($member['student_stream']); ?></td>
                                    <td><?= htmlspecialchars($member['semester']); ?></td>
                                    <td><?= htmlspecialchars($member['enrollment_no']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <span class="text-muted">No Members</span>
<?php endif; ?>

                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>
