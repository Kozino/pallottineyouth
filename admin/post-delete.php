<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT image FROM posts WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$row = $stmt->fetch();
if ($row) {
    db()->prepare("DELETE FROM posts WHERE id=?")->execute([$id]);
    if (!empty($row['image'])) @unlink(__DIR__ . '/../uploads/posts/' . $row['image']);
    flash_set('success', 'Post deleted.');
}
header('Location: posts.php'); exit;
