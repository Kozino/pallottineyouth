<?php
/** Frontend footer */
$wa = preg_replace('/\D/', '', setting('site_whatsapp', ''));
?>
</main>

<!-- Mini cart drawer -->
<div class="mini-overlay" id="miniOverlay"></div>
<aside class="mini-cart" id="miniCart" aria-label="Shopping cart preview">
  <div class="mini-head"><strong>🛒 Added to Cart</strong><button id="miniClose" aria-label="Close">×</button></div>
  <div class="mini-items" id="miniItems"></div>
  <div class="mini-foot">
    <div class="mini-sub"><span>Subtotal</span><span id="miniSub">₦0.00</span></div>
    <div class="row">
      <a href="cart.php" class="btn btn-outline btn-sm">View Cart</a>
      <a href="checkout.php" class="btn btn-gold btn-sm">Checkout →</a>
    </div>
  </div>
</aside>

<!-- Live chat / WhatsApp float -->
<div class="chat-float">
  <button class="chat-btn" id="chatBtn" aria-label="Chat with us">💬</button>
  <div class="chat-panel" id="chatPanel" hidden>
    <div class="chat-head">
      <strong>Chat with us</strong>
      <span>We reply within a day</span>
      <button id="chatClose" aria-label="Close">×</button>
    </div>
    <div class="chat-body">
      <p class="chat-hint">Send your payment receipt, order code or question here — or reach us instantly:</p>
      <div class="chat-links">
        <?php if ($wa): ?>
        <a class="btn btn-green btn-block" target="_blank" rel="noopener" href="https://wa.me/<?= e($wa) ?>?text=Hello%20Pallottine%20Nigerian%20Youth!">Chat on WhatsApp</a>
        <?php endif; ?>
        <a class="btn btn-outline btn-block" href="mailto:<?= e(setting('site_email')) ?>?subject=Payment%20Receipt%20/%20Enquiry">Send receipt by Email</a>
      </div>
      <form method="post" action="contact.php" class="chat-form">
        <?= csrf_field() ?>
        <input type="hidden" name="source" value="livechat">
        <input type="text" name="name" placeholder="Your name" required>
        <input type="text" name="phone" placeholder="Phone / WhatsApp" required>
        <textarea name="message" rows="3" placeholder="Type your message (e.g. ORDER CODE + payment details)…" required></textarea>
        <button class="btn btn-navy btn-block" type="submit">Send message</button>
      </form>
    </div>
  </div>
</div>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="f-col f-about">
      <div class="f-brand">
        <img src="assets/images/logo.png" alt="Pallottine Nigerian Youth logo">
        <div><strong><?= e(setting('site_name')) ?></strong><small><?= e(setting('tagline')) ?></small></div>
      </div>
      <p>Helping young Catholics in Nigeria understand, live and share their faith — in the spirit of St. Vincent Pallotti: <em>“The love of Christ impels us.”</em></p>
      <div class="socials">
        <a href="<?= e(setting('facebook')) ?>" target="_blank" rel="noopener" aria-label="Facebook">f</a>
        <a href="<?= e(setting('instagram')) ?>" target="_blank" rel="noopener" aria-label="Instagram">ig</a>
        <a href="<?= e(setting('twitter')) ?>" target="_blank" rel="noopener" aria-label="X">𝕏</a>
        <a href="<?= e(setting('youtube')) ?>" target="_blank" rel="noopener" aria-label="YouTube">▶</a>
      </div>
    </div>
    <div class="f-col">
      <h4>Explore</h4>
      <a href="about.php">About Us</a>
      <a href="programs.php">Our Apostolates</a>
      <a href="news.php">News &amp; Blog</a>
      <a href="events.php">Events</a>
      <a href="shop.php">Shop</a>
      <a href="donate.php">Donate</a>
    </div>
    <div class="f-col">
      <h4>Get Involved</h4>
      <a href="apply.php">Apply / Become a Member</a>
      <a href="track-order.php">Track Your Order</a>
      <a href="contact.php">Contact Us</a>
      <a href="donate.php">Support Our Mission</a>
    </div>
    <div class="f-col">
      <h4>Contact</h4>
      <p>📧 <?= e(setting('site_email')) ?><br>
         📞 <?= e(setting('site_phone')) ?><br>
         📍 <?= e(setting('site_address')) ?></p>
      <h4 class="f-pay">Pay by Bank Transfer</h4>
      <p class="bank-mini">
        <strong><?= e(setting('bank_name')) ?></strong><br>
        <?= e(setting('bank_account_name')) ?><br>
        <span class="acct"><?= e(setting('bank_account_no')) ?></span>
      </p>
    </div>
  </div>
  <div class="footer-bottom">
    <div class="container footer-bottom-inner">
      <span>© <?= date('Y') ?> <?= e(setting('site_name')) ?> · pallottineyouthnig.com · All rights reserved.</span>
      <span class="f-links"><a href="about.php">About</a> · <a href="contact.php">Contact</a> · <a href="admin/index.php">Admin</a></span>
    </div>
  </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html>
