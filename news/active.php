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

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Invalid JSON format"]);
    exit;
}

$news_id     = $data['news_id'] ?? null;
$user_id     = $data['user_id'] ?? null;
$comment     = $data['comment'] ?? '';
$like_status = isset($data['like_status']) ? (int)$data['like_status'] : 0;
$share_count = isset($data['share_count']) ? (int)$data['share_count'] : 0;
$view_count  = isset($data['view_count']) ? (int)$data['view_count'] : 0;

if (!$news_id || !$user_id) {
    echo json_encode(["success" => false, "message" => "Missing required fields (news_id or user_id)"]);
    exit;
}

// ✅ Verify table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'news_active'");
if ($tableCheck->num_rows === 0) {
    echo json_encode(["success" => false, "message" => "Table 'news_active' does not exist"]);
    exit;
}

// ✅ Check if record exists
$checkSql = "SELECT * FROM news_active WHERE news_id = ? AND user_id = ?";
$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {
    echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
    exit;
}

$checkStmt->bind_param("ii", $news_id, $user_id);
$checkStmt->execute();
$result = $checkStmt->get_result();

if ($result->num_rows > 0) {
    // 🔄 Record exists → Increment fields individually
    $existing = $result->fetch_assoc();

    $new_likes  = $existing['like_status'] + $like_status;
    $new_shares = $existing['share_count'] + $share_count;
    $new_views  = $existing['view_count'] + $view_count;

    $new_comment = trim($comment) ? ($existing['comment'] . "\n" . $comment) : $existing['comment'];

    $updateSql = "UPDATE news_active 
                  SET like_status = ?, share_count = ?, view_count = ?, comment = ?, updated_at = NOW()
                  WHERE news_id = ? AND user_id = ?";
    $updateStmt = $conn->prepare($updateSql);

    if (!$updateStmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $updateStmt->bind_param("iiisii", $new_likes, $new_shares, $new_views, $new_comment, $news_id, $user_id);

    if ($updateStmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Activity updated successfully",
            "data" => [
                "news_id" => $news_id,
                "user_id" => $user_id,
                "like_status" => $new_likes,
                "share_count" => $new_shares,
                "view_count" => $new_views,
                "comment" => $new_comment
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Update failed: " . $updateStmt->error]);
    }

    $updateStmt->close();

} else {
    // 🆕 Insert new record
    $insertSql = "INSERT INTO news_active (news_id, user_id, comment, like_status, share_count, view_count, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
    $insertStmt = $conn->prepare($insertSql);

    if (!$insertStmt) {
        echo json_encode(["success" => false, "message" => "Prepare failed: " . $conn->error]);
        exit;
    }

    $insertStmt->bind_param("iisiii", $news_id, $user_id, $comment, $like_status, $share_count, $view_count);

    if ($insertStmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Activity recorded successfully",
            "data" => [
                "id" => $insertStmt->insert_id,
                "news_id" => $news_id,
                "user_id" => $user_id,
                "like_status" => $like_status,
                "share_count" => $share_count,
                "view_count" => $view_count,
                "comment" => $comment
            ]
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Insert failed: " . $insertStmt->error]);
    }

    $insertStmt->close();
}

$checkStmt->close();
$conn->close();
?>
