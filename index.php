<?php
session_start();
include('config/db.php'); // Include database connection

// Fetch all clubs
$clubs_query = "SELECT * FROM clubs";
$clubs_result = $conn->query($clubs_query);

// Fetch all events
$events_query = "SELECT * FROM events";
$events_result = $conn->query($events_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
   <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
        <!-- CampusConnect Logo -->
        <!-- <a class="navbar-brand" href="index.php">
            <img src="assets/images/logo.png" alt="CampusConnect Logo" height="40">
        </a> -->
        <a class="navbar-brand" href="#">CampusConnect</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Search Bar -->
        <!-- <div class="ms-auto d-flex">
            <input type="text" id="search" class="form-control me-2" placeholder="Search clubs or events"> -->
            
            <!-- Login Button -->
            <a href="views/auth/login.php" class="btn btn-success">Login</a>
        </div>
    </div>
</nav>

    <main>
       <div class="slideshow-container">
    <div class="slide fade">
        <img src="assets/images/event1.png" alt="Event 1">
    </div>
    <div class="slide fade">
        <img src="assets/images/event2.png" alt="Event 2">
    </div>
    <div class="slide fade">
        <img src="assets/images/event3.png" alt="Event 3">
    </div>

    <!-- Navigation Dots -->
    <div class="dots-container">
        <span class="dot" onclick="currentSlide(1)"></span>
        <span class="dot" onclick="currentSlide(2)"></span>
        <span class="dot" onclick="currentSlide(3)"></span>
    </div>
</div>
       <section class="clubs container mt-5">
    <h2 class="text-center mb-4">Available Clubs</h2>
    <div class="row">
        <?php while ($club = $clubs_result->fetch_assoc()) { ?>
            <div class="col-md-4 mb-4">  <!-- 3 columns per row -->
                <div class="card shadow">
                     <img src="<?php echo '/Campus_connect/uploads/clubs/' . $club['image_path']; ?>" class="card-img-top" alt="<?php echo $club['name']; ?>" style="height: 300px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $club['name']; ?></h5>
                        <p class="card-text"><?php echo $club['description']; ?></p>
                        <button class="btn btn-primary w-100" onclick="joinClub(<?php echo $club['id']; ?>)">Join Club</button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</section>


        <section class="events container mt-5">
    <h2 class="text-center mb-4">Upcoming Events</h2>
    <div class="row">
        <?php while ($event = $events_result->fetch_assoc()) { ?>
            <div class="col-md-4 mb-4">  <!-- 3 columns per row -->
                <div class="card shadow">
                    <img src="<?php echo '/Campus_connect/' . $event['image_path']; ?>" class="card-img-top" alt="<?php echo $event['name']; ?>" style="height: 300px; object-fit: cover;">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $event['name']; ?></h5>
                        <p class="card-text"><?php echo $event['description']; ?></p>
                        <button class="btn btn-primary w-100" onclick="registerEvent(<?php echo $event['id']; ?>)">Register</button>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</section>

    </main>

    <script src="assets/js/script.js"></script>
</body>
</html>
