<?php
require_once __DIR__ . '/config/functions.php';
$code = $_GET['code'] ?? ($_SESSION['last_order'] ?? '');
$stmt = db()->prepare("SELECT * FROM orders WHERE order_code=? LIMIT 1");
$stmt->execute([$code]);
$order = $stmt->fetch();
$active = 'shop';
$pageTitle = 'Order Received';
require_once __DIR__ . '/includes/header.php';
$wa = preg_replace('/\D/', '', setting('site_whatsapp', ''));
?>
<section class="section">
  <div class="container" style="max-width:720px">
    <?php if ($order): $items = json_decode($order['items_json'], true) ?: []; ?>
    <div class="form-card success-hero">
      <div class="big">🎉</div>
      <h2>Thank you, <?= e($order['customer_name']) ?>!</h2>
      <p>Your order has been <strong>received</strong> and is awaiting payment confirmation.</p>
      <div class="code-chip"><?= e($order['order_code']) ?></div>
      <p class="hint">Keep this order code — use it to track your order below.</p>
      <div class="order-summary" style="text-align:left;margin-top:1rem">
        <?php foreach ($items as $it): ?>
        <div class="sum-row"><span><?= e($it['name']) ?> × <?= (int)$it['qty'] ?></span><strong><?= naira($it['line_total']) ?></strong></div>
        <?php endforeach; ?>
        <div class="sum-row"><span>Subtotal</span><strong><?= naira($order['subtotal']) ?></strong></div>
        <div class="sum-row"><span>Delivery</span><strong><?= naira($order['delivery_fee']) ?></strong></div>
        <div class="sum-row total"><span>Total Transferred</span><span><?= naira($order['total']) ?></span></div>
        <div class="sum-row"><span>Status</span><strong>⏳ Pending verification</strong></div>
      </div>
      <div class="share-row" style="justify-content:center">
        <a href="track-order.php?code=<?= urlencode($order['order_code']) ?>" class="btn btn-navy btn-sm">Track This Order</a>
        <a href="shop.php" class="btn btn-outline btn-sm">Continue Shopping</a>
        <?php if ($wa): ?>
        <a target="_blank" rel="noopener" class="btn btn-green btn-sm" href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello! I just placed order ' . $order['order_code'] . ' (' . $order['customer_name'] . '). Find my receipt attached.') ?>">Send Receipt on WhatsApp</a>
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="form-card" style="text-align:center">
      <h2>Order not found</h2>
      <p class="hint">If you just checked out, your order code is on its way. You can also track with your code:</p>
      <p><a href="track-order.php" class="btn btn-navy">Track Order</a></p>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
