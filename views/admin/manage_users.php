<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Only Admin can access this page
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../auth/login.php");
    exit();
}

// Get all users except the current admin
// Get users with role 'user' or 'coordinator'
$query = "SELECT id, name, email, role FROM users 
          WHERE role IN ('user', 'coordinator') 
          ORDER BY FIELD(role, 'coordinator', 'user'), name";
$stmt = $conn->prepare($query);
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
        .search-bar {
            width: 100%;
            max-width: 400px;
            margin-bottom: 15px;
        }
    </style>
    <script>
        function confirmPromotion() {
            return confirm("Are you sure you want to promote this user to coordinator?");
        }

       function searchUsers() {
    let input = document.getElementById("searchInput").value.toLowerCase().trim(); // Get input text
    let tableRows = document.querySelectorAll("#userTable tbody tr"); // Get all rows
    let noResultsRow = document.getElementById("noResultsRow"); // Get the "No results found" row
    let found = false; // Flag to track if a match is found

    tableRows.forEach(row => {
        let name = row.cells[0].textContent.toLowerCase(); // Get name
        let email = row.cells[1].textContent.toLowerCase(); // Get email

        if (name.includes(input) || email.includes(input)) {
            row.style.display = ""; // Show matching row
            found = true; // Mark as found
        } else {
            row.style.display = "none"; // Hide non-matching row
        }
    });

    // Show "No results found" if no match is found
    if (!found) {
        noResultsRow.style.display = "table-row";
    } else {
        noResultsRow.style.display = "none";
    }
}


    </script>
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
        <h1 class="mb-4">Manage Users</h1>

        <input type="text" id="searchInput" class="form-control search-bar" placeholder="Search users by name or email..." onkeyup="searchUsers()"> 

        <div class="card p-3 shadow-sm">
            <table class="table table-striped table-hover" id="userTable">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Current Role</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
    <td><?php echo htmlspecialchars($row['name']); ?></td>
    <td><?php echo htmlspecialchars($row['email']); ?></td>
    <td>
        <span class="badge bg-<?php echo $row['role'] === 'coordinator' ? 'info' : 'secondary'; ?>">
            <?php echo ucfirst($row['role']); ?>
        </span>
    </td>
    <td>
        <form action="../../controller/promote_user.php" method="post" onsubmit="return confirm('Are you sure you want to change this user\'s role?');">
            <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>">
            <input type="hidden" name="current_role" value="<?php echo $row['role']; ?>">
            <button type="submit" class="btn btn-<?php echo $row['role'] === 'user' ? 'success' : 'warning'; ?> btn-sm">
                <i class="fas fa-exchange-alt"></i>
                <?php echo $row['role'] === 'user' ? 'Promote to Coordinator' : 'Demote to User'; ?>
            </button>
        </form>
    </td>
</tr>
<?php endwhile; ?>
</tbody>

            </table>
        </div>
    </div>
</div>

</body>
</html>
