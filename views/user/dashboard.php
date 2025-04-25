<?php
session_start();
include(__DIR__ . "/../../config/db.php");

// Ensure only the user can access this page
if ($_SESSION['user_role'] !== 'user') {
    header("Location: ../../views/auth/login.php");
    exit();
}

$user_id = (int)$_SESSION['user_id']; // Ensure it's an integer

// Fetch joined clubs using prepared statement
$sql = "SELECT c.id, c.name, c.description, c.image_path, u.name AS coordinator_name, u.email AS coordinator_email
        FROM clubs c
        JOIN club_members cm ON c.id = cm.club_id
        LEFT JOIN users u ON c.coordinator_id = u.id
        WHERE cm.user_id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("SQL Error (Preparing Statement): " . $conn->error);
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    die("SQL Error (Executing Statement): " . $stmt->error);
}

$joined_clubs = $stmt->get_result();

if (!$joined_clubs) {
    die("SQL Error (Fetching Results): " . $conn->error);
}

// $stmt = $conn->prepare($sql);
// $stmt->bind_param("i", $user_id);
// $stmt->execute();
// $joined_clubs = $stmt->get_result();

// Fetch all clubs (checking membership status)
$sql2 = "SELECT c.*, 
        (SELECT COUNT(*) FROM club_members cm WHERE cm.club_id = c.id AND cm.user_id = ?) AS is_member 
        FROM clubs c";

$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$clubs = $stmt2->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect</title>
    <link rel="stylesheet" href="../../assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
      <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <style type="text/css">
        body {
  font-family: 'Poppins', sans-serif;
  background-color: #f8f9fa;
}
.hero-section {
  background: linear-gradient(rgba(13, 59, 102, 0.95), rgba(13, 59, 102, 0.95)),
              url('/Campus_connect/assets/images/event1.png') no-repeat center center/cover;
  height: 100vh;
  padding: 60px 0;
  color: white;
  position: relative;
}

.hero-content {
  padding-top: 40px;
}

.navbar-brand {
  font-size: 1.6rem;
}

.btn-outline-light,
.btn-outline-info {
  font-weight: 500;
  border-width: 2px;
}
.light-blue-navbar {
    background-color: #e3f2fd; /* Light blue */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow */
}
html {
    scroll-behavior: smooth;
}


/*.light-blue-navbar .navbar-brand,
.light-blue-navbar .nav-link,
.light-blue-navbar .btn {
    color: #0d6efd !important; /* Bootstrap primary blue */
}

/*.light-blue-navbar .btn {
    border: 1px solid #0d6efd;
    background-color: #ffffff;
    transition: background-color 0.3s ease;
}

.light-blue-navbar .btn:hover {
    background-color: #d0e5fc;
}
*/
</style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg light-blue-navbar fixed-top">
  <div class="container">
    <a class="navbar-brand fw-bold" href="#">CampusConnect</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item me-2">
          <a href="ticket.php" class="btn btn-outline-primary">View Tickets</a>
        </li>
        <li class="nav-item me-2">
          <a href="event_list.php" class="btn btn-outline-info">View Events</a>
        </li>
        <li class="nav-item">
          <a href="../../controller/logout.php" class="btn btn-danger">Logout</a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero Section -->
<header class="hero-section d-flex align-items-center justify-content-center text-white text-center">
  <div class="container">
    <h1 class="display-4 fw-bold">Welcome to CampusConnect</h1>
    <p class="lead">Discover clubs, join exciting events, and build your college journey with us.</p>
    <a href="#exploreClubs" class="btn btn-lg btn-primary mt-3">Explore Clubs</a>
  </div>
</header>


<!-- Logos Section -->
<!-- <section class="py-4 bg-white text-center">
  <div class="container">
    <div class="row justify-content-center align-items-center g-4">
      <div class="col-auto"><img src="../../assets/images/event1.png" height="40" alt=""></div>
      <div class="col-auto"><img src="../../assets/images/event1.png" height="40" alt=""></div>
      <div class="col-auto"><img src="../../assets/images/event1.png" height="40" alt=""></div>
      <div class="col-auto"><img src="../../assets/images/event1.png" height="40" alt=""></div>
    </div>
  </div>
</section> -->

<!-- Welcome Message -->
<div class="container mt-5">
    <h2 class="text-center mb-4">👋 Welcome, <?php echo $_SESSION['user_name']; ?>!</h2>

    <?php if (isset($_SESSION['message'])) { ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php } ?>

    <!-- My Clubs -->
    <section class="my-5">
        <h3 class="text-center text-primary mb-4">🎓 My Clubs</h3>
        <div class="row g-4">
            <?php if ($joined_clubs->num_rows > 0): ?>
                <?php while ($club = $joined_clubs->fetch_assoc()) { ?>
                    <div class="col-md-4">
                        <div class="card shadow-sm h-100 border border-primary rounded-4">
                            <img src="<?php echo '/Campus_connect/uploads/clubs/' . $club['image_path']; ?>" class="card-img-top rounded-top-4" alt="Club Image" style="height: 220px; object-fit: cover;">
                            <div class="card-body">
                                <h5 class="card-title d-flex justify-content-between align-items-center">
                                    <?php echo htmlspecialchars($club['name']); ?>
                                    <span class="badge bg-primary">Joined</span>
                                </h5>
                                <p class="text-muted"><?php echo $club['description']; ?></p>
                                <hr>
                                <p><strong>Coordinator:</strong> <?php echo $club['coordinator_name'] ?: 'Not Assigned'; ?></p>
                                <p><strong>Email:</strong> <?php echo $club['coordinator_email'] ?: 'N/A'; ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            <?php else: ?>
                <p class="text-center text-muted">You haven’t joined any clubs yet.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Available Clubs -->
    <section class="my-5">
        <section id="exploreClubs" class="clubs container mt-5">
     <h3 class="text-center text-success mb-4">🧭 Explore Clubs</h3>
    <!-- club cards here -->
</section>

       
        <div class="row g-4">
            <?php while ($club = $clubs->fetch_assoc()) { ?>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-0 rounded-4 club-hover">
                        <img src="<?php echo '/Campus_connect/uploads/clubs/' . $club['image_path']; ?>" class="card-img-top rounded-top-4" alt="Club Image" style="height: 220px; object-fit: cover;">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($club['name']); ?></h5>
                            <p class="text-muted"><?php echo $club['description']; ?></p>
                            <form action="../../controller/<?php echo $club['is_member'] ? 'unjoin_club.php' : 'join_club.php'; ?>" method="post" class="mt-auto">
                                <input type="hidden" name="club_id" value="<?php echo $club['id']; ?>">
                                <button type="submit" class="btn btn-<?php echo $club['is_member'] ? 'outline-danger' : 'outline-success'; ?> w-100">
                                    <i class="fas fa-<?php echo $club['is_member'] ? 'sign-out-alt' : 'sign-in-alt'; ?>"></i>
                                    <?php echo $club['is_member'] ? 'Leave Club' : 'Join Club'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </section>
</div>
</body>
</html>
