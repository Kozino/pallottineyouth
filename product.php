<?php
$active = 'shop';
require_once __DIR__ . '/config/functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare("SELECT * FROM products WHERE slug=? AND status='active' LIMIT 1");
$stmt->execute([$slug]);
$p = $stmt->fetch();
if (!$p) { http_response_code(404); $pageTitle='Product not found'; require_once __DIR__.'/includes/header.php'; echo '<section class="section"><div class="container form-card" style="text-align:center"><h2>Product not found</h2><p><a href="shop.php">← Back to Shop</a></p></div></section>'; require_once __DIR__.'/includes/footer.php'; exit; }
$pageTitle = $p['name'];
require_once __DIR__ . '/includes/header.php';
$rel = db()->prepare("SELECT * FROM products WHERE status='active' AND category=? AND id!=? LIMIT 4");
$rel->execute([$p['category'], $p['id']]);
$related = $rel->fetchAll();
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / <a href="shop.php">Shop</a> / <?= e($p['category']) ?></div>
    <h1><?= e($p['name']) ?></h1>
  </div>
</section>
<section class="section">
  <div class="container prod-single">
    <div><img src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>" style="width:100%"></div>
    <div>
      <span class="pill gold"><?= e($p['category']) ?></span>
      <h2 style="margin:.6rem 0"><?= e($p['name']) ?></h2>
      <div class="price-row" style="margin-bottom:.4rem">
        <span class="price" style="font-size:1.6rem"><?= naira($p['price']) ?></span>
        <?php if (!empty($p['old_price']) && (float)$p['old_price'] > (float)$p['price']): ?><span class="old-price"><?= naira($p['old_price']) ?></span><?php endif; ?>
      </div>
      <span class="stock <?= ((int)$p['stock']>0)?'in':'out' ?>"><?= ((int)$p['stock']>0) ? '● In stock ('.(int)$p['stock'].' available)' : '● Out of stock' ?></span>
      <p style="margin:1rem 0"><?= nl2br(e($p['description'] ?: $p['short_desc'] ?? '')) ?></p>
      <div data-unit-price="<?= (float)$p['price'] ?>">
      <form method="post" action="cart.php" class="js-add-cart" style="display:flex;gap:.8rem;align-items:center;flex-wrap:wrap">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
        <div class="qty-ctl"><button type="button" data-act="dec">−</button><input type="number" name="qty" value="1" min="1" max="99"><button type="button" data-act="inc">+</button></div>
        <button class="btn btn-navy" type="submit" <?= ((int)$p['stock']<=0)?'disabled':'' ?>>Add to Cart 🛒</button>
        <a href="checkout.php" class="btn btn-gold">Buy Now →</a>
      </form>
      <div class="live-total">Total: <span data-live-total><?= naira($p['price']) ?></span></div>
      </div>
      <div class="bank-box" style="margin-top:1.4rem">
        <h3>🏦 Pay by Bank Transfer</h3>
        <div class="bank-row"><span>Bank</span><strong><?= e(setting('bank_name')) ?></strong></div>
        <div class="bank-row"><span>Account Name</span><strong><?= e(setting('bank_account_name')) ?></strong></div>
        <div class="bank-row"><span>Account No</span><strong><?= e(setting('bank_account_no')) ?> <button type="button" class="copy-btn" data-copy="<?= e(setting('bank_account_no')) ?>">Copy</button></strong></div>
        <p class="hint" style="color:#c6d0e8;margin-top:.6rem"><?= e(setting('payment_note')) ?></p>
      </div>
    </div>
  </div>
  <?php if ($related): ?>
  <div class="container" style="margin-top:3rem">
    <h2 style="margin-bottom:1.2rem">You may also like</h2>
    <div class="product-grid cols-4">
      <?php foreach ($related as $r): ?>
      <div class="product">
        <a href="product.php?slug=<?= e($r['slug']) ?>"><img src="<?= e(product_image($r['image'])) ?>" alt="<?= e($r['name']) ?>"></a>
        <div class="product-body">
          <h3><a href="product.php?slug=<?= e($r['slug']) ?>"><?= e($r['name']) ?></a></h3>
          <div class="price-row"><span class="price"><?= naira($r['price']) ?></span></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
