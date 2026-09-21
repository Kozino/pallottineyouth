<?php
$adminActive = 'applications';
$pageTitle = 'Membership Applications';
require_once __DIR__ . '/includes/admin-header.php';
$status = $_GET['status'] ?? 'all';
$q = trim($_GET['q'] ?? '');
$where = '1=1'; $params = [];
if (in_array($status, ['pending','reviewed','accepted','rejected'], true)) { $where .= " AND status=?"; $params[] = $status; }
if ($q !== '') { $where .= " AND (full_name LIKE ? OR email LIKE ? OR phone LIKE ? OR app_code LIKE ? OR state LIKE ?)"; for ($i=0;$i<5;$i++) $params[] = "%$q%"; }
$stmt = db()->prepare("SELECT * FROM applications WHERE $where ORDER BY id DESC LIMIT 300");
$stmt->execute($params);
$rows = $stmt->fetchAll();
?>
<div class="toolbar">
  <form method="get" action="applications.php">
    <input type="text" name="q" placeholder="Search name / email / code…" value="<?= e($q) ?>">
    <select name="status">
      <option value="all">All statuses</option>
      <?php foreach (['pending','reviewed','accepted','rejected'] as $s): ?>
      <option value="<?= $s ?>" <?= $status===$s?'selected':'' ?>><?= ucfirst($s) ?></option><?php endforeach; ?>
    </select>
    <button class="btn btn-navy btn-sm" type="submit">Filter</button>
  </form>
  <span class="hint" style="margin-left:auto"><?= count($rows) ?> application(s)</span>
</div>
<div class="tbl-wrap"><table class="tbl">
  <tr><th>Code</th><th>Applicant</th><th>Contact</th><th>State</th><th>Type</th><th>Status</th><th>Date</th><th></th></tr>
  <?php foreach ($rows as $a): ?>
  <tr>
    <td><strong><?= e($a['app_code']) ?></strong></td>
    <td><?= e($a['full_name']) ?><br><span class="hint"><?= e($a['parish'] ?: '') ?></span></td>
    <td><?= e($a['phone']) ?><br><span class="hint"><?= e($a['email']) ?></span></td>
    <td><?= e($a['state'] ?: '—') ?></td>
    <td><?= e($a['membership_type'] ?: '—') ?></td>
    <td><span class="st <?= status_class($a['status']) ?>"><?= e($a['status']) ?></span></td>
    <td class="hint"><?= e(format_date($a['created_at'])) ?></td>
    <td><a class="btn btn-outline btn-sm" href="application-view.php?id=<?= (int)$a['id'] ?>">View</a></td>
  </tr>
  <?php endforeach; ?>
  <?php if (!$rows): ?><tr><td colspan="8" class="hint">No applications found.</td></tr><?php endif; ?>
</table></div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
