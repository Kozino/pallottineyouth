<?php
$adminActive = 'events';
require_once __DIR__ . '/../config/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$p = ['title'=>'','slug'=>'','venue'=>'','event_date'=>'','event_time'=>'','description'=>'','image'=>'','status'=>'upcoming'];
if ($id) {
    $stmt = db()->prepare("SELECT * FROM events WHERE id=? LIMIT 1");
    $stmt->execute([$id]);
    $found = $stmt->fetch();
    if (!$found) { header('Location: events.php'); exit; }
    $p = $found;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $p['title'] = trim($_POST['title'] ?? '');
    $p['slug'] = slugify($_POST['slug'] ?? $p['title']);
    $p['venue'] = trim($_POST['venue'] ?? '');
    $p['event_date'] = $_POST['event_date'] ?? null;
    $p['event_time'] = trim($_POST['event_time'] ?? '');
    $p['description'] = trim($_POST['description'] ?? '');
    $p['status'] = in_array($_POST['status'] ?? '', ['upcoming','past','cancelled'], true) ? $_POST['status'] : 'upcoming';
    if (!empty($_FILES['image']['name'])) {
        $img = upload_image('image', __DIR__ . '/../uploads/posts', 'event');
        if ($img) $p['image'] = $img;
    }
    if ($id) {
        db()->prepare("UPDATE events SET title=?, slug=?, venue=?, event_date=?, event_time=?, description=?, image=?, status=? WHERE id=?")
          ->execute([$p['title'],$p['slug'],$p['venue'],$p['event_date'] ?: null,$p['event_time'],$p['description'],$p['image'],$p['status'],$id]);
        flash_set('success','Event updated.');
    } else {
        db()->prepare("INSERT INTO events (title, slug, venue, event_date, event_time, description, image, status) VALUES (?,?,?,?,?,?,?,?)")
          ->execute([$p['title'],$p['slug'],$p['venue'],$p['event_date'] ?: null,$p['event_time'],$p['description'],$p['image'],$p['status']]);
        flash_set('success','Event added.');
    }
    header('Location: events.php'); exit;
}
$pageTitle = $id ? 'Edit Event' : 'New Event';
require_once __DIR__ . '/includes/admin-header.php';
?>
<div class="card">
<form method="post" enctype="multipart/form-data">
  <?= csrf_field() ?>
  <div class="form-grid">
    <div class="field"><label>Title *</label><input id="f-title" type="text" name="title" value="<?= e($p['title']) ?>" required></div>
    <div class="field"><label>Slug</label><input id="f-slug" type="text" name="slug" value="<?= e($p['slug']) ?>"></div>
    <div class="field"><label>Venue</label><input type="text" name="venue" value="<?= e($p['venue']) ?>"></div>
    <div class="field"><label>Date</label><input type="date" name="event_date" value="<?= e($p['event_date'] ?? '') ?>"></div>
    <div class="field"><label>Time</label><input type="text" name="event_time" value="<?= e($p['event_time']) ?>" placeholder="e.g. 9:00 AM"></div>
    <div class="field"><label>Status</label><select name="status">
      <?php foreach (['upcoming'=>'Upcoming','past'=>'Past','cancelled'=>'Cancelled'] as $k=>$v): ?>
      <option value="<?= $k ?>" <?= $p['status']===$k?'selected':'' ?>><?= $v ?></option><?php endforeach; ?>
    </select></div>
    <div class="field"><label>Image</label><input type="file" name="image" accept="image/*"></div>
    <div class="field full"><label>Description</label><textarea name="description" rows="5"><?= e($p['description']) ?></textarea></div>
  </div>
  <div style="display:flex;gap:.7rem;margin-top:1.2rem">
    <button class="btn btn-navy" type="submit">Save Event</button>
    <a class="btn btn-outline" href="events.php">Cancel</a>
  </div>
</form>
</div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
