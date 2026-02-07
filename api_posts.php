<?php
require 'db.php';
header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 0;
$limit = 6;
$offset = $page * $limit;

// 建议也在这里加入错误屏蔽，确保返回的是纯净的 JSON
error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 0);

$sql = "SELECT c.cid, c.title, c.slug, c.img, c.created, u.name as author 
        FROM contents c 
        LEFT JOIN users u ON c.aid = u.uid 
        ORDER BY c.is_top DESC, c.created DESC 
        LIMIT $limit OFFSET $offset";

$res = $db->query($sql);
$data = [];

while($row = $res->fetch_assoc()) {
    // 【修复点】确保 img 不为 null，如果是 null 则转换为空字符串
    $row['img'] = $row['img'] ?? ''; 
    // 对标题等文本进行转义
    $row['title'] = htmlspecialchars($row['title']);
    $row['author'] = htmlspecialchars($row['author'] ?? '匿名');
    $data[] = $row;
}

echo json_encode($data);