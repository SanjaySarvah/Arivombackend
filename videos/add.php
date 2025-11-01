<?php
include_once "../db.php";

$title = $_POST['title'];
$category = $_POST['category'];
$excerpt = $_POST['excerpt'];
$content = $_POST['content'];
$author = $_POST['author'];
$slug = $_POST['slug'];

$videoUrl = "";
if (!empty($_FILES['video']['name'])) {
  $targetDir = "../uploads/videos/";
  if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
  $fileName = time() . "_" . basename($_FILES["video"]["name"]);
  $targetFile = $targetDir . $fileName;
  move_uploaded_file($_FILES["video"]["tmp_name"], $targetFile);
  $videoUrl = "uploads/videos/" . $fileName;
}

$thumbnail = "";
if (!empty($_FILES['thumbnail']['name'])) {
  $thumbDir = "../uploads/images/";
  if (!file_exists($thumbDir)) mkdir($thumbDir, 0777, true);
  $thumbFile = time() . "_" . basename($_FILES["thumbnail"]["name"]);
  $thumbTarget = $thumbDir . $thumbFile;
  move_uploaded_file($_FILES["thumbnail"]["tmp_name"], $thumbTarget);
  $thumbnail = "uploads/images/" . $thumbFile;
}

$sql = "INSERT INTO news_videos (title, category, excerpt, content, videoUrl, thumbnail, author, slug)
VALUES ('$title', '$category', '$excerpt', '$content', '$videoUrl', '$thumbnail', '$author', '$slug')";

if ($conn->query($sql)) {
  echo json_encode(["status" => "success", "message" => "Video added successfully"]);
} else {
  echo json_encode(["status" => "error", "message" => $conn->error]);
}
$conn->close();
?>
