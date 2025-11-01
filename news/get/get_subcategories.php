<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once(__DIR__ . "/../../db.php");
header("Content-Type: application/json; charset=UTF-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
$category_id = isset($input["category_id"]) ? intval($input["category_id"]) : 0;

if ($category_id <= 0) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid or missing category_id"
    ]);
    exit;
}

$sql = "SELECT 
            id,
            category_id,
            name,
            tname,
            slug,
            created_at
        FROM news_subcategories
        WHERE category_id = ?
        ORDER BY id ASC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);

if (!$stmt->execute()) {
    echo json_encode([
        "success" => false,
        "message" => "Query failed: " . $stmt->error
    ]);
    exit;
}

$result = $stmt->get_result();
$subcategories = [];

while ($row = $result->fetch_assoc()) {
    $subcategories[] = [
        "id" => (int)$row["id"],
        "category_id" => (int)$row["category_id"],
        "name" => $row["name"],
        "tname" => $row["tname"],
        "slug" => $row["slug"],
        "created_at" => $row["created_at"]
    ];
}

if (count($subcategories) > 0) {
    echo json_encode([
        "success" => true,
        "count" => count($subcategories),
        "data" => $subcategories
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    echo json_encode([
        "success" => false,
        "message" => "No subcategories found for this category"
    ]);
}

$stmt->close();
$conn->close();
?>
