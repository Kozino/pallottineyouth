<?php
$adminActive = 'events';
$pageTitle = 'Events';
require_once __DIR__ . '/includes/admin-header.php';
$rows = db()->query("SELECT * FROM events ORDER BY event_date DESC, id DESC LIMIT 200")->fetchAll();
?>
<div class="toolbar"><a href="event-form.php" class="btn btn-gold btn-sm" style="margin-left:auto">+ New Event</a></div>
<div class="tbl-wrap"><table class="tbl">
  <tr><th>Title</th><th>Venue</th><th>Date</th><th>Status</th><th></th></tr>
  <?php foreach ($rows as $ev): ?>
  <tr>
    <td><strong><?= e($ev['title']) ?></strong></td>
    <td><?= e($ev['venue'] ?: '—') ?></td>
    <td><?= e(format_date($ev['event_date'])) ?> <?= e($ev['event_time'] ?: '') ?></td>
    <td><span class="st <?= status_class($ev['status']) ?>"><?= e($ev['status']) ?></span></td>
    <td><div class="row-actions">
      <a class="btn btn-outline btn-sm" href="event-form.php?id=<?= (int)$ev['id'] ?>">Edit</a>
      <a class="btn btn-danger btn-sm" data-confirm="Delete this event?" href="event-delete.php?id=<?= (int)$ev['id'] ?>">Delete</a>
    </div></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="5" class="hint">No events yet.</td></tr><?php endif; ?>
</table></div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
