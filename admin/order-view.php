<?php
$adminActive = 'orders';
require_once __DIR__ . '/../config/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM orders WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$o = $stmt->fetch();
if (!$o) { flash_set('error','Order not found.'); header('Location: orders.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $new = $_POST['status'] ?? $o['status'];
    if (in_array($new, ['pending','confirmed','shipped','delivered','cancelled'], true)) {
        db()->prepare("UPDATE orders SET status=? WHERE id=?")->execute([$new, $id]);
        $o['status'] = $new;
        flash_set('success', 'Order ' . $o['order_code'] . ' marked as ' . $new . '.');
    }
    header('Location: order-view.php?id=' . $id); exit;
}
$pageTitle = 'Order ' . $o['order_code'];
require_once __DIR__ . '/includes/admin-header.php';
$items = json_decode($o['items_json'], true) ?: [];
?>
<div class="grid-2">
  <div class="card">
    <h3>Order Details</h3>
    <dl class="kv">
      <dt>Order Code</dt><dd><?= e($o['order_code']) ?></dd>
      <dt>Status</dt><dd><span class="st <?= status_class($o['status']) ?>"><?= e($o['status']) ?></span></dd>
      <dt>Customer</dt><dd><?= e($o['customer_name']) ?></dd>
      <dt>Email</dt><dd><?= e($o['email']) ?></dd>
      <dt>Phone</dt><dd><?= e($o['phone']) ?></dd>
      <dt>Address</dt><dd><?= e($o['address']) ?><?= $o['city'] ? ', '.e($o['city']) : '' ?><?= $o['state'] ? ', '.e($o['state']) : '' ?></dd>
      <dt>Sender Name</dt><dd><?= e($o['sender_name'] ?: '—') ?></dd>
      <dt>Payment</dt><dd>Bank Transfer</dd>
      <dt>Notes</dt><dd><?= e($o['notes'] ?: '—') ?></dd>
      <dt>Date</dt><dd><?= e(format_datetime($o['created_at'])) ?></dd>
    </dl>
    <h3 style="margin-top:1.2rem">Items</h3>
    <div class="tbl-wrap"><table class="tbl">
      <tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th></tr>
      <?php foreach ($items as $it): ?>
      <tr><td><?= e($it['name']) ?></td><td><?= naira($it['price']) ?></td><td><?= (int)$it['qty'] ?></td><td><?= naira($it['line_total']) ?></td></tr>
      <?php endforeach; ?>
      <tr><td colspan="3"><strong>Subtotal</strong></td><td><?= naira($o['subtotal']) ?></td></tr>
      <tr><td colspan="3"><strong>Delivery</strong></td><td><?= naira($o['delivery_fee']) ?></td></tr>
      <tr><td colspan="3"><strong>Total</strong></td><td><strong><?= naira($o['total']) ?></strong></td></tr>
    </table></div>
  </div>
  <div>
    <div class="card">
      <h3>🧾 Payment Receipt</h3>
      <?php if ($o['receipt_image'] && file_exists(__DIR__ . '/../uploads/receipts/' . $o['receipt_image'])): ?>
        <a href="../uploads/receipts/<?= e($o['receipt_image']) ?>" target="_blank"><img class="receipt-img" src="../uploads/receipts/<?= e($o['receipt_image']) ?>" alt="Receipt"></a>
        <p class="hint">Click to view full size.</p>
      <?php else: ?>
        <p class="hint">No receipt uploaded yet. Ask customer to send via WhatsApp/email with the order code.</p>
      <?php endif; ?>
    </div>
    <div class="card">
      <h3>Update Status</h3>
      <form method="post">
        <?= csrf_field() ?>
        <div class="field">
          <select name="status">
            <?php foreach (['pending'=>'⏳ Pending','confirmed'=>'✅ Confirmed','shipped'=>'🚚 Shipped','delivered'=>'🎉 Delivered','cancelled'=>'❌ Cancelled'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $o['status']===$k?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?>
          </select>
        </div>
        <button class="btn btn-navy" style="margin-top:.8rem" type="submit">Save Status</button>
        <a class="btn btn-outline" href="orders.php">← All Orders</a>
      </form>
      <p class="hint" style="margin-top:.8rem">Tip: verify the receipt against <strong><?= naira($o['total']) ?></strong> from <strong><?= e($o['sender_name'] ?: 'customer') ?></strong> before confirming.</p>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
