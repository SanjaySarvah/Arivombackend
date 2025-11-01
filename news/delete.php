<?php
include_once "../db.php";

$id = $_GET['id'];
$sql = "DELETE FROM news WHERE id=$id";

if ($conn->query($sql)) {
  echo json_encode(["status" => "success", "message" => "News deleted"]);
} else {
  echo json_encode(["status" => "error", "message" => $conn->error]);
}
$conn->close();
?>
