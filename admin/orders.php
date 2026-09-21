<?php
$adminActive = 'orders';
$pageTitle = 'Orders';
require_once __DIR__ . '/includes/admin-header.php';
$status = $_GET['status'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$where = '1=1'; $params = [];
if (in_array($status, ['pending','confirmed','shipped','delivered','cancelled'], true)) { $where .= " AND status=?"; $params[] = $status; }
if ($q !== '') { $where .= " AND (order_code LIKE ? OR customer_name LIKE ? OR phone LIKE ?)"; $params[]="%$q%"; $params[]="%$q%"; $params[]="%$q%"; }
$stmt = db()->prepare("SELECT * FROM orders WHERE $where ORDER BY id DESC LIMIT 200");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="toolbar">
  <form method="get" action="orders.php">
    <input type="text" name="q" placeholder="Search code / name / phone…" value="<?= e($q) ?>">
    <select name="status">
      <option value="all">All statuses</option>
      <?php foreach (['pending','confirmed','shipped','delivered','cancelled'] as $s): ?>
      <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-navy btn-sm" type="submit">Filter</button>
  </form>
</div>
<div class="tbl-wrap"><table class="tbl">
  <tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Receipt</th><th>Status</th><th>Date</th><th></th></tr>
  <?php foreach ($rows as $o):
    $items = json_decode($o['items_json'], true) ?: [];
    $count = array_sum(array_column($items, 'qty')) ?: count($items);
  ?>
  <tr>
    <td><strong><?= e($o['order_code']) ?></strong></td>
    <td><?= e($o['customer_name']) ?><br><span class="hint"><?= e($o['phone']) ?></span></td>
    <td><?= (int)$count ?></td>
    <td><strong><?= naira($o['total']) ?></strong></td>
    <td><?= $o['receipt_image'] ? '🧾 Yes' : '<span class="hint">None</span>' ?></td>
    <td><span class="st <?= status_class($o['status']) ?>"><?= e($o['status']) ?></span></td>
    <td class="hint"><?= e(format_date($o['created_at'])) ?></td>
    <td><a class="btn btn-outline btn-sm" href="order-view.php?id=<?= (int)$o['id'] ?>">View</a></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="8" class="hint">No orders found.</td></tr><?php endif; ?>
</table></div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
