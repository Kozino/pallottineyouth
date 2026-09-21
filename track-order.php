<?php
$active = 'shop';
$pageTitle = 'Track Your Order';
require_once __DIR__ . '/includes/header.php';
$code = trim($_GET['code'] ?? '');
$order = null;
if ($code !== '') {
    $stmt = db()->prepare("SELECT * FROM orders WHERE order_code=? LIMIT 1");
    $stmt->execute([$code]);
    $order = $stmt->fetch();
}
$labels = ['pending'=>'⏳ Pending verification','confirmed'=>'✅ Payment confirmed','shipped'=>'🚚 Shipped / Ready for pickup','delivered'=>'🎉 Delivered','cancelled'=>'❌ Cancelled'];
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / Track Order</div>
    <h1>Track Your Order 📦</h1>
    <p>Enter the order code you received at checkout (e.g. PYN-2026-ABC123).</p>
  </div>
</section>
<section class="section">
  <div class="container" style="max-width:680px">
    <form method="get" action="track-order.php" class="form-card" style="display:flex;gap:.7rem;flex-wrap:wrap">
      <input type="text" name="code" value="<?= e($code) ?>" placeholder="Enter order code…" style="flex:1;min-width:200px" required>
      <button class="btn btn-navy" type="submit">Track</button>
    </form>
    <?php if ($code !== ''): ?>
      <?php if ($order): $items = json_decode($order['items_json'], true) ?: []; ?>
      <div class="form-card" style="margin-top:1.2rem">
        <h3>Order <?= e($order['order_code']) ?></h3>
        <p class="hint">Placed <?= e(format_datetime($order['created_at'])) ?> · <?= e($order['customer_name']) ?></p>
        <p style="font-size:1.15rem;margin:.8rem 0"><strong><?= e($labels[$order['status']] ?? $order['status']) ?></strong></p>
        <div class="order-summary" style="box-shadow:none">
          <?php foreach ($items as $it): ?>
          <div class="sum-row"><span><?= e($it['name']) ?> × <?= (int)$it['qty'] ?></span><strong><?= naira($it['line_total']) ?></strong></div>
          <?php endforeach; ?>
          <div class="sum-row"><span>Delivery</span><strong><?= naira($order['delivery_fee']) ?></strong></div>
          <div class="sum-row total"><span>Total</span><span><?= naira($order['total']) ?></span></div>
        </div>
        <p class="hint" style="margin-top:.8rem">Questions? Contact us with your order code via the chat button, <a href="mailto:<?= e(setting('site_email')) ?>">email</a> or WhatsApp.</p>
      </div>
      <?php else: ?>
      <div class="flash flash-error" style="margin-top:1.2rem">No order found with code “<?= e($code) ?>”. Please check and try again.</div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
