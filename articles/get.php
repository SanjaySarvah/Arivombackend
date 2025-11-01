<?php
// articles/get.php
include_once "../db.php";

$id = isset($_GET['id']) ? intval($_GET['id']) : null;
$slug = isset($_GET['slug']) ? $conn->real_escape_string($_GET['slug']) : null;
$category = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : null;
$subcategory = isset($_GET['subcategory']) ? $conn->real_escape_string($_GET['subcategory']) : null;
$subsubcategory = isset($_GET['subsubcategory']) ? $conn->real_escape_string($_GET['subsubcategory']) : null;
$q = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : null;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : null;

$params = [];
$where = [];
$sql = "SELECT * FROM articles";

if ($id) {
    $where[] = "id = ?";
    $params[] = [$id,'i'];
} elseif ($slug) {
    $where[] = "slug = ?";
    $params[] = [$slug,'s'];
} else {
    if ($category) { $where[] = "category = ?"; $params[] = [$category,'s']; }
    if ($subcategory) { $where[] = "subcategory = ?"; $params[] = [$subcategory,'s']; }
    if ($subsubcategory) { $where[] = "subsubcategory = ?"; $params[] = [$subsubcategory,'s']; }
    if ($q) {
        $where[] = "(title LIKE ? OR excerpt LIKE ? OR content LIKE ?)";
        $like = "%$q%";
        $params[] = [$like,'s'];
        $params[] = [$like,'s'];
        $params[] = [$like,'s'];
    }
}

if (count($where)>0) $sql .= " WHERE " . implode(" AND ", $where);
$sql .= " ORDER BY created_at DESC";
if ($limit && !$id && !$slug) $sql .= " LIMIT ". intval($limit);

$stmt = $conn->prepare($sql);
if ($stmt === false) { echo json_encode(["status"=>"error","message"=>$conn->error]); exit; }

if (count($params)>0) {
    $types=""; $vals=[];
    foreach($params as $p){ $types .= $p[1]; $vals[] = $p[0]; }
    $bind_names[] = $types;
    for ($i=0;$i<count($vals);$i++){ $bind_name='b'.$i; $$bind_name = $vals[$i]; $bind_names[]=&$$bind_name; }
    call_user_func_array([$stmt,'bind_param'],$bind_names);
}

$stmt->execute();
$res = $stmt->get_result();

$items = [];
while ($row = $res->fetch_assoc()) {
    $row['days_ago'] = floor((time() - strtotime($row['created_at'])) / 86400);
    $aid = intval($row['id']);
    $cstmt = $conn->prepare("SELECT COUNT(*) AS cnt FROM comments WHERE item_type='article' AND item_id=? AND status=1");
    $cstmt->bind_param("i", $aid);
    $cstmt->execute();
    $cnt = $cstmt->get_result()->fetch_assoc();
    $row['totalComments'] = intval($cnt['cnt']);
    $cstmt->close();
    $items[] = $row;
}
$stmt->close();

if ($id || $slug) {
    if (count($items) === 0) echo json_encode(["status"=>"error","message"=>"Not found"]);
    else echo json_encode(["status"=>"success","article"=>$items[0]]);
} else {
    echo json_encode(["status"=>"success","articles"=>$items]);
}
$conn->close();
?>
