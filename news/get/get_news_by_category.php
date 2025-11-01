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

// ✅ Validate category_id
if (!isset($_GET["category_id"]) || empty($_GET["category_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Missing or invalid category_id"
    ]);
    exit;
}

$category_id = intval($_GET["category_id"]);

// ✅ Fetch news by category_id
$sql = "SELECT 
            id,
            category_id,
            subcategory_id,
            title,
            tname,
            excerpt,
            content,
            image,
            author,
            slug,
            likes,
            tags,
            comments,
            seotitle,
            seodescription,
            seokeywords,
            hidingdate,
            highlights,
            created_at,
            updated_at
        FROM news
        WHERE category_id = ?
        ORDER BY created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $category_id);
$stmt->execute();
$result = $stmt->get_result();

$newsData = [];

while ($row = $result->fetch_assoc()) {
    // Decode JSON fields safely
    $row["tags"] = json_decode($row["tags"], true) ?: [];
    $row["comments"] = json_decode($row["comments"], true) ?: [];
    
    $newsData[] = [
        "id" => (int)$row["id"],
        "category_id" => (int)$row["category_id"],
        "subcategory_id" => (int)$row["subcategory_id"],
        "title" => $row["title"],
        "tname" => $row["tname"],
        "excerpt" => $row["excerpt"],
        "content" => $row["content"],
        "image" => $row["image"],
        "author" => $row["author"],
        "slug" => $row["slug"],
        "likes" => (int)$row["likes"],
        "tags" => $row["tags"],
        "comments" => $row["comments"],
        "seotitle" => $row["seotitle"],
        "seodescription" => $row["seodescription"],
        "seokeywords" => $row["seokeywords"],
        "hidingdate" => $row["hidingdate"],
        "highlights" => $row["highlights"],
        "created_at" => $row["created_at"],
        "updated_at" => $row["updated_at"]
    ];
}

if (count($newsData) > 0) {
    echo json_encode([
        "success" => true,
        "count" => count($newsData),
        "data" => $newsData
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} else {
    echo json_encode([
        "success" => false,
        "message" => "No news found for this category"
    ]);
}

$stmt->close();
$conn->close();
?>
