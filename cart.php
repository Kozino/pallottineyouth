<?php
/**
 * cart.php — view + add/update/remove.
 * - Normal POST (no JS): classic redirect flow.
 * - AJAX (ajax=1): JSON responses, page never reloads.
 * - GET ?mini=1: JSON summary for the mini-cart drawer.
 */
require_once __DIR__ . '/config/functions.php';

function cart_json_payload(): array {
    $data = cart_detailed();
    $lines = [];
    foreach ($data['lines'] as $line) {
        $lines[] = [
            'id'         => (int)$line['product']['id'],
            'name'       => $line['product']['name'],
            'slug'       => $line['product']['slug'],
            'image'      => product_image($line['product']['image']),
            'price'      => (float)$line['product']['price'],
            'qty'        => (int)$line['qty'],
            'line_total' => (float)$line['line_total'],
        ];
    }
    return ['count' => cart_count(), 'subtotal' => (float)$data['subtotal'], 'lines' => $lines];
}
function cart_json(array $payload): void {
    header('Content-Type: application/json');
    echo json_encode($payload);
    exit;
}

// --- Mini-cart summary (GET) ---
if (isset($_GET['mini'])) {
    cart_json(['ok' => true] + cart_json_payload());
}
// --- Remove / clear via link ---
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][(int)$_GET['remove']]);
    flash_set('success', 'Item removed.');
    header('Location: cart.php'); exit;
}
if (isset($_GET['clear'])) {
    cart_clear();
    flash_set('success', 'Cart cleared.');
    header('Location: cart.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isAjax = isset($_POST['ajax']) || (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'XMLHttpRequest');
    $action = $_POST['action'] ?? 'add';
    if (!csrf_check()) {
        if ($isAjax) cart_json(['ok' => false, 'message' => 'Session expired. Please refresh the page.']);
        flash_set('error', 'Session expired. Please try again.');
        header('Location: shop.php'); exit;
    }
    if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

    if ($action === 'add') {
        $id = (int)($_POST['id'] ?? 0);
        $qty = max(1, min(99, (int)($_POST['qty'] ?? 1)));
        $stmt = db()->prepare("SELECT id, name, stock FROM products WHERE id=? AND status='active'");
        $stmt->execute([$id]);
        $prod = $stmt->fetch();
        if (!$prod) {
            if ($isAjax) cart_json(['ok' => false, 'message' => 'Product not available.']);
            flash_set('error', 'Product not available.');
        } else {
            $current = (int)($_SESSION['cart'][$id] ?? 0);
            if ($current + $qty > (int)$prod['stock']) {
                $msg = 'Only ' . (int)$prod['stock'] . ' pc(s) of "' . $prod['name'] . '" in stock.';
                if ($isAjax) cart_json(['ok' => false, 'message' => $msg] + cart_json_payload());
                flash_set('error', $msg);
            } else {
                $_SESSION['cart'][$id] = $current + $qty;
                if ($isAjax) cart_json(['ok' => true, 'message' => '"' . $prod['name'] . '" added to cart ✓'] + cart_json_payload());
                flash_set('success', '"' . $prod['name'] . '" added to cart.');
            }
        }
        header('Location: cart.php'); exit;
    }

    if ($action === 'update') {
        foreach (($_POST['qty'] ?? []) as $id => $qty) {
            $id = (int)$id; $qty = (int)$qty;
            if ($qty <= 0) unset($_SESSION['cart'][$id]);
            else $_SESSION['cart'][$id] = min(99, $qty);
        }
        if ($isAjax) cart_json(['ok' => true, 'message' => 'Cart updated ✓'] + cart_json_payload());
        flash_set('success', 'Cart updated.');
        header('Location: cart.php'); exit;
    }

    if ($action === 'remove') {
        unset($_SESSION['cart'][(int)($_POST['id'] ?? 0)]);
        if ($isAjax) cart_json(['ok' => true, 'message' => 'Item removed.'] + cart_json_payload());
        flash_set('success', 'Item removed.');
        header('Location: cart.php'); exit;
    }
}

$active = 'cart';
$pageTitle = 'Your Cart';
require_once __DIR__ . '/includes/header.php';
$data = cart_detailed();
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / Cart</div>
    <h1>Your Cart 🛒</h1>
  </div>
</section>
<section class="section">
  <div class="container">
    <?php if (!$data['lines']): ?>
      <div class="form-card" style="text-align:center">
        <div style="font-size:3rem">🛒</div>
        <h2>Your cart is empty</h2>
        <p class="hint">Browse the shop and add branded items that support the mission.</p>
        <a href="shop.php" class="btn btn-gold" style="margin-top:1rem">Go to Shop →</a>
      </div>
    <?php else: ?>
      <form method="post" action="cart.php" id="cartForm">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="update">
        <div style="overflow-x:auto">
        <table class="cart-table">
          <thead><tr><th>Item</th><th>Price</th><th>Qty</th><th>Total</th><th></th></tr></thead>
          <tbody>
          <?php foreach ($data['lines'] as $line): $p = $line['product']; ?>
            <tr data-cart-row data-price="<?= (float)$p['price'] ?>" data-id="<?= (int)$p['id'] ?>">
              <td><div style="display:flex;gap:.8rem;align-items:center">
                <img class="cart-thumb" src="<?= e(product_image($p['image'])) ?>" alt="">
                <div><strong><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></strong><br><span class="hint"><?= e($p['category']) ?></span></div>
              </div></td>
              <td><?= naira($p['price']) ?></td>
              <td>
                <div class="qty-ctl">
                  <button type="button" data-act="dec" aria-label="Decrease">−</button>
                  <input type="number" name="qty[<?= (int)$p['id'] ?>]" value="<?= (int)$line['qty'] ?>" min="0" max="99" data-cart-qty aria-label="Quantity">
                  <button type="button" data-act="inc" aria-label="Increase">+</button>
                </div>
              </td>
              <td><strong data-line-total><?= naira($line['line_total']) ?></strong></td>
              <td><a class="btn btn-outline btn-sm" href="cart.php?remove=<?= (int)$p['id'] ?>" onclick="return confirm('Remove this item?')">✕</a></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        </div>
        <div style="display:flex;gap:1rem;justify-content:space-between;align-items:center;margin-top:1.2rem;flex-wrap:wrap">
          <div style="display:flex;gap:.6rem;flex-wrap:wrap">
            <a href="shop.php" class="btn btn-outline btn-sm">← Continue Shopping</a>
            <a href="cart.php?clear=1" class="btn btn-outline btn-sm" onclick="return confirm('Clear the whole cart?')">Clear Cart</a>
          </div>
          <div style="text-align:right">
            <div style="font-size:1.2rem">Subtotal: <strong id="cartSubtotal" data-value="<?= (float)$data['subtotal'] ?>"><?= naira($data['subtotal']) ?></strong></div>
            <div class="hint">Quantities save automatically. Delivery fee is added at checkout.</div>
            <a href="checkout.php" class="btn btn-gold" style="margin-top:.6rem">Proceed to Checkout →</a>
          </div>
        </div>
      </form>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
