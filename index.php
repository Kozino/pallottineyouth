<?php
$active = 'home';
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';

// Latest posts
$latestPosts = db()->query("SELECT * FROM posts WHERE status='published' ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3")->fetchAll();
// Featured products
$featured = db()->query("SELECT * FROM products WHERE status='active' AND featured=1 ORDER BY id DESC LIMIT 4")->fetchAll();
if (!$featured) {
    $featured = db()->query("SELECT * FROM products WHERE status='active' ORDER BY id DESC LIMIT 4")->fetchAll();
}
// Upcoming events
$events = db()->query("SELECT * FROM events WHERE status='upcoming' ORDER BY event_date ASC LIMIT 3")->fetchAll();
?>

<!-- ================= HERO ================= -->
<section class="hero">
  <div class="container hero-inner">
    <div>
      <span class="hero-eyebrow">✦ St. Vincent Pallotti · Nigeria</span>
      <h1><?= e(setting('hero_title')) ?></h1>
      <p class="lead"><?= e(setting('hero_subtitle')) ?></p>
      <div class="hero-cta">
        <a href="apply.php" class="btn btn-gold">Become a Member</a>
        <a href="shop.php" class="btn btn-light">Visit Our Shop</a>
      </div>
      <div class="hero-stats">
        <div><strong>36+</strong><span>States &amp; Dioceses</span></div>
        <div><strong>100%</strong><span>Catholic &amp; Apostolic</span></div>
        <div><strong>∞</strong><span>Love of Christ</span></div>
      </div>
    </div>
    <div class="hero-card">
      <span class="pill gold">Shop with ease</span>
      <h3>Pay by Bank Transfer</h3>
      <p class="hint">No online card stress — transfer to our account, upload your receipt, and we confirm your order.</p>
      <div class="bank-box">
        <h3>🏦 <?= e(setting('bank_name')) ?></h3>
        <div class="bank-row"><span>Account Name</span><strong><?= e(setting('bank_account_name')) ?></strong></div>
        <div class="bank-row"><span>Account No</span><strong><?= e(setting('bank_account_no')) ?></strong></div>
      </div>
      <div class="hero-card-actions">
        <a href="shop.php" class="btn btn-navy btn-sm">Shop Now</a>
        <a href="track-order.php" class="btn btn-outline btn-sm">Track Order</a>
      </div>
    </div>
  </div>
</section>

<div class="verse-strip">“The love of Christ impels us.” — 2 Corinthians 5:14 · Motto of St. Vincent Pallotti</div>

<!-- ================= ABOUT TEASER ================= -->
<section class="section">
  <div class="container split">
    <div>
      <img src="assets/images/placeholder-post.svg" alt="Pallottine Nigerian Youth fellowship">
    </div>
    <div>
      <span class="sec-kicker">Who we are</span>
      <h2>Young Apostles, Formed for Mission</h2>
      <p><?= e(setting('about_home')) ?></p>
      <ul class="check-list">
        <li><strong>Faith formation</strong> — retreats, Bible study, catechesis &amp; novenas</li>
        <li><strong>Charity in action</strong> — prison, hospital &amp; rural outreaches</li>
        <li><strong>Youth fellowship</strong> — festivals, sports, music &amp; campus cells</li>
        <li><strong>Leadership</strong> — training young leaders for Church &amp; society</li>
      </ul>
      <a href="about.php" class="btn btn-navy">Learn More About Us</a>
    </div>
  </div>
</section>

<!-- ================= APOSTOLATES ================= -->
<section class="section section-cream">
  <div class="container">
    <div class="sec-head center">
      <span class="sec-kicker">Our apostolates</span>
      <h2>What We Do</h2>
      <p>Four pillars of the Pallottine charism, lived out by young people across Nigeria.</p>
    </div>
    <div class="grid-4">
      <div class="feature"><div class="f-ico">🙏</div><h3>Prayer &amp; Worship</h3><p>Masses, adoration, rosary crusades and annual retreats that keep Christ at the centre.</p></div>
      <div class="feature"><div class="f-ico">📖</div><h3>Faith Formation</h3><p>Bible study, catechism classes, apologetics and leadership courses for youths.</p></div>
      <div class="feature"><div class="f-ico">🤝</div><h3>Charity &amp; Outreach</h3><p>Visits to orphanages, hospitals and prisons; food drives and rural evangelisation.</p></div>
      <div class="feature"><div class="f-ico">🎉</div><h3>Fellowship &amp; Culture</h3><p>Youth festivals, concerts, sports, drama and media evangelisation.</p></div>
    </div>
    <div style="text-align:center;margin-top:2rem"><a href="programs.php" class="btn btn-outline">Explore All Apostolates</a></div>
  </div>
</section>

<!-- ================= SHOP ================= -->
<section class="section">
  <div class="container">
    <div class="sec-head center">
      <span class="sec-kicker">Support the mission</span>
      <h2>Shop — Wear &amp; Share the Faith</h2>
      <p>Branded shirts, devotionals, books and more. Every purchase supports youth apostolate.</p>
    </div>
    <div class="product-grid cols-4">
      <?php foreach ($featured as $p): ?>
      <div class="product">
        <a href="product.php?slug=<?= e($p['slug']) ?>"><img src="<?= e(product_image($p['image'])) ?>" alt="<?= e($p['name']) ?>"></a>
        <div class="product-body">
          <span class="pill"><?= e($p['category']) ?></span>
          <h3><a href="product.php?slug=<?= e($p['slug']) ?>"><?= e($p['name']) ?></a></h3>
          <div class="price-row"><span class="price"><?= naira($p['price']) ?></span>
            <?php if (!empty($p['old_price'])): ?><span class="old-price"><?= naira($p['old_price']) ?></span><?php endif; ?>
          </div>
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
    <div style="text-align:center;margin-top:2rem"><a href="shop.php" class="btn btn-gold">View All Items →</a></div>
  </div>
</section>

<!-- ================= NEWS ================= -->
<section class="section section-cream">
  <div class="container">
    <div class="sec-head center">
      <span class="sec-kicker">Stay updated</span>
      <h2>Latest News &amp; Reflections</h2>
    </div>
    <div class="grid-3">
      <?php foreach ($latestPosts as $post): ?>
      <article class="card">
        <a href="news-single.php?slug=<?= e($post['slug']) ?>"><img src="<?= e(post_image($post['image'])) ?>" alt="<?= e($post['title']) ?>"></a>
        <div class="card-body">
          <span class="pill <?= $post['type']==='news'?'gold':'green' ?>"><?= e(ucfirst($post['type'])) ?></span>
          <h3><a href="news-single.php?slug=<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
          <div class="card-meta"><span>📅 <?= e(format_date($post['published_at'] ?: $post['created_at'])) ?></span></div>
          <p class="hint"><?= e(excerpt($post['excerpt'] ?: $post['content'] ?? '', 110)) ?></p>
          <a class="card-link" href="news-single.php?slug=<?= e($post['slug']) ?>">Read more →</a>
        </div>
      </article>
      <?php endforeach; ?>
      <?php if (!$latestPosts): ?><p>No posts yet. Check back soon.</p><?php endif; ?>
    </div>
  </div>
</section>

<!-- ================= EVENTS ================= -->
<?php if ($events): ?>
<section class="section">
  <div class="container">
    <div class="sec-head center">
      <span class="sec-kicker">Gather with us</span>
      <h2>Upcoming Events</h2>
    </div>
    <div class="grid-2">
      <?php foreach ($events as $ev): ?>
      <div class="event-card">
        <div class="event-date"><strong><?= e(format_date($ev['event_date'],'d')) ?></strong><span><?= e(format_date($ev['event_date'],'M Y')) ?></span></div>
        <div>
          <h3><?= e($ev['title']) ?></h3>
          <p class="hint">📍 <?= e($ev['venue'] ?: 'TBA') ?> · 🕘 <?= e($ev['event_time'] ?: '') ?></p>
          <p class="hint"><?= e(excerpt($ev['description'] ?? '', 100)) ?></p>
        </div>
        <div><a href="events.php" class="btn btn-outline btn-sm">Details</a></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ================= CTA ================= -->
<section class="section" style="padding-top:0">
  <div class="container">
    <div class="cta-band">
      <div>
        <h2>Ready to become a young apostle?</h2>
        <p>Fill our membership application in under 5 minutes. Our coordinators will reach out to you.</p>
      </div>
      <div><a href="apply.php" class="btn btn-navy">Apply Now →</a></div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
