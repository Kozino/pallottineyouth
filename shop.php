<?php
$active = 'shop';
$pageTitle = 'Shop';
require_once __DIR__ . '/includes/header.php';

$cat = trim($_GET['cat'] ?? 'all');
$cats = db()->query("SELECT DISTINCT category FROM products WHERE status='active' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$page = max(1, (int)($_GET['page'] ?? 1));
$per = PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $per;

if ($cat !== 'all' && $cat !== '') {
    $total = (int) db()->prepare("SELECT COUNT(*) c FROM products WHERE status='active' AND category=?")->execute([$cat]) ?? 0;
    $stmt = db()->prepare("SELECT COUNT(*) c FROM products WHERE status='active' AND category=?");
    $stmt->execute([$cat]);
    $total = (int) $stmt->fetch()['c'];
    $stmt = db()->prepare("SELECT * FROM products WHERE status='active' AND category=? ORDER BY featured DESC, id DESC LIMIT $per OFFSET $offset");
    $stmt->execute([$cat]);
    $products = $stmt->fetchAll();
} else {
    $total = (int) db()->query("SELECT COUNT(*) c FROM products WHERE status='active'")->fetch()['c'];
    $products = db()->query("SELECT * FROM products WHERE status='active' ORDER BY featured DESC, id DESC LIMIT $per OFFSET $offset")->fetchAll();
}
$pages = max(1, (int) ceil($total / $per));
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / Shop</div>
    <h1>Our Shop 🛍️</h1>
    <p>Pay by <strong>bank transfer</strong> — then upload your receipt at checkout. Simple and safe.</p>
  </div>
</section>

<section class="section">
  <div class="container shop-layout">
    <aside class="shop-side">
      <h4>Categories</h4>
      <a href="shop.php?cat=all" class="<?= ($cat==='all'||$cat==='')?'on':'' ?>">All Items (<?= (int) db()->query("SELECT COUNT(*) c FROM products WHERE status='active'")->fetch()['c'] ?>)</a>
      <?php foreach ($cats as $c): ?>
        <a href="shop.php?cat=<?= urlencode($c) ?>" class="<?= $cat===$c?'on':'' ?>"><?= e($c) ?></a>
      <?php endforeach; ?>
      <h4 style="margin-top:1.4rem">How it works</h4>
      <p class="hint">1️⃣ Add items to cart<br>2️⃣ Checkout &amp; transfer to our bank<br>3️⃣ Upload receipt<br>4️⃣ We confirm &amp; deliver 🎉</p>
      <a href="track-order.php" class="btn btn-outline btn-sm btn-block" style="margin-top:.8rem">Track Order</a>
    </aside>

    <div>
      <?php if ($products): ?>
      <div class="product-grid">
        <?php foreach ($products as $p): ?>
        <div class="product">
          <a href="product.php?slug=<?= e($p['slug']) ?>"><img src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>"></a>
          <div class="product-body">
            <span class="pill"><?= e($p['category']) ?></span>
            <h3><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
            <div class="price-row"><span class="price"><?= naira($p['price']) ?></span>
              <?php if (!empty($p['old_price']) && (float)$p['old_price'] > (float)$p['price']): ?><span class="old-price"><?= naira($p['old_price']) ?></span><?php endif; ?>
            </div>
            <span class="stock <?= ((int)$p['stock']>0)?'in':'out' ?>"><?= ((int)$p['stock']>0) ? '● In stock ('.(int)$p['stock'].')' : '● Out of stock' ?></span>
            <div class="product-actions">
              <form method="post" action="cart.php" class="js-add-cart" style="flex:1;display:flex">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                <button class="btn btn-navy" style="flex:1" type="submit" <?= ((int)$p['stock']<=0)?'disabled':'' ?>>Add to Cart</button>
              </form>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php if ($pages > 1): ?>
      <div class="pager">
        <?php for ($i = 1; $i <= $pages; $i++): ?>
          <a href="shop.php?cat=<?= urlencode($cat) ?>&page=<?= $i ?>" class="<?= $i===$page?'on':'' ?>"><?= $i ?></a>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
      <?php else: ?>
        <div class="form-card" style="text-align:center"><h3>Nothing here yet</h3><p class="hint">Items will appear here once the admin adds them.</p></div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
