<?php
require_once __DIR__ . '/config/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) {
        flash_set('error', 'Session expired. Please try again.');
    } else {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? 'Website message');
        $message = trim($_POST['message'] ?? '');
        $source = ($_POST['source'] ?? 'contact') === 'livechat' ? 'livechat' : 'contact';
        if (strlen($name) < 2 || strlen($message) < 3) {
            flash_set('error', 'Please enter your name and a message.');
        } else {
            $stmt = db()->prepare("INSERT INTO messages (name, email, phone, subject, message, source, status) VALUES (?,?,?,?,?,?, 'unread')");
            $stmt->execute([$name, $email, $phone, $subject, $message, $source]);
            flash_set('success', 'Message sent! We will get back to you soon. God bless you. 🙏');
        }
    }
    header('Location: contact.php'); exit;
}
$active = 'contact';
$pageTitle = 'Contact Us';
require_once __DIR__ . '/includes/header.php';
$wa = preg_replace('/\D/', '', setting('site_whatsapp', ''));
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / Contact</div>
    <h1>Contact Us 💬</h1>
    <p>Questions, payment receipts, partnerships — we look forward to hearing from you.</p>
  </div>
</section>
<section class="section">
  <div class="container grid-2">
    <form method="post" action="contact.php" class="form-card">
      <?= csrf_field() ?>
      <h3>Send a Message</h3><br>
      <div class="form-grid">
        <div class="field"><label>Your Name <span class="req">*</span></label><input type="text" name="name" required></div>
        <div class="field"><label>Email</label><input type="email" name="email"></div>
        <div class="field"><label>Phone / WhatsApp</label><input type="tel" name="phone"></div>
        <div class="field"><label>Subject</label>
          <select name="subject">
            <option>General Enquiry</option>
            <option>Payment / Order Issue</option>
            <option>Membership Application</option>
            <option>Partnership / Donation</option>
            <option>Media / Press</option>
          </select></div>
        <div class="field full"><label>Message <span class="req">*</span></label><textarea name="message" rows="5" required placeholder="Type here… (for payments, include your ORDER CODE)"></textarea></div>
      </div>
      <button class="btn btn-navy btn-block" style="margin-top:1rem" type="submit">Send Message ✓</button>
    </form>
    <div>
      <div class="form-card">
        <h3>Reach Us Directly</h3><br>
        <p>📧 <strong><?= e(setting('site_email')) ?></strong></p>
        <p>📞 <strong><?= e(setting('site_phone')) ?></strong></p>
        <p>📍 <?= e(setting('site_address')) ?></p>
        <div class="share-row">
          <?php if ($wa): ?><a class="btn btn-green btn-sm" target="_blank" rel="noopener" href="https://wa.me/<?= e($wa) ?>">WhatsApp Us</a><?php endif; ?>
          <a class="btn btn-outline btn-sm" href="mailto:<?= e(setting('site_email')) ?>?subject=Payment%20Receipt">Email Receipt</a>
        </div>
      </div>
      <div class="bank-box" style="margin-top:1.2rem">
        <h3>🏦 Our Bank Details</h3>
        <div class="bank-row"><span>Bank</span><strong><?= e(setting('bank_name')) ?></strong></div>
        <div class="bank-row"><span>Account Name</span><strong><?= e(setting('bank_account_name')) ?></strong></div>
        <div class="bank-row"><span>Account No</span><strong style="font-size:1.15rem"><?= e(setting('bank_account_no')) ?></strong></div>
        <button type="button" class="btn btn-light btn-block btn-sm" style="margin-top:.8rem" data-copy="<?= e(setting('bank_account_no')) ?>">Copy Account Number</button>
      </div>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
