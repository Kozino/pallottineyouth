<?php
$active = 'news';
require_once __DIR__ . '/config/functions.php';
$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare("SELECT * FROM posts WHERE slug = ? AND status='published' LIMIT 1");
$stmt->execute([$slug]);
$post = $stmt->fetch();
if (!$post) { http_response_code(404); $pageTitle='Not found'; require_once __DIR__.'/includes/header.php'; echo '<section class="section"><div class="container form-card" style="text-align:center"><h2>Story not found</h2><p><a href="news.php">← Back to News</a></p></div></section>'; require_once __DIR__.'/includes/footer.php'; exit; }
$pageTitle = $post['title'];
$pageDesc = excerpt($post['excerpt'] ?: $post['content'] ?? '', 150);
require_once __DIR__ . '/includes/header.php';
$related = db()->prepare("SELECT * FROM posts WHERE status='published' AND id != ? ORDER BY COALESCE(published_at, created_at) DESC LIMIT 3");
$related->execute([$post['id']]);
$related = $related->fetchAll();
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / <a href="news.php">News &amp; Blog</a> / <?= e(ucfirst($post['type'])) ?></div>
    <h1><?= e($post['title']) ?></h1>
    <p>📅 <?= e(format_date($post['published_at'] ?: $post['created_at'])) ?><?= $post['author'] ? ' · ✍️ ' . e($post['author']) : '' ?></p>
  </div>
</section>
<section class="section">
  <div class="container">
    <article class="article">
      <img class="hero-img" src="<?= e(post_image($post['image'])) ?>" alt="<?= e($post['title']) ?>">
      <div class="article-body"><?= nl2br(e($post['content'] ?? '')) ?></div>
      <div class="share-row">
        <a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="https://wa.me/?text=<?= urlencode($post['title']) ?>">Share on WhatsApp</a>
        <a class="btn btn-outline btn-sm" target="_blank" rel="noopener" href="https://www.facebook.com/sharer/sharer.php?u=">Share on Facebook</a>
        <a class="btn btn-outline btn-sm" href="news.php">← All stories</a>
      </div>
    </article>
    <?php if ($related): ?>
    <div style="margin-top:3rem">
      <h2 style="margin-bottom:1.2rem">Read also</h2>
      <div class="grid-3">
        <?php foreach ($related as $r): ?>
        <article class="card">
          <a href="news-single.php?slug=<?= e($r['slug']) ?>"><img src="<?= e(post_image($r['image'])) ?>" alt="<?= e($r['title']) ?>"></a>
          <div class="card-body">
            <span class="pill"><?= e(ucfirst($r['type'])) ?></span>
            <h3><a href="news-single.php?slug=<?= e($r['slug']) ?>"><?= e($r['title']) ?></a></h3>
            <a class="card-link" href="news-single.php?slug=<?= e($r['slug']) ?>">Read more →</a>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
