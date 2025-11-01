<?php
// articles/add.php
include_once "../db.php";

$input = json_decode(file_get_contents("php://input"), true);
if (!$input) { echo json_encode(["status"=>"error","message"=>"Invalid JSON"]); exit; }

$title = $input['title'] ?? null;
$category = $input['category'] ?? null;
$subcategory = $input['subcategory'] ?? null;
$subsubcategory = $input['subsubcategory'] ?? null;
$excerpt = $input['excerpt'] ?? null;
$content = $input['content'] ?? null;
$image = $input['image'] ?? null;
$author = $input['author'] ?? null;
$slug = $input['slug'] ?? null;

if (!$title || !$category || !$excerpt || !$content || !$author || !$slug) {
    echo json_encode(["status"=>"error","message"=>"Missing required fields"]);
    exit;
}

$sql = "INSERT INTO articles (title, category, subcategory, subsubcategory, excerpt, content, image, author, slug)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $title, $category, $subcategory, $subsubcategory, $excerpt, $content, $image, $author, $slug);

if ($stmt->execute()) {
    echo json_encode(["status"=>"success","message"=>"Article added","id"=>$stmt->insert_id]);
} else {
    echo json_encode(["status"=>"error","message"=>$stmt->error]);
}
$stmt->close();
$conn->close();
?>
