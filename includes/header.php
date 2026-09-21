<?php
/**
 * Frontend header + navigation (relative links only — no base URL)
 * Expects $pageTitle, $pageDesc, $active variables (optional).
 */
require_once __DIR__ . '/../config/functions.php';
$siteName  = setting('site_name', SITE_NAME_DEFAULT);
$tagline   = setting('tagline', SITE_TAGLINE_DEFAULT);
$announce  = setting('announcement', '');
$cartCount = cart_count();
$flashes   = flash_get();
$active    = $active ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle ?? $siteName) ?> — <?= e($siteName) ?></title>
<meta name="description" content="<?= e($pageDesc ?? $tagline) ?>">
<link rel="icon" href="assets/images/favicon.png" type="image/png">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,500;9..144,600;9..144,700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<?php if ($announce): ?>
<div class="announce-bar">
  <span class="announce-dot"></span>
  <span><?= e($announce) ?></span>
  <a href="apply.php">Join us →</a>
</div>
<?php endif; ?>

<header class="site-header">
  <div class="container header-inner">
    <a href="index.php" class="brand">
      <span class="brand-mark"><img src="assets/images/logo.png" alt="Pallottine Nigerian Youth logo"></span>
      <span class="brand-text">
        <strong>Pallottine Nigerian Youth</strong>
        <small>Society of the Catholic Apostolate · Nigeria</small>
      </span>
    </a>

    <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false">
      <span></span><span></span><span></span>
    </button>

    <div class="nav-overlay" id="navOverlay"></div>
    <nav class="main-nav" id="mainNav">
      <button class="nav-close" id="navClose" aria-label="Close menu">×</button>
      <a href="index.php" class="<?= $active==='home'?'on':'' ?>">Home</a>
      <a href="about.php" class="<?= $active==='about'?'on':'' ?>">About</a>
      <a href="programs.php" class="<?= $active==='programs'?'on':'' ?>">Apostolates</a>
      <a href="news.php" class="<?= $active==='news'?'on':'' ?>">News &amp; Blog</a>
      <a href="events.php" class="<?= $active==='events'?'on':'' ?>">Events</a>
      <a href="shop.php" class="<?= $active==='shop'?'on':'' ?>">Shop</a>
      <a href="contact.php" class="<?= $active==='contact'?'on':'' ?>">Contact</a>
      <a href="cart.php" class="nav-cart <?= $active==='cart'?'on':'' ?>" aria-label="Open cart">
        🛒 Cart <span class="cart-badge" id="cartBadge"><?= (int)$cartCount ?></span>
      </a>
      <a href="apply.php" class="btn btn-gold btn-sm nav-cta">Apply / Join</a>
    </nav>
  </div>
</header>

<?php foreach ($flashes as $f): ?>
<div class="container"><div class="flash flash-<?= e($f['type']) ?>"><?= e($f['msg']) ?></div></div>
<?php endforeach; ?>

<main>
