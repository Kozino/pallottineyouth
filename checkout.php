<?php
/**
 * checkout.php — Bank transfer checkout + receipt upload.
 * Flow: fill details → transfer → upload receipt → order saved as "pending"
 * → admin verifies in dashboard → status updated.
 */
require_once __DIR__ . '/config/functions.php';
$data = cart_detailed();
if (!$data['lines']) {
    flash_set('info', 'Your cart is empty. Add some items first.');
    header('Location: shop.php'); exit;
}

$feeLagos   = (float) setting('delivery_fee_lagos', '2000');
$feeOutside = (float) setting('delivery_fee_outside', '3500');
$errors = [];
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) $errors[] = 'Session expired. Please refresh and try again.';
    $old = [
        'customer_name' => trim($_POST['customer_name'] ?? ''),
        'email'         => trim($_POST['email'] ?? ''),
        'phone'         => trim($_POST['phone'] ?? ''),
        'address'       => trim($_POST['address'] ?? ''),
        'city'          => trim($_POST['city'] ?? ''),
        'state'         => trim($_POST['state'] ?? ''),
        'zone'          => $_POST['zone'] ?? 'lagos',
        'sender_name'   => trim($_POST['sender_name'] ?? ''),
        'notes'         => trim($_POST['notes'] ?? ''),
    ];
    if (strlen($old['customer_name']) < 3) $errors[] = 'Please enter your full name.';
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email address.';
    if (strlen(preg_replace('/\D/', '', $old['phone'])) < 7) $errors[] = 'Please enter a valid phone number.';
    if (strlen($old['address']) < 5) $errors[] = 'Please enter your delivery address.';
    if (strlen($old['sender_name']) < 3) $errors[] = 'Please enter the name the transfer was sent from.';
    if (empty($_FILES['receipt']) || ($_FILES['receipt']['error'] ?? 4) === 4) {
        $errors[] = 'Please upload your payment receipt (screenshot or photo).';
    }

    if (!$errors) {
        $receipt = upload_image('receipt', __DIR__ . '/uploads/receipts', 'receipt');
        if (!$receipt) $errors[] = 'Receipt upload failed. Use a JPG/PNG/WEBP image under 5MB.';
    }

    if (!$errors) {
        $fee = ($old['zone'] === 'outside') ? $feeOutside : $feeLagos;
        $subtotal = $data['subtotal'];
        $total = $subtotal + $fee;
        $items = [];
        foreach ($data['lines'] as $line) {
            $items[] = [
                'id' => (int)$line['product']['id'],
                'name' => $line['product']['name'],
                'price' => (float)$line['product']['price'],
                'qty' => (int)$line['qty'],
                'line_total' => (float)$line['line_total'],
            ];
        }
        $code = gen_code('PYN');
        try {
            $stmt = db()->prepare("INSERT INTO orders (order_code, customer_name, email, phone, address, city, state, items_json, subtotal, delivery_fee, total, payment_method, sender_name, receipt_image, notes, status) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?, 'pending')");
            $stmt->execute([$code, $old['customer_name'], $old['email'], $old['phone'], $old['address'], $old['city'], $old['state'], json_encode($items), $subtotal, $fee, $total, 'bank_transfer', $old['sender_name'], $receipt, $old['notes']]);
            // Reduce stock
            foreach ($items as $it) {
                db()->prepare("UPDATE products SET stock = CASE WHEN stock >= ? THEN stock - ? ELSE 0 END WHERE id = ?")
                   ->execute([$it['qty'], $it['qty'], $it['id']]);
            }
            $_SESSION['last_order'] = $code;
            cart_clear();
            header('Location: order-success.php?code=' . urlencode($code));
            exit;
        } catch (Throwable $e) {
            $errors[] = 'Could not save your order. Please try again.';
        }
    }
}

$active = 'cart';
$pageTitle = 'Checkout — Bank Transfer';
$wa = preg_replace('/\D/', '', setting('site_whatsapp', ''));
require_once __DIR__ . '/includes/header.php';
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / <a href="cart.php">Cart</a> / Checkout</div>
    <h1>Checkout — Pay by Bank Transfer 🏦</h1>
    <p>3 simple steps: transfer → upload receipt → we confirm &amp; deliver.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($errors): ?>
      <div class="flash flash-error" style="margin-bottom:1.2rem"><?= implode('<br>', array_map('e', $errors)) ?></div>
    <?php endif; ?>

    <!-- Steps -->
    <div class="grid-3" style="margin-bottom:2rem">
      <div class="feature"><div class="f-ico">1️⃣</div><h3>Transfer <?= naira_short($data['subtotal']) ?> + delivery</h3><p>To the account shown on this page. Keep your receipt/screenshot.</p></div>
      <div class="feature"><div class="f-ico">2️⃣</div><h3>Fill &amp; upload receipt</h3><p>Complete the form below and attach your payment receipt.</p></div>
      <div class="feature"><div class="f-ico">3️⃣</div><h3>We confirm &amp; deliver</h3><p>You get an order code to track. Confirmation within 24 hours.</p></div>
    </div>

    <div class="checkout-layout">
      <!-- Form -->
      <form method="post" action="checkout.php" enctype="multipart/form-data" class="form-card">
        <?= csrf_field() ?>
        <h3>Your Details</h3><br>
        <div class="form-grid">
          <div class="field"><label>Full Name <span class="req">*</span></label><input type="text" name="customer_name" value="<?= e($old['customer_name'] ?? '') ?>" required></div>
          <div class="field"><label>Email <span class="req">*</span></label><input type="email" name="email" value="<?= e($old['email'] ?? '') ?>" required></div>
          <div class="field"><label>Phone / WhatsApp <span class="req">*</span></label><input type="tel" name="phone" value="<?= e($old['phone'] ?? '') ?>" required></div>
          <div class="field"><label>City</label><input type="text" name="city" value="<?= e($old['city'] ?? '') ?>"></div>
          <div class="field full"><label>Delivery Address <span class="req">*</span></label><input type="text" name="address" value="<?= e($old['address'] ?? '') ?>" placeholder="House no, street, area…" required></div>
          <div class="field"><label>State</label><input type="text" name="state" value="<?= e($old['state'] ?? '') ?>" placeholder="e.g. Lagos"></div>
          <div class="field"><label>Delivery Zone <span class="req">*</span></label>
            <select name="zone" id="zoneSelect">
              <option value="lagos" data-fee="<?= (float)$feeLagos ?>" <?= (($old['zone'] ?? 'lagos')==='lagos')?'selected':'' ?>>Lagos — <?= naira($feeLagos) ?></option>
              <option value="outside" data-fee="<?= (float)$feeOutside ?>" <?= (($old['zone'] ?? '')==='outside')?'selected':'' ?>>Outside Lagos — <?= naira($feeOutside) ?></option>
            </select>
          </div>
        </div>
        <h3 style="margin-top:1.5rem">Payment Proof</h3><br>
        <div class="form-grid">
          <div class="field full"><label>Name Transfer Was Sent From <span class="req">*</span></label><input type="text" name="sender_name" value="<?= e($old['sender_name'] ?? '') ?>" placeholder="e.g. CHIDI O. EMEKA" required><span class="hint">The account name that appeared when you transferred.</span></div>
          <div class="field full"><label>Upload Payment Receipt <span class="req">*</span></label><input type="file" id="receipt" name="receipt" accept="image/*" required><span class="hint" id="receiptLabel">Screenshot or photo of transfer receipt (JPG/PNG, max 5MB).</span></div>
          <div class="field full"><label>Order Notes (optional)</label><textarea name="notes" rows="2" placeholder="Size, colour, special instructions…"><?= e($old['notes'] ?? '') ?></textarea></div>
        </div>
        <button class="btn btn-gold btn-block" style="margin-top:1.2rem" type="submit">Submit Order ✓</button>
        <p class="hint" style="margin-top:.6rem;text-align:center">No receipt yet? You can also send it later via <a href="mailto:<?= e(setting('site_email')) ?>">email</a><?= $wa ? ' or <a target="_blank" rel="noopener" href="https://wa.me/'.e($wa).'">WhatsApp</a>' : '' ?> with your order code.</p>
      </form>

      <!-- Summary -->
      <div>
        <div class="bank-box">
          <h3>🏦 Transfer To This Account</h3>
          <div class="bank-row"><span>Bank</span><strong><?= e(setting('bank_name')) ?></strong></div>
          <div class="bank-row"><span>Account Name</span><strong><?= e(setting('bank_account_name')) ?></strong></div>
          <div class="bank-row"><span>Account Number</span><strong style="font-size:1.2rem"><?= e(setting('bank_account_no')) ?></strong></div>
          <button type="button" class="btn btn-light btn-block btn-sm" style="margin-top:.8rem" data-copy="<?= e(setting('bank_account_no')) ?>">Copy Account Number</button>
          <p class="hint" style="color:#c6d0e8;margin-top:.6rem"><?= e(setting('payment_note')) ?></p>
        </div>
        <div class="order-summary" style="margin-top:1.2rem">
          <h3>Order Summary</h3>
          <?php foreach ($data['lines'] as $line): ?>
            <div class="sum-row"><span><?= e($line['product']['name']) ?> × <?= (int)$line['qty'] ?></span><strong><?= naira($line['line_total']) ?></strong></div>
          <?php endforeach; ?>
          <div class="sum-row"><span>Subtotal</span><strong id="subTotal" data-value="<?= (float)$data['subtotal'] ?>"><?= naira($data['subtotal']) ?></strong></div>
          <div class="sum-row"><span>Delivery Fee</span><strong id="deliveryFee"><?= naira($feeLagos) ?></strong></div>
          <div class="sum-row total"><span>Total to Transfer</span><span id="grandTotal"><?= naira($data['subtotal'] + $feeLagos) ?></span></div>
          <p class="hint" style="margin-top:.6rem">💡 The total updates automatically when you change the delivery zone in the form.</p>
        </div>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
