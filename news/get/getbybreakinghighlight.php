<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once(__DIR__ . "/../../db.php");


header("Content-Type: application/json; charset=UTF-8");

// ✅ Allow only GET method
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// ✅ Prepare SQL — fetch only “Breaking News”
$sql = "SELECT 
            id,
            category_id,
            subcategory_id,
            highlights,
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
            created_at
        FROM news
        WHERE highlights LIKE '%Breaking%'
        ORDER BY created_at DESC";

$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["success" => false, "message" => "Query failed: " . $conn->error]);
    exit;
}

$breaking_news = [];
while ($row = $result->fetch_assoc()) {
    $row['image'] = !empty($row['image'])
        ? "http://localhost/newsapi/" . $row['image']  // ✅ Change domain
        : null;

    $breaking_news[] = $row;
}

if (count($breaking_news) > 0) {
    echo json_encode([
        "success" => true,
        "count" => count($breaking_news),
        "data" => $breaking_news
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode(["success" => false, "message" => "No breaking news found"]);
}

$conn->close();
?>
