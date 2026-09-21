<?php
/**
 * Admin layout header (relative links — no base URL).
 * Set $adminActive before including. Assumes require_admin() already called,
 * except on index.php (login) which uses $publicPage = true.
 */
require_once __DIR__ . '/../../config/functions.php';
if (empty($publicPage)) require_admin();
$admin = current_admin();
$flashes = flash_get();
$pendingOrders = 0; $pendingApps = 0; $unreadMsgs = 0;
try {
    $pendingOrders = (int) db()->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch()['c'];
    $pendingApps = (int) db()->query("SELECT COUNT(*) c FROM applications WHERE status='pending'")->fetch()['c'];
    $unreadMsgs = (int) db()->query("SELECT COUNT(*) c FROM messages WHERE status='unread'")->fetch()['c'];
} catch (Throwable $e) {}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? 'Admin') ?> — Admin · <?= e(setting('site_name')) ?></title>
<link rel="icon" href="../assets/images/favicon.png" type="image/png">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<?php if (!empty($publicPage)): ?>
<main class="login-wrap">
<?php else: ?>
<div class="admin-shell">
  <aside class="sidebar" id="sidebar">
    <a href="dashboard.php" class="side-brand">
      <img src="../assets/images/logo.png" alt="Logo">
      <span><strong>Pallottine Youth</strong><small>Admin Panel</small></span>
    </a>
    <nav class="side-nav">
      <a href="dashboard.php" class="<?= ($adminActive??'')==='dashboard'?'on':'' ?>">📊 Dashboard</a>
      <a href="orders.php" class="<?= ($adminActive??'')==='orders'?'on':'' ?>">🧾 Orders <?= $pendingOrders?'<span class="bdg">'.$pendingOrders.'</span>':'' ?></a>
      <a href="applications.php" class="<?= ($adminActive??'')==='applications'?'on':'' ?>">🙋 Applications <?= $pendingApps?'<span class="bdg">'.$pendingApps.'</span>':'' ?></a>
      <a href="products.php" class="<?= ($adminActive??'')==='products'?'on':'' ?>">🛍️ Products / Shop</a>
      <a href="posts.php" class="<?= ($adminActive??'')==='posts'?'on':'' ?>">📰 News &amp; Blog</a>
      <a href="events.php" class="<?= ($adminActive??'')==='events'?'on':'' ?>">📅 Events</a>
      <a href="messages.php" class="<?= ($adminActive??'')==='messages'?'on':'' ?>">💬 Messages <?= $unreadMsgs?'<span class="bdg">'.$unreadMsgs.'</span>':'' ?></a>
      <a href="settings.php" class="<?= ($adminActive??'')==='settings'?'on':'' ?>">⚙️ Settings &amp; Bank</a>
      <a href="admins.php" class="<?= ($adminActive??'')==='admins'?'on':'' ?>">👤 Admins</a>
    </nav>
    <div class="side-foot">
      <a href="../index.php" target="_blank" rel="noopener">🌐 View Website</a>
      <a href="logout.php">🚪 Logout (<?= e($admin['name'] ?? '') ?>)</a>
    </div>
  </aside>
  <div class="main-col">
    <header class="topbar">
      <button class="nav-toggle" id="sideToggle" aria-label="Menu"><span></span><span></span><span></span></button>
      <h1><?= e($pageTitle ?? 'Dashboard') ?></h1>
      <span class="top-admin">👤 <?= e($admin['name'] ?? '') ?> · <?= e($admin['role'] ?? '') ?></span>
    </header>
    <main class="content">
      <?php foreach ($flashes as $f): ?>
        <div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
      <?php endforeach; ?>
<?php endif; ?>
