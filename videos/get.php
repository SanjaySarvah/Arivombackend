<?php
include_once "../db.php";

$sql = "SELECT * FROM news_videos ORDER BY created_at DESC";
$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
  $row["days_ago"] = floor((time() - strtotime($row["created_at"])) / 86400);
  $data[] = $row;
}

echo json_encode(["status" => "success", "videos" => $data]);
$conn->close();
?>
