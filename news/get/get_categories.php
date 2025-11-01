<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once(__DIR__ . "/../../db.php");
header("Content-Type: application/json; charset=UTF-8");

// ✅ Allow only GET method
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

// ✅ Fetch all categories (with Tamil name and slug)
$sql = "SELECT 
            id,
            name,
            tname,
            slug,
            created_at
        FROM news_categories
        ORDER BY id ASC";

$result = $conn->query($sql);

// ✅ Check for query failure
if (!$result) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $conn->error
    ]);
    exit;
}

$categories = [];
while ($row = $result->fetch_assoc()) {
    $categories[] = [
        "id" => (int)$row["id"],
        "name" => $row["name"],
        "tname" => $row["tname"],
        "slug" => $row["slug"],
        "created_at" => $row["created_at"]
    ];
}

// ✅ Return JSON response
if (count($categories) > 0) {
    echo json_encode([
        "success" => true,
        "count" => count($categories),
        "data" => $categories
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    echo json_encode([
        "success" => false,
        "message" => "No categories found"
    ]);
}

$conn->close();
?>
