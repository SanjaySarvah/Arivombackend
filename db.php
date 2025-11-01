<?php
// db.php - DB connection, include config.php before using this
include_once "config.php";

$host = "localhost";
$user = "root";      // XAMPP default
$pass = "";          // XAMPP default
$dbname = "news_db";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Database connection failed: " . $conn->connect_error]);
    exit;
}

// set charset
$conn->set_charset("utf8mb4");
?>
