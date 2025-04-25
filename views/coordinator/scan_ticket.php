<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CampusConnect</title>
    <script src="https://cdn.jsdelivr.net/npm/html5-qrcode/minified/html5-qrcode.min.js"></script>
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            text-align: center;
        }
        .navbar {
            background-color: #007bff;
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
        .scanner-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            max-width: 450px;
            margin: 50px auto;
        }
        .scanner-box {
            border: 2px dashed #007bff;
            border-radius: 10px;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 320px;
            background: #e9ecef;
        }
        .scan-result {
            font-size: 18px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="#">CampusConnect</a>
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
    <!-- Scanner Container -->
    <div class="scanner-container">
        <h2 class="text-primary"><i class="bi bi-upc-scan"></i> Scan Your Ticket</h2>
        <p class="text-muted">Align the QR code inside the box to scan.</p>

        <!-- Scanner Box -->
        <div id="reader" class="scanner-box"></div>

        <!-- Scan Result -->
        <p id="scan-result" class="scan-result text-success mt-3"></p>
        <p id="error-message" class="text-danger"></p> <!-- Show errors -->
    </div>
   <script>
function onScanSuccess(decodedText, decodedResult) {
    console.log("Scanned Ticket Code:", decodedText); // Debugging
    document.getElementById('scan-result').innerText = "Scanned Code: " + decodedText;

    // Ensure correct format before sending
    if (!/^TKT_\d+_\d+_[a-z0-9]{8}$/i.test(decodedText)) {
        alert("Invalid QR Code Format! Please scan a valid event ticket.");
        return;
    }

    fetch('../../controller/mark_ticket.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'ticket_code=' + encodeURIComponent(decodedText) // ✅ Send correct ticket_code
    })
    .then(response => response.json())
    .then(data => {
        console.log("Server Response:", data); // Debugging
        if (data.status === 'success') {
            document.getElementById('scan-result').innerHTML = '<span style="color: green;">' + data.message + '</span>';
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error("Fetch Error:", error);
        alert("Error communicating with the server.");
    });
}

navigator.mediaDevices.getUserMedia({ video: true })
.catch(err => alert("Camera access denied. Please allow camera permissions."));

let scanner = new Html5Qrcode("reader");

scanner.start(
    { facingMode: "environment" },
    { fps: 10, qrbox: 250 },
    onScanSuccess
).catch(error => {
    console.error("Scanner Error:", error);
    alert("Scanner initialization failed.");
});
</script>

</body>
</html>