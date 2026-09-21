<?php
$adminActive = 'messages';
require_once __DIR__ . '/../config/functions.php';
require_admin();

// Actions
if (isset($_GET['read'])) {
    db()->prepare("UPDATE messages SET status='read' WHERE id=?")->execute([(int)$_GET['read']]);
    header('Location: messages.php'); exit;
}
if (isset($_GET['delete'])) {
    db()->prepare("DELETE FROM messages WHERE id=?")->execute([(int)$_GET['delete']]);
    flash_set('success','Message deleted.');
    header('Location: messages.php'); exit;
}
$pageTitle = 'Messages Inbox';
require_once __DIR__ . '/includes/admin-header.php';
$filter = $_GET['status'] ?? 'all';
$where = in_array($filter, ['unread','read'], true) ? "WHERE status=" . db()->quote($filter) : '';
$rows = db()->query("SELECT * FROM messages $where ORDER BY id DESC LIMIT 300")->fetchAll();
$viewId = (int)($_GET['view'] ?? 0);
$view = null;
if ($viewId) {
    $s = db()->prepare("SELECT * FROM messages WHERE id=? LIMIT 1");
    $s->execute([$viewId]);
    $view = $s->fetch();
    if ($view && $view['status'] === 'unread') {
        db()->prepare("UPDATE messages SET status='read' WHERE id=?")->execute([$viewId]);
        $view['status'] = 'read';
    }
}
?>
<div class="grid-2" style="grid-template-columns:1.2fr .8fr">
  <div>
    <div class="toolbar">
      <a class="btn btn-sm <?= $filter==='all'?'btn-navy':'btn-outline' ?>" href="messages.php">All</a>
      <a class="btn btn-sm <?= $filter==='unread'?'btn-navy':'btn-outline' ?>" href="messages.php?status=unread">Unread</a>
      <a class="btn btn-sm <?= $filter==='read'?'btn-navy':'btn-outline' ?>" href="messages.php?status=read">Read</a>
    </div>
    <div class="tbl-wrap"><table class="tbl">
      <tr><th>From</th><th>Subject</th><th>Source</th><th>Date</th><th></th></tr>
      <?php foreach ($rows as $m): ?>
      <tr style="<?= $m['status']==='unread'?'font-weight:700':'' ?>">
        <td><?= e($m['name']) ?><br><span class="hint"><?= e($m['phone'] ?: $m['email']) ?></span></td>
        <td><?= e(excerpt($m['subject'] ?: $m['message'], 50)) ?></td>
        <td><span class="st <?= $m['source']==='livechat'?'st-info':'st-muted' ?>"><?= e($m['source']) ?></span></td>
        <td class="hint"><?= e(format_date($m['created_at'])) ?></td>
        <td><a class="btn btn-outline btn-sm" href="messages.php?status=<?= e($filter) ?>&view=<?= (int)$m['id'] ?>">Open</a></td>
      </tr>
      <?php endforeach; ?>
      <?php if (!$rows): ?><tr><td colspan="5" class="hint">Inbox is empty.</td></tr><?php endif; ?>
    </table></div>
  </div>
  <div class="card">
    <h3>Message</h3>
    <?php if ($view): ?>
      <dl class="kv">
        <dt>From</dt><dd><?= e($view['name']) ?></dd>
        <dt>Email</dt><dd><?= e($view['email'] ?: '—') ?></dd>
        <dt>Phone</dt><dd><?= e($view['phone'] ?: '—') ?></dd>
        <dt>Subject</dt><dd><?= e($view['subject'] ?: '—') ?></dd>
        <dt>Date</dt><dd><?= e(format_datetime($view['created_at'])) ?></dd>
      </dl>
      <p style="margin-top:1rem"><?= nl2br(e($view['message'])) ?></p>
      <div style="display:flex;gap:.6rem;margin-top:1rem;flex-wrap:wrap">
        <?php if ($view['email']): ?><a class="btn btn-outline btn-sm" href="mailto:<?= e($view['email']) ?>?subject=Re:%20<?= urlencode($view['subject'] ?? 'Your message') ?>">Reply by Email</a><?php endif; ?>
        <a class="btn btn-danger btn-sm" data-confirm="Delete this message?" href="messages.php?delete=<?= (int)$view['id'] ?>">Delete</a>
      </div>
    <?php else: ?>
      <p class="hint">Select a message to read it here.</p>
    <?php endif; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
