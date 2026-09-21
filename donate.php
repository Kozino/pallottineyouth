<?php
$active = 'donate';
$pageTitle = 'Donate — Support the Mission';
require_once __DIR__ . '/includes/header.php';
$wa = preg_replace('/\D/', '', setting('site_whatsapp', ''));
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / Donate</div>
    <h1>Support the Mission 💛</h1>
    <p>“God loves a cheerful giver.” — 2 Cor 9:7. Your seed keeps the youth apostolate alive.</p>
  </div>
</section>
<section class="section">
  <div class="container grid-2">
    <div>
      <h2>Your Giving Fuels…</h2><br>
      <ul class="check-list">
        <li>Youth retreats, festivals &amp; faith formation</li>
        <li>Prison, hospital &amp; rural charity outreaches</li>
        <li>Support for indigent students &amp; seminarians</li>
        <li>Media evangelisation &amp; campus fellowships</li>
      </ul>
      <div class="form-card" style="margin-top:1.2rem;background:var(--cream)">
        <h3>After You Give</h3>
        <p class="hint">Please send your receipt/evidence of transfer with your name via:</p>
        <div class="share-row">
          <?php if ($wa): ?><a class="btn btn-green btn-sm" target="_blank" rel="noopener" href="https://wa.me/<?= e($wa) ?>?text=<?= urlencode('Hello! I just made a donation. Find my receipt attached. Name: ') ?>">WhatsApp Receipt</a><?php endif; ?>
          <a class="btn btn-outline btn-sm" href="mailto:<?= e(setting('site_email')) ?>?subject=Donation%20Receipt">Email Receipt</a>
          <a class="btn btn-outline btn-sm" href="contact.php">Contact Form</a>
        </div>
      </div>
    </div>
    <div class="bank-box" style="align-self:start">
      <h3>🏦 Donate via Bank Transfer</h3>
      <div class="bank-row"><span>Bank</span><strong><?= e(setting('bank_name')) ?></strong></div>
      <div class="bank-row"><span>Account Name</span><strong><?= e(setting('bank_account_name')) ?></strong></div>
      <div class="bank-row"><span>Account Number</span><strong style="font-size:1.3rem"><?= e(setting('bank_account_no')) ?></strong></div>
      <?php if (setting('bank_branch')): ?><div class="bank-row"><span>Branch</span><strong><?= e(setting('bank_branch')) ?></strong></div><?php endif; ?>
      <button type="button" class="btn btn-light btn-block" style="margin-top:.8rem" data-copy="<?= e(setting('bank_account_no')) ?>">Copy Account Number</button>
      <p class="hint" style="color:#c6d0e8;margin-top:.6rem">🙏 May God bless you abundantly for supporting young apostles.</p>
    </div>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
