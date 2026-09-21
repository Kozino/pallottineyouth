<?php
$adminActive = 'dashboard';
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = db();
$stats = [
    'pending_orders' => (int)$pdo->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch()['c'],
    'total_orders'   => (int)$pdo->query("SELECT COUNT(*) c FROM orders")->fetch()['c'],
    'revenue'        => (float)($pdo->query("SELECT COALESCE(SUM(total),0) s FROM orders WHERE status IN ('confirmed','shipped','delivered')")->fetch()['s'] ?? 0),
    'pending_apps'   => (int)$pdo->query("SELECT COUNT(*) c FROM applications WHERE status='pending'")->fetch()['c'],
    'total_apps'     => (int)$pdo->query("SELECT COUNT(*) c FROM applications")->fetch()['c'],
    'products'       => (int)$pdo->query("SELECT COUNT(*) c FROM products WHERE status='active'")->fetch()['c'],
    'low_stock'      => (int)$pdo->query("SELECT COUNT(*) c FROM products WHERE status='active' AND stock <= 5")->fetch()['c'],
    'posts'          => (int)$pdo->query("SELECT COUNT(*) c FROM posts WHERE status='published'")->fetch()['c'],
    'unread_msgs'    => (int)$pdo->query("SELECT COUNT(*) c FROM messages WHERE status='unread'")->fetch()['c'],
];
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 6")->fetchAll();
$recentApps = $pdo->query("SELECT * FROM applications ORDER BY id DESC LIMIT 6")->fetchAll();
$lowStock = $pdo->query("SELECT * FROM products WHERE status='active' ORDER BY stock ASC LIMIT 5")->fetchAll();
?>
<div class="stat-grid">
  <div class="stat"><div class="ico">🧾</div><strong><?= (int)$stats['pending_orders'] ?></strong><span>Pending orders</span><br><small><a href="orders.php?status=pending">Review →</a></small></div>
  <div class="stat"><div class="ico">💰</div><strong><?= naira_short($stats['revenue']) ?></strong><span>Confirmed revenue (<?= (int)$stats['total_orders'] ?> orders)</span></div>
  <div class="stat"><div class="ico">🙋</div><strong><?= (int)$stats['pending_apps'] ?></strong><span>Pending applications</span><br><small><a href="applications.php?status=pending">Review →</a></small></div>
  <div class="stat"><div class="ico">💬</div><strong><?= (int)$stats['unread_msgs'] ?></strong><span>Unread messages</span><br><small><a href="messages.php?status=unread">Open inbox →</a></small></div>
  <div class="stat"><div class="ico">🛍️</div><strong><?= (int)$stats['products'] ?></strong><span>Active products</span><br><small><a href="product-form.php">+ Add product</a></small></div>
  <div class="stat"><div class="ico">⚠️</div><strong><?= (int)$stats['low_stock'] ?></strong><span>Low stock (≤5)</span></div>
  <div class="stat"><div class="ico">📰</div><strong><?= (int)$stats['posts'] ?></strong><span>Published posts</span><br><small><a href="post-form.php">+ New post</a></small></div>
  <div class="stat"><div class="ico">🏦</div><strong style="font-size:1.05rem"><?= e(setting('bank_account_no')) ?></strong><span><?= e(setting('bank_name')) ?></span><br><small><a href="settings.php">Edit bank →</a></small></div>
</div>

<div class="grid-2">
  <div class="card">
    <h3>Latest Orders</h3>
    <div class="tbl-wrap"><table class="tbl">
      <tr><th>Code</th><th>Customer</th><th>Total</th><th>Status</th></tr>
      <?php foreach ($recentOrders as $o): ?>
      <tr>
        <td><a href="order-view.php?id=<?= (int)$o['id'] ?>"><strong><?= e($o['order_code']) ?></strong></a></td>
        <td><?= e($o['customer_name']) ?></td>
        <td><?= naira($o['total']) ?></td>
        <td><span class="st <?= status_class($o['status']) ?>"><?= e($o['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$recentOrders): ?><tr><td colspan="4" class="hint">No orders yet.</td></tr><?php endif; ?>
    </table></div>
  </div>
  <div class="card">
    <h3>Latest Applications</h3>
    <div class="tbl-wrap"><table class="tbl">
      <tr><th>Code</th><th>Name</th><th>Type</th><th>Status</th></tr>
      <?php foreach ($recentApps as $a): ?>
      <tr>
        <td><a href="application-view.php?id=<?= (int)$a['id'] ?>"><strong><?= e($a['app_code']) ?></strong></a></td>
        <td><?= e($a['full_name']) ?></td>
        <td><?= e($a['membership_type'] ?: '—') ?></td>
        <td><span class="st <?= status_class($a['status']) ?>"><?= e($a['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$recentApps): ?><tr><td colspan="4" class="hint">No applications yet.</td></tr><?php endif; ?>
    </table></div>
  </div>
</div>

<div class="card">
  <h3>Stock Watch (lowest first)</h3>
  <div class="tbl-wrap"><table class="tbl">
    <tr><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th></th></tr>
    <?php foreach ($lowStock as $p): ?>
    <tr>
      <td><strong><?= e($p['name']) ?></strong></td>
      <td><?= e($p['category']) ?></td>
      <td><?= naira($p['price']) ?></td>
      <td><span class="st <?= ((int)$p['stock']<=5)?'st-pending':'st-ok' ?>"><?= (int)$p['stock'] ?></span></td>
      <td><a class="btn btn-outline btn-sm" href="product-form.php?id=<?= (int)$p['id'] ?>">Restock</a></td>
    </tr>
    <?php endforeach; ?>
  </table></div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
