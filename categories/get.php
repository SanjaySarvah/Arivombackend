<?php
// get_category.php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
include_once("../db.php");

// Accept either GET ?category_id= or POST JSON { "category_id": N }
$category_id = 0;
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['category_id'])) {
        $category_id = intval($_GET['category_id']);
    }
} else {
    $input = json_decode(file_get_contents("php://input"), true);
    if (!empty($input['category_id'])) {
        $category_id = intval($input['category_id']);
    }
}

if ($category_id <= 0) {
    echo json_encode(["status" => "error", "message" => "Missing or invalid category_id"]);
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT id, name, tname, slug, created_at
        FROM news_categories
        WHERE id = ?
        LIMIT 1
    ");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $row = $result->fetch_assoc()) {
        // ensure fields are strings as expected
        $category = [
            "id" => (string)$row["id"],
            "name" => $row["name"],
            "tname" => $row["tname"],
            "slug" => $row["slug"],
            "created_at" => $row["created_at"]
        ];
        echo json_encode(["status" => "success", "category" => $category], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(["status" => "error", "message" => "Category not found"]);
    }

    $stmt->close();
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => "Server error: ".$e->getMessage()]);
}

$conn->close();
