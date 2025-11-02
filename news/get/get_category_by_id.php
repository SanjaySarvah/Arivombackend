<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once(__DIR__ . "/../../db.php");
$input = json_decode(file_get_contents("php://input"), true);
if (!isset($input['category_id'])) {
    echo json_encode(["success" => false, "message" => "category_id is required"]);
    exit;
}

$category_id = $input['category_id'];

// Step 1: Get main category
$stmt = $conn->prepare("SELECT id, name, tname, slug, created_at FROM news_categories WHERE id = ?");
if (!$stmt) {
    die(json_encode(["success" => false, "error" => $conn->error, "query" => "SELECT id, name, tname, slug, created_at FROM news_categories WHERE id = ?"]));
}
$stmt->bind_param("i", $category_id);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    echo json_encode(["success" => false, "message" => "Category not found"]);
    exit;
}

// Step 2: Get news for this category
$news_stmt = $conn->prepare("SELECT id, category_id, subcategory_id, title, tname, excerpt, content, image, author, slug, likes, tags, comments, seotitle, seodescription, seokeywords, hidingdate, highlights, created_at, updated_at FROM news WHERE category_id = ?");
if (!$news_stmt) {
    die(json_encode(["success" => false, "error" => $conn->error, "query" => "SELECT * FROM news WHERE category_id = ?"]));
}
$news_stmt->bind_param("i", $category_id);
$news_stmt->execute();
$news_result = $news_stmt->get_result();
$news = [];
while ($row = $news_result->fetch_assoc()) {
    $news[] = $row;
}

// Step 3: Get subcategories of this category
$sub_stmt = $conn->prepare("SELECT id, category_id, name, tname, slug, created_at FROM news_subcategories WHERE category_id = ?");
if (!$sub_stmt) {
    die(json_encode(["success" => false, "error" => $conn->error, "query" => "SELECT id, category_id, name, tname, slug, created_at FROM news_subcategories WHERE category_id = ?"]));
}
$sub_stmt->bind_param("i", $category_id);
$sub_stmt->execute();
$sub_result = $sub_stmt->get_result();
$subcategories = [];

while ($sub = $sub_result->fetch_assoc()) {
    // Step 4: For each subcategory, get its news
    $sub_news_stmt = $conn->prepare("SELECT id, category_id, subcategory_id, title, tname, excerpt, content, image, author, slug, likes, tags, comments, seotitle, seodescription, seokeywords, hidingdate, highlights, created_at, updated_at FROM news WHERE subcategory_id = ?");
    if (!$sub_news_stmt) {
        die(json_encode(["success" => false, "error" => $conn->error, "query" => "SELECT * FROM news WHERE subcategory_id = ?"]));
    }
    $sub_news_stmt->bind_param("i", $sub['id']);
    $sub_news_stmt->execute();
    $sub_news_result = $sub_news_stmt->get_result();
    $sub_news = [];

    while ($row = $sub_news_result->fetch_assoc()) {
        $sub_news[] = $row;
    }

    $sub['news'] = $sub_news;
    $sub['children'] = [];
    $subcategories[] = $sub;
}

echo json_encode([
    "success" => true,
    "data" => [
        "category" => $category,
        "news" => $news,
        "subcategories" => $subcategories
    ]
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
