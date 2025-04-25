<?php
session_start();
include(__DIR__ . "/../../config/db.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch registered events and ticket paths
$query = "SELECT e.name AS event_name, e.event_date, er.ticket_path 
          FROM event_registrations er 
          JOIN events e ON er.event_id = e.id 
          WHERE er.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
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
            padding: 25px;
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
        .qr-code {
            border-radius: 8px;
            box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        }
        .btn-download {
            background-color: #28a745;
            color: white;
        }
        .btn-download:hover {
            background-color: #218838;
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
    <h1 class="heading"><i class="bi bi-ticket-perforated"></i> My Event Tickets</h1>
    <hr>
    
    <a href="dashboard.php" class="btn btn-back mb-3"><i class="bi bi-arrow-left"></i> Back to Dashboard</a>

    <table class="table table-bordered table-hover">
        <thead>
            <tr>
                <th>Event Name</th>
                <th>Date</th>
                <th>QR Code</th>
                <th>Download</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()) { 
                $ticket_path = "../../" . $row['ticket_path']; // Correct file path
            ?>
            <tr>
                <td><?php echo htmlspecialchars($row['event_name']); ?></td>
                <td><?php echo htmlspecialchars($row['event_date']); ?></td>
                <td>
                    <?php if (file_exists($ticket_path)) { ?>
                        <img src="<?php echo $ticket_path; ?>" alt="QR Code" width="100" class="qr-code">
                    <?php } else { ?>
                        <span class="text-danger"><i class="bi bi-exclamation-circle"></i> Not Available</span>
                    <?php } ?>
                </td>
                <td>
                    <?php if (file_exists($ticket_path)) { ?>
                        <a href="download_ticket.php?file=<?php echo urlencode("../" . $row['ticket_path']); ?>" class="btn btn-download btn-sm">
                            <i class="bi bi-download"></i> Download
                        </a>
                    <?php } else { ?>
                        <span class="text-danger"><i class="bi bi-x-circle"></i> Not Available</span>
                    <?php } ?>
                </td>
            </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
