<?php
// Database Configuration
$host     = "127.0.0.1";
$db_name  = "glowcare_db";
$username = "root";
$password = "";

// Database Connection via MySQLi
$conn = new mysqli($host, $username, $password, $db_name);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>