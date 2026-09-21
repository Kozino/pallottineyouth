<?php
$adminActive = 'settings';
require_once __DIR__ . '/../config/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $keys = ['site_name','tagline','site_email','site_phone','site_whatsapp','site_address','facebook','instagram','twitter','youtube','bank_name','bank_account_name','bank_account_no','bank_branch','payment_note','delivery_fee_lagos','delivery_fee_outside','hero_title','hero_subtitle','announcement','about_home'];
    foreach ($keys as $k) {
        save_setting($k, trim($_POST[$k] ?? ''));
    }
    flash_set('success', 'Settings saved. The website now shows your new details.');
    header('Location: settings.php'); exit;
}
$pageTitle = 'Settings & Bank Details';
require_once __DIR__ . '/includes/admin-header.php';
?>
<form method="post">
<?= csrf_field() ?>
<div class="card">
  <h3>🏦 Bank Details (shown at checkout, shop &amp; donate pages)</h3>
  <div class="form-grid">
    <div class="field"><label>Bank Name</label><input type="text" name="bank_name" value="<?= e(setting('bank_name')) ?>"></div>
    <div class="field"><label>Account Name</label><input type="text" name="bank_account_name" value="<?= e(setting('bank_account_name')) ?>"></div>
    <div class="field"><label>Account Number</label><input type="text" name="bank_account_no" value="<?= e(setting('bank_account_no')) ?>"></div>
    <div class="field"><label>Branch (optional)</label><input type="text" name="bank_branch" value="<?= e(setting('bank_branch')) ?>"></div>
    <div class="field full"><label>Payment Note (instructions for buyers)</label><textarea name="payment_note" rows="2"><?= e(setting('payment_note')) ?></textarea></div>
    <div class="field"><label>Delivery Fee — Lagos (₦)</label><input type="number" step="0.01" name="delivery_fee_lagos" value="<?= e(setting('delivery_fee_lagos')) ?>"></div>
    <div class="field"><label>Delivery Fee — Outside Lagos (₦)</label><input type="number" step="0.01" name="delivery_fee_outside" value="<?= e(setting('delivery_fee_outside')) ?>"></div>
  </div>
</div>

<div class="card">
  <h3>🌐 Site &amp; Contact Details</h3>
  <div class="form-grid">
    <div class="field"><label>Site Name</label><input type="text" name="site_name" value="<?= e(setting('site_name')) ?>"></div>
    <div class="field"><label>Tagline</label><input type="text" name="tagline" value="<?= e(setting('tagline')) ?>"></div>
    <div class="field"><label>Email</label><input type="text" name="site_email" value="<?= e(setting('site_email')) ?>"></div>
    <div class="field"><label>Phone</label><input type="text" name="site_phone" value="<?= e(setting('site_phone')) ?>"></div>
    <div class="field"><label>WhatsApp Number (intl format, no +)</label><input type="text" name="site_whatsapp" value="<?= e(setting('site_whatsapp')) ?>" placeholder="2348012345678"></div>
    <div class="field"><label>Address</label><input type="text" name="site_address" value="<?= e(setting('site_address')) ?>"></div>
    <div class="field"><label>Facebook URL</label><input type="text" name="facebook" value="<?= e(setting('facebook')) ?>"></div>
    <div class="field"><label>Instagram URL</label><input type="text" name="instagram" value="<?= e(setting('instagram')) ?>"></div>
    <div class="field"><label>X (Twitter) URL</label><input type="text" name="twitter" value="<?= e(setting('twitter')) ?>"></div>
    <div class="field"><label>YouTube URL</label><input type="text" name="youtube" value="<?= e(setting('youtube')) ?>"></div>
  </div>
</div>

<div class="card">
  <h3>🏠 Homepage Content</h3>
  <div class="form-grid">
    <div class="field full"><label>Announcement Bar (leave empty to hide)</label><input type="text" name="announcement" value="<?= e(setting('announcement')) ?>"></div>
    <div class="field full"><label>Hero Title</label><input type="text" name="hero_title" value="<?= e(setting('hero_title')) ?>"></div>
    <div class="field full"><label>Hero Subtitle</label><textarea name="hero_subtitle" rows="2"><?= e(setting('hero_subtitle')) ?></textarea></div>
    <div class="field full"><label>About Snippet (homepage)</label><textarea name="about_home" rows="2"><?= e(setting('about_home')) ?></textarea></div>
  </div>
</div>

<button class="btn btn-navy" type="submit">💾 Save All Settings</button>
</form>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
