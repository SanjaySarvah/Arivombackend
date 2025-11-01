<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../db.php");
header("Content-Type: application/json; charset=UTF-8");

// ✅ Allow only POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// ✅ Read JSON input
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Invalid JSON format"]);
    exit;
}

// ✅ Get news_id
$news_id = $data['news_id'] ?? null;

if (!$news_id) {
    echo json_encode(["success" => false, "message" => "Missing news_id"]);
    exit;
}

// ✅ Check if table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'news_active'");
if ($tableCheck->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Table 'news_active' does not exist"]);
    exit;
}

// ✅ Fetch all comments for this news_id
$sql = "SELECT user_id, comment, created_at 
        FROM news_active 
        WHERE news_id = ? AND comment IS NOT NULL AND comment != ''
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();

$comments = [];

while ($row = $result->fetch_assoc()) {
    $user_id = $row['user_id'];
    $commentText = trim($row['comment']);

    // ✅ If comment field contains multiple JSON entries or lines, convert to array
    $commentArray = [];
    if (json_decode($commentText, true) !== null) {
        $decoded = json_decode($commentText, true);
        $commentArray = is_array($decoded) ? $decoded : [$decoded];
    } else {
        // Split by new lines if not JSON
        $commentArray = preg_split("/[\r\n]+/", $commentText, -1, PREG_SPLIT_NO_EMPTY);
    }

    // ✅ Fetch user details
    $userSql = "SELECT id, name, email FROM users WHERE id = ?";
    $userStmt = $conn->prepare($userSql);
    $userStmt->bind_param("i", $user_id);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userData = $userResult->fetch_assoc();
    $userStmt->close();

    // ✅ Push all comments for this user
    foreach ($commentArray as $singleComment) {
        $comments[] = [
            "user" => [
                "id" => $userData['id'] ?? null,
                "name" => $userData['name'] ?? "Unknown",
                "email" => $userData['email'] ?? "N/A",
            ],
            "comment" => $singleComment,
            "created_at" => $row['created_at']
        ];
    }
}

$stmt->close();
$conn->close();

// ✅ Final Response
echo json_encode([
    "success" => true,
    "news_id" => $news_id,
    "total_comments" => count($comments),
    "comments" => $comments
]);
?>
