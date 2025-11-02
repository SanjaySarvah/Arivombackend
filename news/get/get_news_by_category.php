<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once(__DIR__ . "/../../db.php");
header("Content-Type: application/json; charset=UTF-8");

// ✅ POST only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// ✅ Get category_id
$input = json_decode(file_get_contents("php://input"), true);
$category_id = isset($input["category_id"]) ? intval($input["category_id"]) : 0;

if ($category_id <= 0) {
    echo json_encode(["success" => false, "message" => "Missing or invalid category_id"]);
    exit;
}

try {
    // ✅ Step 1: Get subcategory IDs
    $subQuery = "SELECT id FROM news_subcategories WHERE category_id = ?";
    $subStmt = $conn->prepare($subQuery);
    $subStmt->bind_param("i", $category_id);
    $subStmt->execute();
    $subResult = $subStmt->get_result();

    $subcategory_ids = [];
    while ($sub = $subResult->fetch_assoc()) {
        $subcategory_ids[] = (int)$sub["id"];
    }
    $subStmt->close();

    // ✅ Step 2: Prepare WHERE condition
    $whereClauses = ["n.category_id = ?"];
    $params = [$category_id];
    $types = "i";

    if (!empty($subcategory_ids)) {
        $placeholders = implode(",", array_fill(0, count($subcategory_ids), "?"));
        $whereClauses[] = "n.subcategory_id IN ($placeholders)";
        $types .= str_repeat("i", count($subcategory_ids));
        $params = array_merge($params, $subcategory_ids);
    }

    $whereSQL = implode(" OR ", $whereClauses);

    // ✅ Step 3: Query
    $sql = "SELECT 
                n.id,
                n.category_id,
                n.subcategory_id,
                n.title,
                n.tname,
                n.excerpt,
                n.content,
                n.image,
                n.author,
                n.slug,
                n.likes,
                n.tags,
                n.comments,
                n.seotitle,
                n.seodescription,
                n.seokeywords,
                n.hidingdate,
                n.highlights,
                n.created_at,
                n.updated_at,
                s.name AS subcategory_name,
                s.tname AS subcategory_tname,
                s.slug AS subcategory_slug
            FROM news n
            LEFT JOIN news_subcategories s ON n.subcategory_id = s.id
            WHERE $whereSQL
            ORDER BY n.created_at DESC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    $newsData = [];
    while ($row = $result->fetch_assoc()) {
        $row["tags"] = json_decode($row["tags"], true) ?: [];
        $row["comments"] = json_decode($row["comments"], true) ?: [];

        $newsData[] = [
            "id" => (int)$row["id"],
            "category_id" => (int)$row["category_id"],
            "subcategory_id" => (int)$row["subcategory_id"],
            "subcategory_name" => $row["subcategory_name"] ?? null,
            "subcategory_tname" => $row["subcategory_tname"] ?? null,
            "subcategory_slug" => $row["subcategory_slug"] ?? null,
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

    echo json_encode([
        "success" => true,
        "count" => count($newsData),
        "data" => $newsData
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
?>
