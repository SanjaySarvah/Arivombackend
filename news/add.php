<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once("../db.php");
header("Content-Type: application/json; charset=UTF-8");

// ✅ Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
    exit;
}

// ✅ Ensure upload directory exists
$upload_dir = __DIR__ . "/../uploads/news/";
if (!file_exists($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ✅ Decode JSON sent as a text field (key = data)
$jsonData = $_POST['data'] ?? null;
if (!$jsonData) {
    echo json_encode(["success" => false, "message" => "No JSON data received"]);
    exit;
}

$data = json_decode($jsonData, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(["success" => false, "message" => "Invalid JSON format"]);
    exit;
}

// ✅ Collect JSON data
$category_id     = $data['category_id'] ?? null;
$subcategory_id  = $data['subcategory_id'] ?? null;
$highlights      = $data['highlights'] ?? '';
$title           = $data['title'] ?? '';
$tname           = $data['tname'] ?? '';
$excerpt         = $data['excerpt'] ?? '';
$content         = $data['content'] ?? '';
$author          = $data['author'] ?? '';
$slug            = $data['slug'] ?? '';
$likes           = $data['likes'] ?? 0;
$tags            = json_encode($data['tags'] ?? []);
$comments        = json_encode($data['comments'] ?? []);
$seotitle        = $data['seotitle'] ?? '';
$seodescription  = $data['seodescription'] ?? '';
$seokeywords     = json_encode($data['seokeywords'] ?? []);
$hidingdate      = $data['hidingdate'] ?? null;

// ✅ Auto-generate slug if missing
if (empty($slug) && !empty($title)) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
}

// ✅ Handle file upload
$imagePath = null;
if (!empty($_FILES['image']['name'])) {
    $file_name = time() . "_" . basename($_FILES['image']['name']);
    $target_file = $upload_dir . $file_name;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
        $imagePath = "uploads/news/" . $file_name;
    } else {
        echo json_encode(["success" => false, "message" => "File upload failed"]);
        exit;
    }
}

// ✅ Prepare SQL
$sql = "INSERT INTO news 
(category_id, subcategory_id, highlights, title, tname, excerpt, content, image, author, slug, likes, tags, comments, seotitle, seodescription, seokeywords, hidingdate, created_at)
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "SQL prepare failed: " . $conn->error]);
    exit;
}

$stmt->bind_param(
    "iissssssssissssss",
    $category_id,
    $subcategory_id,
    $highlights,
    $title,
    $tname,
    $excerpt,
    $content,
    $imagePath,
    $author,
    $slug,
    $likes,
    $tags,
    $comments,
    $seotitle,
    $seodescription,
    $seokeywords,
    $hidingdate
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "News added successfully",
        "data" => [
            "id" => $stmt->insert_id,
            "title" => $title,
            "slug" => $slug,
            "highlights" => $highlights,
            "image" => $imagePath,
            "created_at" => date('Y-m-d H:i:s')
        ]
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Insert failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
