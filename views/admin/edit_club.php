<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Ensure only the admin can access this page
if ($_SESSION['user_role'] !== 'admin') {
    header("Location: ../../views/auth/login.php");
    exit();
}

// Get club ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Invalid club ID.");
}

$club_id = $_GET['id'];

// Fetch club details
$stmt = $conn->prepare("SELECT * FROM clubs WHERE id = ?");
$stmt->bind_param("i", $club_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Club not found.");
}

$club = $result->fetch_assoc();

// Fetch coordinators for dropdown
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
</head>
<body>
<div class="container mt-5">
    <h2>Edit Club</h2>
    <form action="../../controller/update_club.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="club_id" value="<?= $club['id']; ?>">

        <div class="mb-3">
            <label>Club Name</label>
            <input type="text" name="name" class="form-control" value="<?= $club['name']; ?>" required>
        </div>

        <div class="mb-3">
            <label>Club Description</label>
            <textarea name="description" class="form-control" required><?= $club['description']; ?></textarea>
        </div>

        <div class="mb-3">
            <label>Assign Coordinator</label>
            <select name="coordinator_id" class="form-select">
                <option value="">Select Coordinator</option>
                <?php while ($user = $coordinators->fetch_assoc()) { ?>
                    <option value="<?= $user['id']; ?>" <?= $user['id'] == $club['coordinator_id'] ? 'selected' : ''; ?>>
                        <?= $user['name']; ?> (<?= $user['email']; ?>)
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="mb-3">
            <label>Current Image</label><br>
             <img src="/Campus_connect/uploads/clubs/<?php echo htmlspecialchars($club['image_path']); ?>" alt="Club Image" width="100">
        </div>

        <div class="mb-3">
            <label>Upload New Image</label>
            <input type="file" name="club_image" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">Update Club</button>
        <a href="dashboard" class="btn btn-secondary">Cancel</a>
    </form>
</div>
</body>
</html>
