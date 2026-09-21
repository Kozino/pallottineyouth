<?php
$active = '';
$pageTitle = 'Page Not Found';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container form-card" style="text-align:center;max-width:600px">
    <div style="font-size:4rem">😇</div>
    <h1>404 — Page Not Found</h1>
    <p class="hint">The page you are looking for does not exist or was moved.</p>
    <div class="share-row" style="justify-content:center">
      <a href="index.php" class="btn btn-navy">Go Home</a>
      <a href="shop.php" class="btn btn-outline">Visit Shop</a>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
