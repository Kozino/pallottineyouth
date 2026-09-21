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
    'events'         => (int)$pdo->query("SELECT COUNT(*) c FROM events WHERE status='upcoming'")->fetch()['c'],
];

$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 6")->fetchAll();
$recentApps   = $pdo->query("SELECT * FROM applications ORDER BY id DESC LIMIT 6")->fetchAll();
$recentMsgs   = $pdo->query("SELECT * FROM messages ORDER BY id DESC LIMIT 5")->fetchAll();
$lowStock     = $pdo->query("SELECT * FROM products WHERE status='active' ORDER BY stock ASC LIMIT 5")->fetchAll();

$feed = [];
foreach ($recentOrders as $o) $feed[] = ['t' => $o['created_at'], 'ico' => '🧾', 'html' => '<strong>' . e($o['customer_name']) . '</strong> placed order <a href="order-view.php?id=' . (int)$o['id'] . '"><strong>' . e($o['order_code']) . '</strong></a> · ' . naira($o['total'])];
foreach ($recentApps as $a)   $feed[] = ['t' => $a['created_at'], 'ico' => '🙋', 'html' => '<strong>' . e($a['full_name']) . '</strong> applied (<a href="application-view.php?id=' . (int)$a['id'] . '">' . e($a['app_code']) . '</a>)'];
foreach ($recentMsgs as $m)   $feed[] = ['t' => $m['created_at'], 'ico' => '💬', 'html' => '<strong>' . e($m['name']) . '</strong> sent a message via ' . e($m['source']) . ' — <a href="messages.php?view=' . (int)$m['id'] . '">read</a>'];
usort($feed, fn($a, $b) => strcmp($b['t'], $a['t']));
$feed = array_slice($feed, 0, 8);
?>

<!-- ============ OVERVIEW ============ -->
<div class="stat-grid">
  <a class="stat t-green" href="orders.php">
    <div class="s-row"><span class="s-ico">💰</span><span class="s-label">Confirmed Revenue</span></div>
    <strong><?= naira_short($stats['revenue']) ?></strong>
    <span class="s-link"><?= (int)$stats['total_orders'] ?> total orders →</span>
  </a>
  <a class="stat t-amber" href="orders.php?status=pending">
    <div class="s-row"><span class="s-ico">🧾</span><span class="s-label">Pending Orders</span></div>
    <strong><?= (int)$stats['pending_orders'] ?></strong>
    <span class="s-link">Needs verification →</span>
  </a>
  <a class="stat t-blue" href="applications.php?status=pending">
    <div class="s-row"><span class="s-ico">🙋</span><span class="s-label">Pending Applications</span></div>
    <strong><?= (int)$stats['pending_apps'] ?></strong>
    <span class="s-link"><?= (int)$stats['total_apps'] ?> total →</span>
  </a>
  <a class="stat" href="messages.php?status=unread">
    <div class="s-row"><span class="s-ico">💬</span><span class="s-label">Unread Messages</span></div>
    <strong><?= (int)$stats['unread_msgs'] ?></strong>
    <span class="s-link">Open inbox →</span>
  </a>
  <a class="stat t-grey" href="products.php">
    <div class="s-row"><span class="s-ico">🛍️</span><span class="s-label">Active Products</span></div>
    <strong><?= (int)$stats['products'] ?></strong>
    <span class="s-link"><?= (int)$stats['low_stock'] ?> low stock · manage →</span>
  </a>
  <a class="stat t-grey" href="posts.php">
    <div class="s-row"><span class="s-ico">📰</span><span class="s-label">Published Posts</span></div>
    <strong><?= (int)$stats['posts'] ?></strong>
    <span class="s-link">Manage →</span>
  </a>
  <a class="stat t-grey" href="events.php">
    <div class="s-row"><span class="s-ico">📅</span><span class="s-label">Upcoming Events</span></div>
    <strong><?= (int)$stats['events'] ?></strong>
    <span class="s-link">Manage →</span>
  </a>
  <a class="stat t-grey" href="settings.php">
    <div class="s-row"><span class="s-ico">🏦</span><span class="s-label">Bank Account</span></div>
    <strong style="font-size:1.15rem"><?= e(setting('bank_account_no')) ?></strong>
    <span class="s-link"><?= e(setting('bank_name')) ?> →</span>
  </a>
</div>

<!-- ============ LATEST ============ -->
<div class="grid-2">
  <div class="card">
    <h3>🧾 Latest Orders <span class="count"><?= (int)$stats['total_orders'] ?></span></h3>
    <div class="tbl-wrap narrow"><table class="tbl">
      <tr><th>Order</th><th>Total</th><th>Status</th></tr>
      <?php foreach ($recentOrders as $o): ?>
      <tr>
        <td><a href="order-view.php?id=<?= (int)$o['id'] ?>"><strong><?= e($o['order_code']) ?></strong></a><br><span class="hint"><?= e($o['customer_name']) ?></span></td>
        <td><?= naira($o['total']) ?></td>
        <td><span class="st <?= status_class($o['status']) ?>"><?= e($o['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$recentOrders): ?><tr><td colspan="3" class="hint">No orders yet.</td></tr><?php endif; ?>
    </table></div>
    <p style="margin-top:.8rem"><a class="btn btn-outline btn-sm" href="orders.php">All orders →</a></p>
  </div>
  <div class="card">
    <h3>🙋 Latest Applications <span class="count"><?= (int)$stats['total_apps'] ?></span></h3>
    <div class="tbl-wrap narrow"><table class="tbl">
      <tr><th>Code</th><th>Type</th><th>Status</th></tr>
      <?php foreach ($recentApps as $a): ?>
      <tr>
        <td><a href="application-view.php?id=<?= (int)$a['id'] ?>"><strong><?= e($a['app_code']) ?></strong></a><br><span class="hint"><?= e($a['full_name']) ?></span></td>
        <td><?= e($a['membership_type'] ?: '—') ?></td>
        <td><span class="st <?= status_class($a['status']) ?>"><?= e($a['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$recentApps): ?><tr><td colspan="3" class="hint">No applications yet.</td></tr><?php endif; ?>
    </table></div>
    <p style="margin-top:.8rem"><a class="btn btn-outline btn-sm" href="applications.php">All applications →</a></p>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <h3>⚡ Recent Activity</h3>
    <div class="feed">
      <?php foreach ($feed as $f): ?>
      <div class="feed-item">
        <span class="feed-ico"><?= $f['ico'] ?></span>
        <div><p><?= $f['html'] ?></p><time><?= e(format_datetime($f['t'])) ?></time></div>
      </div>
      <?php endforeach; ?>
      <?php if (!$feed): ?><p class="hint">No activity yet.</p><?php endif; ?>
    </div>
  </div>
  <div>
    <div class="card">
      <h3>🚀 Quick Actions</h3>
      <div class="quick-grid">
        <a class="quick" href="product-form.php"><span>➕</span>Add Product</a>
        <a class="quick" href="post-form.php"><span>✍️</span>New Post</a>
        <a class="quick" href="event-form.php"><span>📅</span>New Event</a>
        <a class="quick" href="orders.php?status=pending"><span>✅</span>Verify Orders</a>
        <a class="quick" href="applications.php?status=pending"><span>🙋</span>Review Apps</a>
        <a class="quick" href="messages.php"><span>💬</span>Inbox</a>
        <a class="quick" href="settings.php"><span>🏦</span>Bank Setup</a>
        <a class="quick" href="../index.php" target="_blank" rel="noopener"><span>🌐</span>View Site</a>
      </div>
    </div>
    <div class="card">
      <h3>⚠️ Stock Watch <span class="count">Lowest first</span></h3>
      <div class="tbl-wrap narrow"><table class="tbl">
        <tr><th>Product</th><th>Stock</th><th></th></tr>
        <?php foreach ($lowStock as $p): ?>
        <tr>
          <td><?= e($p['name']) ?></td>
          <td><span class="st <?= ((int)$p['stock']<=5)?'st-pending':'st-ok' ?>"><?= (int)$p['stock'] ?></span></td>
          <td><a class="btn btn-outline btn-sm" href="product-form.php?id=<?= (int)$p['id'] ?>">Restock</a></td>
        </tr>
        <?php endforeach; ?>
      </table></div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
