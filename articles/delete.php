<?php
// articles/delete.php
include_once "../db.php";

$input = json_decode(file_get_contents("php://input"), true);
$id = null;
if ($input && isset($input['id'])) $id = intval($input['id']);
elseif (isset($_GET['id'])) $id = intval($_GET['id']);

if (!$id) { echo json_encode(["status"=>"error","message"=>"Missing id"]); exit; }

$stmt = $conn->prepare("DELETE FROM articles WHERE id = ?");
$stmt->bind_param("i",$id);
if ($stmt->execute()) echo json_encode(["status"=>"success","message"=>"Article deleted"]);
else echo json_encode(["status"=>"error","message"=>$stmt->error]);

$stmt->close();
$conn->close();
?>
