<?php
$adminActive = 'dashboard';
$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/admin-header.php';

$pdo = db();

/* ---------- KPI stats ---------- */
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

/* ---------- Analytics: last 6 months buckets (driver-agnostic) ---------- */
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-$i months"));
    $months[$key] = ['label' => date('M', strtotime("-$i months")), 'revenue' => 0, 'orders' => 0, 'apps' => 0, 'msgs' => 0];
}
$paidStatuses = ['confirmed', 'shipped', 'delivered'];
foreach ($pdo->query("SELECT total, status, created_at FROM orders") as $r) {
    $k = substr((string)$r['created_at'], 0, 7);
    if (!isset($months[$k])) continue;
    $months[$k]['orders']++;
    if (in_array($r['status'], $paidStatuses, true)) $months[$k]['revenue'] += (float)$r['total'];
}
foreach ($pdo->query("SELECT created_at FROM applications") as $r) {
    $k = substr((string)$r['created_at'], 0, 7);
    if (isset($months[$k])) $months[$k]['apps']++;
}
foreach ($pdo->query("SELECT created_at FROM messages") as $r) {
    $k = substr((string)$r['created_at'], 0, 7);
    if (isset($months[$k])) $months[$k]['msgs']++;
}
$chLabels  = array_column($months, 'label');
$chRevenue = array_column($months, 'revenue');
$chOrders  = array_column($months, 'orders');
$chApps    = array_column($months, 'apps');

/* ---------- Breakdowns ---------- */
$orderStatus = ['pending' => 0, 'confirmed' => 0, 'shipped' => 0, 'delivered' => 0, 'cancelled' => 0];
foreach ($pdo->query("SELECT status, COUNT(*) c FROM orders GROUP BY status") as $r) {
    if (isset($orderStatus[$r['status']])) $orderStatus[$r['status']] = (int)$r['c'];
}
$appStatus = ['pending' => 0, 'reviewed' => 0, 'accepted' => 0, 'rejected' => 0];
foreach ($pdo->query("SELECT status, COUNT(*) c FROM applications GROUP BY status") as $r) {
    if (isset($appStatus[$r['status']])) $appStatus[$r['status']] = (int)$r['c'];
}
// Top states
$stateCounts = [];
foreach ($pdo->query("SELECT state, COUNT(*) c FROM applications WHERE state IS NOT NULL AND state != '' GROUP BY state ORDER BY c DESC LIMIT 8") as $r) {
    $stateCounts[$r['state']] = (int)$r['c'];
}
// Top products by qty ordered
$prodQty = [];
foreach ($pdo->query("SELECT items_json FROM orders WHERE status != 'cancelled'") as $r) {
    foreach ((json_decode($r['items_json'], true) ?: []) as $it) {
        $nm = $it['name'] ?? 'Item';
        $prodQty[$nm] = ($prodQty[$nm] ?? 0) + (int)($it['qty'] ?? 0);
    }
}
arsort($prodQty);
$prodQty = array_slice($prodQty, 0, 6, true);
// Messages by source
$msgSrc = ['contact' => 0, 'livechat' => 0];
foreach ($pdo->query("SELECT source, COUNT(*) c FROM messages GROUP BY source") as $r) {
    $msgSrc[$r['source']] = ($msgSrc[$r['source']] ?? 0) + (int)$r['c'];
}

/* ---------- Lists & activity ---------- */
$recentOrders = $pdo->query("SELECT * FROM orders ORDER BY id DESC LIMIT 5")->fetchAll();
$recentApps   = $pdo->query("SELECT * FROM applications ORDER BY id DESC LIMIT 5")->fetchAll();
$recentMsgs   = $pdo->query("SELECT * FROM messages ORDER BY id DESC LIMIT 5")->fetchAll();
$lowStock     = $pdo->query("SELECT * FROM products WHERE status='active' ORDER BY stock ASC LIMIT 5")->fetchAll();

$feed = [];
foreach ($recentOrders as $o) $feed[] = ['t' => $o['created_at'], 'ico' => '🧾', 'html' => '<strong>' . e($o['customer_name']) . '</strong> placed order <a href="order-view.php?id=' . (int)$o['id'] . '"><strong>' . e($o['order_code']) . '</strong></a> · ' . naira($o['total'])];
foreach ($recentApps as $a)   $feed[] = ['t' => $a['created_at'], 'ico' => '🙋', 'html' => '<strong>' . e($a['full_name']) . '</strong> applied (<a href="application-view.php?id=' . (int)$a['id'] . '">' . e($a['app_code']) . '</a>)'];
foreach ($recentMsgs as $m)   $feed[] = ['t' => $m['created_at'], 'ico' => '💬', 'html' => '<strong>' . e($m['name']) . '</strong> sent a message via ' . e($m['source']) . ' — <a href="messages.php?view=' . (int)$m['id'] . '">read</a>'];
usort($feed, fn($a, $b) => strcmp($b['t'], $a['t']));
$feed = array_slice($feed, 0, 9);
?>

<!-- ============ KPI ROW ============ -->
<div class="kpi-grid">
  <a class="kpi c-green" href="orders.php?status=pending">
    <div class="k-top"><span class="k-ico">💰</span><span class="k-label">Confirmed Revenue</span></div>
    <strong><?= naira_short($stats['revenue']) ?></strong>
    <span class="k-sub"><?= (int)$stats['total_orders'] ?> total orders →</span>
  </a>
  <a class="kpi c-amber" href="orders.php?status=pending">
    <div class="k-top"><span class="k-ico">🧾</span><span class="k-label">Pending Orders</span></div>
    <strong><?= (int)$stats['pending_orders'] ?></strong>
    <span class="k-sub">Needs verification →</span>
  </a>
  <a class="kpi c-blue" href="applications.php?status=pending">
    <div class="k-top"><span class="k-ico">🙋</span><span class="k-label">Pending Applications</span></div>
    <strong><?= (int)$stats['pending_apps'] ?></strong>
    <span class="k-sub"><?= (int)$stats['total_apps'] ?> total →</span>
  </a>
  <a class="kpi" href="messages.php?status=unread">
    <div class="k-top"><span class="k-ico">💬</span><span class="k-label">Unread Messages</span></div>
    <strong><?= (int)$stats['unread_msgs'] ?></strong>
    <span class="k-sub">Open inbox →</span>
  </a>
  <a class="kpi" href="products.php">
    <div class="k-top"><span class="k-ico">🛍️</span><span class="k-label">Active Products</span></div>
    <strong><?= (int)$stats['products'] ?></strong>
    <small><?= (int)$stats['low_stock'] ?> low stock (≤5) · <a href="product-form.php">+ Add</a></small>
  </a>
  <a class="kpi c-green" href="posts.php">
    <div class="k-top"><span class="k-ico">📰</span><span class="k-label">Published Posts</span></div>
    <strong><?= (int)$stats['posts'] ?></strong>
    <small><a href="post-form.php">+ New post</a></small>
  </a>
  <a class="kpi c-blue" href="events.php">
    <div class="k-top"><span class="k-ico">📅</span><span class="k-label">Upcoming Events</span></div>
    <strong><?= (int)$stats['events'] ?></strong>
    <small><a href="event-form.php">+ New event</a></small>
  </a>
  <a class="kpi c-amber" href="settings.php">
    <div class="k-top"><span class="k-ico">🏦</span><span class="k-label">Bank Account</span></div>
    <strong style="font-size:1.15rem"><?= e(setting('bank_account_no')) ?></strong>
    <small><?= e(setting('bank_name')) ?> · edit →</small>
  </a>
</div>

<!-- ============ ANALYTICS ============ -->
<div class="card">
  <h3>📈 Performance Analytics <span class="tag">Last 6 months</span></h3>
  <div class="chart-grid">
    <div>
      <p class="hint" style="margin-bottom:.5rem">Revenue trend (confirmed orders)</p>
      <div class="chart-box"><canvas id="chRevenue"></canvas></div>
    </div>
    <div>
      <p class="hint" style="margin-bottom:.5rem">Orders vs applications</p>
      <div class="chart-box"><canvas id="chVolume"></canvas></div>
    </div>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <h3>🧾 Orders by Status <span class="tag"><?= (int)$stats['total_orders'] ?> total</span></h3>
    <div class="chart-box short"><canvas id="chOrderStatus"></canvas></div>
    <p style="margin-top:.7rem"><a class="btn btn-outline btn-sm" href="orders.php">Manage orders →</a></p>
  </div>
  <div class="card">
    <h3>🙋 Applications by Status <span class="tag"><?= (int)$stats['total_apps'] ?> total</span></h3>
    <div class="chart-box short"><canvas id="chAppStatus"></canvas></div>
    <p style="margin-top:.7rem"><a class="btn btn-outline btn-sm" href="applications.php">Review applications →</a></p>
  </div>
</div>

<div class="grid-2">
  <div class="card">
    <h3>🗺️ Applications by State <span class="tag">Top 8</span></h3>
    <?php if ($stateCounts): ?>
    <div class="chart-box short"><canvas id="chStates"></canvas></div>
    <?php else: ?><p class="hint">No application data yet.</p><?php endif; ?>
  </div>
  <div class="card">
    <h3>🏆 Best-Selling Items <span class="tag">By qty ordered</span></h3>
    <?php if ($prodQty): ?>
    <div class="chart-box short"><canvas id="chProducts"></canvas></div>
    <?php else: ?><p class="hint">No sales yet — best sellers will appear here.</p><?php endif; ?>
  </div>
</div>

<!-- ============ ACTIVITY + QUICK ACTIONS ============ -->
<div class="grid-2">
  <div class="card">
    <h3>⚡ Recent Activity <span class="tag">Live feed</span></h3>
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
      <h3>⚠️ Stock Watch <span class="tag">Lowest first</span></h3>
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

<!-- ============ LATEST TABLES ============ -->
<div class="grid-2">
  <div class="card">
    <h3>Latest Orders</h3>
    <div class="tbl-wrap narrow"><table class="tbl">
      <tr><th>Code</th><th>Total</th><th>Status</th></tr>
      <?php foreach ($recentOrders as $o): ?>
      <tr>
        <td><a href="order-view.php?id=<?= (int)$o['id'] ?>"><strong><?= e($o['order_code']) ?></strong></a><br><span class="hint"><?= e($o['customer_name']) ?></span></td>
        <td><?= naira($o['total']) ?></td>
        <td><span class="st <?= status_class($o['status']) ?>"><?= e($o['status']) ?></span></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$recentOrders): ?><tr><td colspan="3" class="hint">No orders yet.</td></tr><?php endif; ?>
    </table></div>
  </div>
  <div class="card">
    <h3>Latest Applications</h3>
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
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
  if (typeof Chart === "undefined") return; /* offline: tables still work */
  Chart.defaults.font.family = "'Inter',system-ui,sans-serif";
  Chart.defaults.color = "#8a7579";
  var RED = "#C8102E", DARK = "#7A0B1A", GREEN = "#128C4B", AMBER = "#D97706", BLUE = "#1D4ED8", GREY = "#C9B8BB";

  var labels = <?= json_encode($chLabels) ?>;

  /* Revenue line */
  var el1 = document.getElementById("chRevenue");
  if (el1) new Chart(el1, {
    type: "line",
    data: { labels: labels, datasets: [{ label: "Revenue (₦)", data: <?= json_encode($chRevenue) ?>, borderColor: RED, backgroundColor: "rgba(200,16,46,.12)", fill: true, tension: .4, pointBackgroundColor: DARK, borderWidth: 3 }] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: function (v) { return "₦" + (v >= 1000 ? (v / 1000) + "k" : v); } } } } }
  });

  /* Orders vs apps bar */
  var el2 = document.getElementById("chVolume");
  if (el2) new Chart(el2, {
    type: "bar",
    data: { labels: labels, datasets: [
      { label: "Orders", data: <?= json_encode($chOrders) ?>, backgroundColor: RED, borderRadius: 6 },
      { label: "Applications", data: <?= json_encode($chApps) ?>, backgroundColor: DARK, borderRadius: 6 }
    ] },
    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: "bottom" } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
  });

  /* Orders by status doughnut */
  var el3 = document.getElementById("chOrderStatus");
  if (el3) new Chart(el3, {
    type: "doughnut",
    data: { labels: ["Pending", "Confirmed", "Shipped", "Delivered", "Cancelled"],
      datasets: [{ data: <?= json_encode(array_values($orderStatus)) ?>, backgroundColor: [AMBER, BLUE, "#7C3AED", GREEN, GREY], borderWidth: 2, borderColor: "#fff" }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: "62%", plugins: { legend: { position: "right" } } }
  });

  /* Applications by status doughnut */
  var el4 = document.getElementById("chAppStatus");
  if (el4) new Chart(el4, {
    type: "doughnut",
    data: { labels: ["Pending", "Reviewed", "Accepted", "Rejected"],
      datasets: [{ data: <?= json_encode(array_values($appStatus)) ?>, backgroundColor: [AMBER, BLUE, GREEN, "#DC2626"], borderWidth: 2, borderColor: "#fff" }] },
    options: { responsive: true, maintainAspectRatio: false, cutout: "62%", plugins: { legend: { position: "right" } } }
  });

  /* States horizontal bar */
  var el5 = document.getElementById("chStates");
  <?php if ($stateCounts): ?>
  if (el5) new Chart(el5, {
    type: "bar",
    data: { labels: <?= json_encode(array_keys($stateCounts)) ?>,
      datasets: [{ data: <?= json_encode(array_values($stateCounts)) ?>, backgroundColor: RED, borderRadius: 6 }] },
    options: { indexAxis: "y", responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
  <?php endif; ?>

  /* Top products horizontal bar */
  var el6 = document.getElementById("chProducts");
  <?php if ($prodQty): ?>
  if (el6) new Chart(el6, {
    type: "bar",
    data: { labels: <?= json_encode(array_map(fn($n) => mb_strimwidth($n, 0, 26, "…"), array_keys($prodQty))) ?>,
      datasets: [{ label: "Qty sold", data: <?= json_encode(array_values($prodQty)) ?>, backgroundColor: [RED, DARK, AMBER, BLUE, GREEN, "#7C3AED"], borderRadius: 6 }] },
    options: { indexAxis: "y", responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { precision: 0 } } } }
  });
  <?php endif; ?>
})();
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
