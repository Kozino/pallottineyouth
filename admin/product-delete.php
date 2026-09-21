<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT image FROM products WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$row = $stmt->fetch();
if ($row) {
    db()->prepare("DELETE FROM products WHERE id=?")->execute([$id]);
    if (!empty($row['image'])) @unlink(__DIR__ . '/../uploads/products/' . $row['image']);
    flash_set('success', 'Product deleted.');
} else {
    flash_set('error', 'Product not found.');
}
header('Location: products.php'); exit;
