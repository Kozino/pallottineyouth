<?php
$active = 'news';
$pageTitle = 'News & Blog';
require_once __DIR__ . '/includes/header.php';

$type = $_GET['type'] ?? 'all';
$allowed = ['all', 'news', 'blog'];
if (!in_array($type, $allowed, true)) $type = 'all';
$page = max(1, (int)($_GET['page'] ?? 1));
$per = POSTS_PER_PAGE;
$offset = ($page - 1) * $per;

$where = "status='published'" . ($type !== 'all' ? " AND type=" . db()->quote($type) : "");
$total = (int) db()->query("SELECT COUNT(*) c FROM posts WHERE $where")->fetch()['c'];
$pages = max(1, (int) ceil($total / $per));
$posts = db()->query("SELECT * FROM posts WHERE $where ORDER BY COALESCE(published_at, created_at) DESC LIMIT $per OFFSET $offset")->fetchAll();
?>
<section class="page-hero">
  <div class="container">
    <div class="crumbs"><a href="index.php">Home</a> / News &amp; Blog</div>
    <h1>News &amp; Blog</h1>
    <p>Stories, announcements and faith reflections from the family.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="pager" style="justify-content:flex-start;margin:0 0 1.6rem">
      <a href="news.php?type=all" class="<?= $type==='all'?'on':'' ?>">All</a>
      <a href="news.php?type=news" class="<?= $type==='news'?'on':'' ?>">News</a>
      <a href="news.php?type=blog" class="<?= $type==='blog'?'on':'' ?>">Blog</a>
    </div>
    <?php if ($posts): ?>
    <div class="grid-3">
      <?php foreach ($posts as $post): ?>
      <article class="card">
        <a href="news-single.php?slug=<?= e($post['slug']) ?>"><img src="<?= e(post_image($post['image'])) ?>" alt="<?= e($post['title']) ?>"></a>
        <div class="card-body">
          <span class="pill <?= $post['type']==='news'?'gold':'green' ?>"><?= e(ucfirst($post['type'])) ?></span>
          <h3><a href="news-single.php?slug=<?= e($post['slug']) ?>"><?= e($post['title']) ?></a></h3>
          <div class="card-meta">
            <span>📅 <?= e(format_date($post['published_at'] ?: $post['created_at'])) ?></span>
            <?php if ($post['author']): ?><span>✍️ <?= e($post['author']) ?></span><?php endif; ?>
          </div>
          <p class="hint"><?= e(excerpt($post['excerpt'] ?: $post['content'] ?? '', 120)) ?></p>
          <a class="card-link" href="news-single.php?slug=<?= e($post['slug']) ?>">Read more →</a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?>
    <div class="pager">
      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <a href="news.php?type=<?= e($type) ?>&page=<?= $i ?>" class="<?= $i===$page?'on':'' ?>"><?= $i ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php else: ?>
      <div class="form-card" style="text-align:center"><p>No stories here yet. Please check back soon.</p></div>
    <?php endif; ?>
  </div>
</section>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
