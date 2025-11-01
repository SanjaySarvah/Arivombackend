<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../db.php");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// ✅ Get JSON input
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Invalid JSON format"]);
    exit;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(["success" => false, "message" => "Email and password required"]);
    exit;
}

// ✅ Fetch user by email
$stmt = $conn->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    exit;
}

$user = $result->fetch_assoc();

// ✅ Verify password
if (!password_verify($password, $user['password'])) {
    echo json_encode(["success" => false, "message" => "Invalid email or password"]);
    exit;
}

// ✅ Success
echo json_encode([
    "success" => true,
    "message" => "Login successful",
    "user" => [
        "id" => $user['id'],
        "name" => $user['name'],
        "email" => $user['email']
    ]
]);

$stmt->close();
$conn->close();
?>
