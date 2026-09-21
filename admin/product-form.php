<?php
$adminActive = 'products';
require_once __DIR__ . '/../config/functions.php';
require_admin();

$id = (int)($_GET['id'] ?? 0);
$p = ['name'=>'','slug'=>'','category'=>'General','price'=>'','old_price'=>'','stock'=>'10','short_desc'=>'','description'=>'','image'=>'','featured'=>0,'status'=>'active'];
if ($id) {
    $stmt = db()->prepare("SELECT * FROM products WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { flash_set('error','Product not found.'); header('Location: products.php'); exit; }
    $p = $found;
}
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_check()) $errors[] = 'Session expired. Try again.';
    $p['name'] = trim($_POST['name'] ?? '');
    $p['slug'] = slugify($_POST['slug'] ?? $p['name']);
    $p['category'] = trim($_POST['category'] ?? 'General') ?: 'General';
    $p['price'] = (float)($_POST['price'] ?? 0);
    $p['old_price'] = ($_POST['old_price'] ?? '') === '' ? null : (float)$_POST['old_price'];
    $p['stock'] = max(0, (int)($_POST['stock'] ?? 0));
    $p['short_desc'] = trim($_POST['short_desc'] ?? '');
    $p['description'] = trim($_POST['description'] ?? '');
    $p['featured'] = isset($_POST['featured']) ? 1 : 0;
    $p['status'] = ($_POST['status'] ?? 'active') === 'hidden' ? 'hidden' : 'active';
    if (strlen($p['name']) < 3) $errors[] = 'Product name is required.';
    if ($p['price'] <= 0) $errors[] = 'Price must be greater than zero.';
    // unique slug
    $chk = db()->prepare("SELECT id FROM products WHERE slug=? AND id!=? LIMIT 1");
    $chk->execute([$p['slug'], $id]);
    if ($chk->fetch()) $p['slug'] .= '-' . time();

    if (!$errors && !empty($_FILES['image']['name'])) {
        $img = upload_image('image', __DIR__ . '/../uploads/products', 'prod');
        if (!$img) $errors[] = 'Image upload failed (JPG/PNG/WEBP under 5MB).';
        else {
            if (!empty($p['image'])) @unlink(__DIR__ . '/../uploads/products/' . $p['image']);
            $p['image'] = $img;
        }
    }
    if (!$errors) {
        if ($id) {
            db()->prepare("UPDATE products SET name=?, slug=?, category=?, price=?, old_price=?, stock=?, short_desc=?, description=?, image=?, featured=?, status=? WHERE id=?")
              ->execute([$p['name'],$p['slug'],$p['category'],$p['price'],$p['old_price'],$p['stock'],$p['short_desc'],$p['description'],$p['image'],$p['featured'],$p['status'],$id]);
            flash_set('success','Product updated.');
        } else {
            db()->prepare("INSERT INTO products (name, slug, category, price, old_price, stock, short_desc, description, image, featured, status) VALUES (?,?,?,?,?,?,?,?,?,?,?)")
              ->execute([$p['name'],$p['slug'],$p['category'],$p['price'],$p['old_price'],$p['stock'],$p['short_desc'],$p['description'],$p['image'],$p['featured'],$p['status']]);
            flash_set('success','Product added.');
        }
        header('Location: products.php'); exit;
    }
}
$pageTitle = $id ? 'Edit Product' : 'Add Product';
require_once __DIR__ . '/includes/admin-header.php';
$cats = db()->query("SELECT DISTINCT category FROM products ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
?>
<?php if ($errors): ?><div class="flash flash-error"><?= implode('<br>', array_map('e',$errors)) ?></div><?php endif; ?>
<div class="card">
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field"><label>Product Name *</label><input id="f-title" type="text" name="name" value="<?= e($p['name']) ?>" required></div>
    <div class="field"><label>Slug (URL)</label><input id="f-slug" type="text" name="slug" value="<?= e($p['slug']) ?>" placeholder="auto-generated"></div>
    <div class="field"><label>Category</label><input type="text" name="category" list="catlist" value="<?= e($p['category']) ?>"><datalist id="catlist"><?php foreach ($cats as $c): ?><option value="<?= e($c) ?>"><?php endforeach; ?></datalist></div>
    <div class="field"><label>Status</label><select name="status"><option value="active" <?= $p['status']==='active'?'selected':'' ?>>Active (visible in shop)</option><option value="hidden" <?= $p['status']==='hidden'?'selected':'' ?>>Hidden</option></select></div>
    <div class="field"><label>Price (₦) *</label><input type="number" step="0.01" min="0" name="price" value="<?= e($p['price']) ?>" required></div>
    <div class="field"><label>Old Price (₦, optional)</label><input type="number" step="0.01" min="0" name="old_price" value="<?= e($p['old_price'] ?? '') ?>" placeholder="for discount display"></div>
    <div class="field"><label>Stock Quantity</label><input type="number" min="0" name="stock" value="<?= e($p['stock']) ?>"></div>
    <div class="field"><label>Photo</label><input type="file" name="image" accept="image/*"><?php if ($p['image']): ?><span class="hint">Current: <?= e($p['image']) ?></span><?php endif; ?></div>
    <div class="field full"><label>Short Description</label><input type="text" name="short_desc" value="<?= e($p['short_desc']) ?>" maxlength="255"></div>
    <div class="field full"><label>Full Description</label><textarea name="description" rows="5"><?= e($p['description']) ?></textarea></div>
    <div class="field"><label><input type="checkbox" name="featured" value="1" <?= !empty($p['featured'])?'checked':'' ?>> ⭐ Featured (show on homepage)</label></div>
  </div>
  <div style="display:flex;gap:.7rem;margin-top:1.2rem">
    <button class="btn btn-navy" type="submit"><?= $id?'Update Product':'Add Product' ?></button>
    <a class="btn btn-outline" href="products.php">Cancel</a>
  </div>
</form>
</div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
