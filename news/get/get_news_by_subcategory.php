<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Include your database config
include_once(__DIR__ . "/../../db.php");

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!$input) {
    echo json_encode(["success" => false, "message" => "Invalid JSON input"]);
    exit;
}

$category_id = isset($input['category_id']) ? intval($input['category_id']) : null;
$subcategory_id = isset($input['subcategory_id']) ? intval($input['subcategory_id']) : null;

// ✅ If category_id and subcategory_id are same — fetch all news
if ($category_id !== null && $subcategory_id !== null && $category_id === $subcategory_id) {
    $query = "SELECT * FROM news ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);
}
// ✅ Else, filter based on available inputs
else {
    $query = "SELECT * FROM news WHERE 1=1";
    $params = [];
    $types = "";

    if ($category_id !== null) {
        $query .= " AND category_id = ?";
        $params[] = $category_id;
        $types .= "i";
    }

    if ($subcategory_id !== null) {
        $query .= " AND subcategory_id = ?";
        $params[] = $subcategory_id;
        $types .= "i";
    }

    $query .= " ORDER BY created_at DESC";
    $stmt = $conn->prepare($query);

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $news = [];
    while ($row = $result->fetch_assoc()) {
        // Optional: decode JSON columns safely
        if (!empty($row['tags'])) $row['tags'] = json_decode($row['tags'], true);
        if (!empty($row['comments'])) $row['comments'] = json_decode($row['comments'], true);

        $news[] = $row;
    }

    echo json_encode([
        "success" => true,
        "count" => count($news),
        "data" => $news
    ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
} else {
    echo json_encode([
        "success" => false,
        "message" => "No news found."
    ]);
}

$stmt->close();
$conn->close();
?>
