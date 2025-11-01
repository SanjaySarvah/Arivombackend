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

// ✅ Read JSON body
$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Invalid JSON format"]);
    exit;
}

// ✅ Extract news_id
$news_id = $data['news_id'] ?? null;

if (!$news_id) {
    echo json_encode(["success" => false, "message" => "Missing required field: news_id"]);
    exit;
}

// ✅ Fetch all activity data for this news_id
$sql = "SELECT 
            id,
            news_id,
            user_id,
            comment,
            like_status,
            share_count,
            view_count,
            created_at
        FROM news_active
        WHERE news_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $news_id);
$stmt->execute();
$result = $stmt->get_result();

$activities = [];
$totalLikes = 0;
$totalShares = 0;
$totalViews = 0;

while ($row = $result->fetch_assoc()) {
    $activities[] = $row;
    $totalLikes  += (int)$row['like_status'];
    $totalShares += (int)$row['share_count'];
    $totalViews  += (int)$row['view_count'];
}

if (count($activities) > 0) {
    echo json_encode([
        "success" => true,
        "message" => "Activity data fetched successfully",
        "summary" => [
            "total_comments" => count(array_filter($activities, fn($a) => !empty($a['comment']))),
            "total_likes"    => $totalLikes,
            "total_shares"   => $totalShares,
            "total_views"    => $totalViews
        ],
        "data" => $activities
    ]);
} else {
    echo json_encode([
        "success" => true,
        "message" => "No activity found for this news_id",
        "summary" => [
            "total_comments" => 0,
            "total_likes" => 0,
            "total_shares" => 0,
            "total_views" => 0
        ],
        "data" => []
    ]);
}

$stmt->close();
$conn->close();
?>
