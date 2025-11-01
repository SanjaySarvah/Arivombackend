<?php
// videos/update.php
include_once "../db.php";
$input = json_decode(file_get_contents("php://input"), true);
if (!$input || !isset($input['id'])) { echo json_encode(["status"=>"error","message"=>"Missing JSON or id"]); exit; }

$id = intval($input['id']);
$allowed = ['title','category','excerpt','content','videoUrl','thumbnail','author','slug','likes','created_at'];
$fields = []; $params = []; $types = "";

foreach ($allowed as $f) {
    if (isset($input[$f])) {
        $fields[] = "$f = ?";
        $params[] = $input[$f];
        $types .= ($f==='likes') ? 'i' : 's';
    }
}

if (count($fields)===0) { echo json_encode(["status"=>"error","message"=>"No valid fields to update"]); exit; }

$sql = "UPDATE news_videos SET ".implode(", ", $fields).", updated_at = NOW() WHERE id = ?";
$params[] = $id; $types .= 'i';
$stmt = $conn->prepare($sql);
$bind_names[] = $types;
for ($i=0;$i<count($params);$i++){ $bn='b'.$i; $$bn = $params[$i]; $bind_names[]=&$$bn; }
call_user_func_array([$stmt,'bind_param'],$bind_names);

if ($stmt->execute()) echo json_encode(["status"=>"success","message"=>"Video updated"]);
else echo json_encode(["status"=>"error","message"=>$stmt->error]);

$stmt->close();
$conn->close();
?>
