<?php
$adminActive = 'posts';
$pageTitle = 'News & Blog Posts';
require_once __DIR__ . '/includes/admin-header.php';
$type = $_GET['type'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$where = '1=1'; $params = [];
if (in_array($type, ['news','blog'], true)) { $where .= " AND type=?"; $params[] = $type; }
if ($q !== '') { $where .= " AND title LIKE ?"; $params[] = "%$q%"; }
$stmt = db()->prepare("SELECT * FROM posts WHERE $where ORDER BY id DESC LIMIT 200");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="toolbar">
  <form method="get" action="posts.php">
    <input type="text" name="q" placeholder="Search posts…" value="<?= e($q) ?>">
    <select name="type">
      <option value="all">News + Blog</option>
      <option value="news" <?= $type==='news'?'selected':'' ?>>News</option>
      <option value="blog" <?= $type==='blog'?'selected':'' ?>>Blog</option>
    </select>
    <button class="btn btn-navy btn-sm" type="submit">Filter</button>
  </form>
  <a href="post-form.php" class="btn btn-gold btn-sm" style="margin-left:auto">+ New Post</a>
</div>
<div class="tbl-wrap"><table class="tbl">
  <tr><th></th><th>Title</th><th>Type</th><th>Status</th><th>Date</th><th></th></tr>
  <?php foreach ($rows as $p): ?>
  <tr>
    <td><img class="thumb" src="../<?= e(post_image($p['image'])) ?>" alt=""></td>
    <td><strong><?= e($p['title']) ?></strong><br><span class="hint"><?= e(excerpt($p['excerpt'] ?: '', 70)) ?></span></td>
    <td><span class="st st-info"><?= e(ucfirst($p['type'])) ?></span></td>
    <td><span class="st <?= status_class($p['status']) ?>"><?= e($p['status']) ?></span></td>
    <td class="hint"><?= e(format_date($p['published_at'] ?: $p['created_at'])) ?></td>
    <td><div class="row-actions">
      <a class="btn btn-outline btn-sm" href="post-form.php?id=<?= (int)$p['id'] ?>">Edit</a>
      <a class="btn btn-outline btn-sm" target="_blank" href="../news-single.php?slug=<?= e($p['slug']) ?>">View</a>
      <a class="btn btn-danger btn-sm" data-confirm="Delete this post?" href="post-delete.php?id=<?= (int)$p['id'] ?>">Delete</a>
    </div></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="6" class="hint">No posts yet. <a href="post-form.php">Write the first one →</a></td></tr><?php endif; ?>
</table></div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
