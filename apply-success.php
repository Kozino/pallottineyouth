<?php
require_once __DIR__ . '/config/functions.php';
$code = $_GET['code'] ?? '';
$stmt = db()->prepare("SELECT * FROM applications WHERE app_code=? LIMIT 1");
$stmt->execute([$code]);
$app = $stmt->fetch();
$active = 'apply';
$pageTitle = 'Application Received';
require_once __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="container" style="max-width:680px">
    <?php if ($app): ?>
    <div class="form-card success-hero">
      <div class="big">🙏</div>
      <h2>Application Received!</h2>
      <p>Thank you, <strong><?= e($app['full_name']) ?></strong>. Your application for <strong><?= e($app['membership_type']) ?></strong> is now <strong>pending review</strong>.</p>
      <div class="code-chip"><?= e($app['app_code']) ?></div>
      <p class="hint">Keep this code. Our coordinators will contact you at <strong><?= e($app['phone']) ?></strong> / <?= e($app['email']) ?>.</p>
      <div class="share-row" style="justify-content:center">
        <a href="index.php" class="btn btn-navy btn-sm">Back to Home</a>
        <a href="shop.php" class="btn btn-outline btn-sm">Visit Our Shop</a>
      </div>
    </div>
    <?php else: ?>
    <div class="form-card" style="text-align:center"><h2>Application not found</h2><p><a href="apply.php">← Back to application form</a></p></div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
