<?php
$adminActive = 'products';
$pageTitle = 'Products / Shop Items';
require_once __DIR__ . '/includes/admin-header.php';
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? 'all';
$where = '1=1'; $params = [];
if ($q !== '') { $where .= " AND (name LIKE ? OR category LIKE ?)"; $params[] = "%$q%"; $params[] = "%$q%"; }
if (in_array($status, ['active','hidden'], true)) { $where .= " AND status=?"; $params[] = $status; }
$stmt = db()->prepare("SELECT * FROM products WHERE $where ORDER BY id DESC");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="toolbar">
  <form method="get" action="products.php">
    <input type="text" name="q" placeholder="Search products…" value="<?= e($q) ?>">
    <select name="status">
      <option value="all">All statuses</option>
      <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
      <option value="hidden" <?= $status==='hidden'?'selected':'' ?>>Hidden</option>
    </select>
    <button class="btn btn-navy btn-sm" type="submit">Filter</button>
  </form>
  <a href="product-form.php" class="btn btn-gold btn-sm" style="margin-left:auto">+ Add Product</a>
</div>
<div class="tbl-wrap"><table class="tbl">
  <tr><th></th><th>Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Featured</th><th>Status</th><th></th></tr>
  <?php foreach ($rows as $p): ?>
  <tr>
    <td><img class="thumb" src="../<?= e(product_image($p['image'])) ?>" alt=""></td>
    <td><strong><?= e($p['name']) ?></strong><br><span class="hint"><?= e($p['slug']) ?></span></td>
    <td><?= e($p['category']) ?></td>
    <td><?= naira($p['price']) ?><?php if ($p['old_price']): ?><br><span class="hint"><s><?= naira($p['old_price']) ?></s></span><?php endif; ?></td>
    <td><?= (int)$p['stock'] ?></td>
    <td><?= $p['featured'] ? '⭐ Yes' : '—' ?></td>
    <td><span class="st <?= status_class($p['status']) ?>"><?= e($p['status']) ?></span></td>
    <td><div class="row-actions">
      <a class="btn btn-outline btn-sm" href="product-form.php?id=<?= (int)$p['id'] ?>">Edit</a>
      <a class="btn btn-danger btn-sm" data-confirm="Delete '<?= e($p['name']) ?>'?" href="product-delete.php?id=<?= (int)$p['id'] ?>">Delete</a>
    </div></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="8" class="hint">No products found. <a href="product-form.php">Add the first item →</a></td></tr><?php endif; ?>
</table></div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
