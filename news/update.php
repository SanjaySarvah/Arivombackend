<?php
include_once "../db.php";

$id = $_POST['id'];
$title = $_POST['title'];
$category = $_POST['category'];
$subcategory = $_POST['subcategory'];
$excerpt = $_POST['excerpt'];
$content = $_POST['content'];
$author = $_POST['author'];
$slug = $_POST['slug'];

$imageQuery = "";
if (!empty($_FILES['image']['name'])) {
  $targetDir = "../uploads/images/";
  if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
  $fileName = time() . "_" . basename($_FILES["image"]["name"]);
  $targetFile = $targetDir . $fileName;
  move_uploaded_file($_FILES["image"]["tmp_name"], $targetFile);
  $image = "uploads/images/" . $fileName;
  $imageQuery = ", image='$image'";
}

$sql = "UPDATE news SET 
title='$title', category='$category', subcategory='$subcategory', excerpt='$excerpt', content='$content', author='$author', slug='$slug' $imageQuery 
WHERE id=$id";

if ($conn->query($sql)) {
  echo json_encode(["status" => "success", "message" => "News updated successfully"]);
} else {
  echo json_encode(["status" => "error", "message" => $conn->error]);
}
$conn->close();
?>
