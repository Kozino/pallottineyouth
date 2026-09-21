<?php
$adminActive = 'posts';
require_once __DIR__ . '/../config/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$p = ['title'=>'','slug'=>'','type'=>'news','excerpt'=>'','content'=>'','image'=>'','author'=>(current_admin()['name'] ?? 'Admin'),'status'=>'published'];
if ($id) {
    $stmt = db()->prepare("SELECT * FROM posts WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { flash_set('error','Post not found.'); header('Location: posts.php'); exit; }
    $p = $found;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) $errors[] = 'Session expired. Try again.';
    $p['title'] = trim($_POST['title'] ?? '');
    $p['slug'] = slugify($_POST['slug'] ?? $p['title']);
    $p['type'] = ($_POST['type'] ?? 'news') === 'blog' ? 'blog' : 'news';
    $p['excerpt'] = trim($_POST['excerpt'] ?? '');
    $p['content'] = trim($_POST['content'] ?? '');
    $p['author'] = trim($_POST['author'] ?? 'Admin');
    $p['status'] = ($_POST['status'] ?? 'published') === 'draft' ? 'draft' : 'published';
    if (strlen($p['title']) < 3) $errors[] = 'Title is required.';
    if (strlen($p['content']) < 10) $errors[] = 'Content is too short.';
    $chk = db()->prepare("SELECT id FROM posts WHERE slug=? AND id!=? LIMIT 1");
    $chk->execute([$p['slug'], $id]);
    if ($chk->fetch()) $p['slug'] .= '-' . time();
    if (!$errors && !empty($_FILES['image']['name'])) {
        $img = upload_image('image', __DIR__ . '/../uploads/posts', 'post');
        if (!$img) $errors[] = 'Image upload failed (JPG/PNG/WEBP under 5MB).';
        else {
            if (!empty($p['image'])) @unlink(__DIR__ . '/../uploads/posts/' . $p['image']);
            $p['image'] = $img;
        }
    }
    if (!$errors) {
        if ($id) {
            db()->prepare("UPDATE posts SET title=?, slug=?, type=?, excerpt=?, content=?, image=?, author=?, status=?, published_at=? WHERE id=?")
              ->execute([$p['title'],$p['slug'],$p['type'],$p['excerpt'],$p['content'],$p['image'],$p['author'],$p['status'], $p['status']==='published' ? date('Y-m-d H:i:s') : null, $id]);
            flash_set('success','Post updated.');
        } else {
            db()->prepare("INSERT INTO posts (title, slug, type, excerpt, content, image, author, status, published_at) VALUES (?,?,?,?,?,?,?,?,?)")
              ->execute([$p['title'],$p['slug'],$p['type'],$p['excerpt'],$p['content'],$p['image'],$p['author'],$p['status'], $p['status']==='published' ? date('Y-m-d H:i:s') : null]);
            flash_set('success','Post published.');
        }
        header('Location: posts.php'); exit;
    }
}
$pageTitle = $id ? 'Edit Post' : 'New Post';
require_once __DIR__ . '/includes/admin-header.php';
?>
<?php if ($errors): ?><div class="flash flash-error"><?= implode('<br>', array_map('e',$errors)) ?></div><?php endif; ?>
<div class="card">
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field"><label>Title *</label><input id="f-title" type="text" name="title" value="<?= e($p['title']) ?>" required></div>
    <div class="field"><label>Slug</label><input id="f-slug" type="text" name="slug" value="<?= e($p['slug']) ?>"></div>
    <div class="field"><label>Type</label><select name="type"><option value="news" <?= $p['type']==='news'?'selected':'' ?>>News</option><option value="blog" <?= $p['type']==='blog'?'selected':'' ?>>Blog</option></select></div>
    <div class="field"><label>Status</label><select name="status"><option value="published" <?= $p['status']==='published'?'selected':'' ?>>Published</option><option value="draft" <?= $p['status']==='draft'?'selected':'' ?>>Draft</option></select></div>
    <div class="field"><label>Author</label><input type="text" name="author" value="<?= e($p['author']) ?>"></div>
    <div class="field"><label>Cover Image</label><input type="file" name="image" accept="image/*"><?php if ($p['image']): ?><span class="hint">Current: <?= e($p['image']) ?></span><?php endif; ?></div>
    <div class="field full"><label>Excerpt (short summary)</label><input type="text" name="excerpt" value="<?= e($p['excerpt']) ?>" maxlength="300"></div>
    <div class="field full"><label>Content *</label><textarea name="content" rows="12" placeholder="Write the full story here… (blank line = new paragraph)"><?= e($p['content']) ?></textarea></div>
  </div>
  <div style="display:flex;gap:.7rem;margin-top:1.2rem">
    <button class="btn btn-navy" type="submit"><?= $id?'Update Post':'Publish Post' ?></button>
    <a class="btn btn-outline" href="posts.php">Cancel</a>
  </div>
</form>
</div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
