<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Ensure only the coordinator can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordinator') {
    header("Location: ../../views/auth/login.php");
    exit();
}

if (!isset($_GET['club_id']) || !ctype_digit($_GET['club_id'])) {
    die("Invalid club.");
}

$club_id = $_GET['club_id'];
$coordinator_id = $_SESSION['user_id'];

// Check if the club belongs to this coordinator
$club_check_query = $conn->prepare("SELECT name FROM clubs WHERE id = ? AND coordinator_id = ?");
$club_check_query->bind_param("ii", $club_id, $coordinator_id);
$club_check_query->execute();
$club_result = $club_check_query->get_result();
$club = $club_result->fetch_assoc();

if (!$club) {
    die("You do not have permission to view this club.");
}

// Fetch members who joined this club from `club_members`
$members_query = $conn->prepare("
    SELECT u.id, u.name, u.email, u.student_stream, u.semester, u.enrollment_no
    FROM club_members cm
    JOIN users u ON cm.user_id = u.id
    WHERE cm.club_id = ?");

$members_query->bind_param("i", $club_id);
$members_query->execute();
$members_result = $members_query->get_result();
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
            max-width: 900px;
            margin-top: 50px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
        }
        .heading {
            color: #007bff;
            font-weight: bold;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .table {
            border-radius: 10px;
            overflow: hidden;
        }
        .table thead {
            background-color: #007bff;
            color: white;
        }
        .table tbody tr:hover {
            background-color: #f1f1f1;
        }
        .no-members {
            text-align: center;
            font-size: 18px;
            padding: 20px;
            border-radius: 5px;
            background: #ffeeba;
            color: #856404;
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
    <h1 class="heading"><i class="bi bi-people"></i> Members of <?php echo htmlspecialchars($club['name']); ?></h1>
    <hr>

    <?php if ($members_result->num_rows > 0) { ?>
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
    <?php while ($member = $members_result->fetch_assoc()) { ?>
    <tr>
        <td><?php echo htmlspecialchars($member['id']); ?></td>
        <td><?php echo htmlspecialchars($member['name']); ?></td>
        <td><?php echo htmlspecialchars($member['email']); ?></td>
        <td><?php echo htmlspecialchars($member['student_stream']); ?></td>
        <td><?php echo htmlspecialchars($member['semester']); ?></td>
        <td><?php echo htmlspecialchars($member['enrollment_no']); ?></td>
    </tr>
    <?php } ?>
</tbody>

        </table>
    <?php } else { ?>
        <p class="no-members"><i class="bi bi-exclamation-circle"></i> No members have joined this club yet.</p>
    <?php } ?>

    <a href="dashboard.php" class="btn btn-back mt-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>
</div>

</body>
</html>
