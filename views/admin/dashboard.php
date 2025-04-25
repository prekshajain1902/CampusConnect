<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Ensure only the admin can access this page
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Fetch all clubs
$clubs = $conn->query("SELECT clubs.*, users.name AS coordinator_name FROM clubs 
                       LEFT JOIN users ON clubs.coordinator_id = users.id");

// Fetch all coordinators for assignment
$coordinators = $conn->query("SELECT * FROM users WHERE role = 'coordinator'");
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

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
    </style>
    <script>
$(document).ready(function () {
    $(".assign-coordinator-form").on("submit", function (e) {
        e.preventDefault();

        let form = $(this);
        let formData = form.serialize();

        $.ajax({
            type: "POST",
            url: "../../controller/assign_coordinator.php",
            data: formData,
            dataType: "json",
            success: function (response) {
                let messageBox = $("#messageBox");

                if (response.status === "success") {
                    messageBox.html(`
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                } else {
                    messageBox.html(`
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `);
                }
            },
            error: function () {
                $("#messageBox").html(`
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        Something went wrong. Please try again.
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
            }
        });
    });
});
</script>
</head>
<body>
<div class="sidebar">
    <h3 class="text-center text-white">Admin Panel</h3>
    <a href="manage_clubs.php"><i class="fas fa-users"></i> Manage Clubs</a>
    <a href="manage_users.php"><i class="fas fa-user"></i> Manage Users</a>
    <a href="create_event.php"><i class="fas fa-calendar-plus"></i> Create Event</a>
    <a href="manage_events.php"><i class="fas fa-calendar"></i> Manage Events</a>
    <a href="../../controller/logout.php" class="text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
</div>

<div class="content">
    <div class="container mt-4">
        <h1 class="mb-4">Welcome, <span class="text-primary"><?php echo $_SESSION['user_name']; ?></span></h1>

        <!-- Create Club Form -->
        <div class="card p-3 shadow-sm mb-4">
            <h4 class="mb-3">Create New Club</h4>
            <form action="../../controller/create_club.php" method="post" enctype="multipart/form-data">
                <div class="mb-3">
                    <input type="text" name="name" class="form-control" placeholder="Club Name" required>
                </div>
                <div class="mb-3">
                    <textarea name="description" class="form-control" placeholder="Club Description" required></textarea>
                </div>
                <div class="mb-3">
                    <label>Upload Club Image:</label>
                    <input type="file" name="club_image" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create Club</button>
            </form>
        </div>

        <!-- List of Clubs -->
        <div class="card p-3 shadow-sm">
            <h4 class="mb-3">Existing Clubs</h4>
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Club Image</th>
                        <th>Club Name</th>
                        <th>Description</th>
                        <th>Coordinator</th>
                        <th>Assign Coordinator</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($club = $clubs->fetch_assoc()) { ?>
                    <tr>
                        <td>
                         <img src="/Campus_connect/uploads/clubs/<?php echo htmlspecialchars($club['image_path']); ?>" alt="Club Image" width="100">
                        </td>
                        <td><?php echo $club['name']; ?></td>
                        <td><?php echo $club['description']; ?></td>
                        <td>
                            <?php echo $club['coordinator_name'] ?: "<span class='text-danger'>Not Assigned</span>"; ?>
                        </td>
                        <td>
                            <form action="../../controller/assign_coordinator.php" method="post" class="assign-coordinator-form">
                                <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                <select name="coordinator_id" class="form-select form-select-sm" required>
                                    <option value="">Select Coordinator</option>
                                    <?php
                                    $coordinators = $conn->query("SELECT * FROM users WHERE role = 'coordinator'");
                                    while ($user = $coordinators->fetch_assoc()) { ?>
                                        <option value="<?= $user['id']; ?>"><?= $user['name']; ?> (<?= $user['email']; ?>)</option>
                                    <?php } ?>
                                </select>
                                <button type="submit" class="btn btn-success btn-sm mt-2">Assign</button>
                            </form>
                            <div id="messageBox" class="mt-2"></div>
                        </td>
                        <td>
                            <a href="edit_club?id=<?php echo $club['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>Edit
                            </a>
                            <form action="../../controller/delete_club.php" method="post" style="display:inline;">
                                <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>Delete
                                </button>
                            </form>
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
