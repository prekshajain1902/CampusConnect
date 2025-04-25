<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db_name = 'campusconnect';

$conn = new mysqli($host, $user, $pass, $db_name);

if ($conn->connect_error) {
    die('Connection Failed: ' . $conn->connect_error);
}
?>
