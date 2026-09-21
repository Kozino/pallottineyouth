<?php
$adminActive = 'applications';
require_once __DIR__ . '/../config/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare("SELECT * FROM applications WHERE id=? LIMIT 1");
$stmt->execute([$id]);
$a = $stmt->fetch();
if (!$a) { flash_set('error','Application not found.'); header('Location: applications.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $new = $_POST['status'] ?? $a['status'];
    $note = trim($_POST['admin_note'] ?? '');
    if (in_array($new, ['pending','reviewed','accepted','rejected'], true)) {
        db()->prepare("UPDATE applications SET status=?, admin_note=? WHERE id=?")->execute([$new, $note, $id]);
        $a['status'] = $new; $a['admin_note'] = $note;
        flash_set('success', 'Application ' . $a['app_code'] . ' updated to ' . $new . '.');
    }
    header('Location: application-view.php?id=' . $id); exit;
}
if (isset($_GET['delete'])) {
    db()->prepare("DELETE FROM applications WHERE id=?")->execute([$id]);
    if (!empty($a['passport_image'])) @unlink(__DIR__ . '/../uploads/passports/' . $a['passport_image']);
    flash_set('success','Application deleted.');
    header('Location: applications.php'); exit;
}
$pageTitle = 'Application ' . $a['app_code'];
require_once __DIR__ . '/includes/admin-header.php';
?>
<div class="grid-2">
  <div class="card">
    <h3>Applicant Details</h3>
    <?php if ($a['passport_image'] && file_exists(__DIR__ . '/../uploads/passports/' . $a['passport_image'])): ?>
      <img class="receipt-img" style="max-width:160px;margin-bottom:1rem" src="../uploads/passports/<?= e($a['passport_image']) ?>" alt="Passport">
    <?php endif; ?>
    <dl class="kv">
      <dt>Full Name</dt><dd><?= e($a['full_name']) ?></dd>
      <dt>DOB / Gender</dt><dd><?= e($a['dob'] ?: '—') ?> / <?= e($a['gender'] ?: '—') ?></dd>
      <dt>Phone</dt><dd><?= e($a['phone']) ?></dd>
      <dt>Email</dt><dd><?= e($a['email']) ?></dd>
      <dt>Address</dt><dd><?= e($a['address'] ?: '—') ?></dd>
      <dt>State</dt><dd><?= e($a['state'] ?: '—') ?></dd>
      <dt>Diocese</dt><dd><?= e($a['diocese'] ?: '—') ?></dd>
      <dt>Parish</dt><dd><?= e($a['parish'] ?: '—') ?></dd>
      <dt>Education</dt><dd><?= e($a['education'] ?: '—') ?></dd>
      <dt>Membership</dt><dd><?= e($a['membership_type'] ?: '—') ?></dd>
      <dt>Skills</dt><dd><?= e($a['skills'] ?: '—') ?></dd>
      <dt>Emergency</dt><dd><?= e($a['emergency_name'] ?: '—') ?> <?= e($a['emergency_phone'] ?: '') ?></dd>
      <dt>Applied</dt><dd><?= e(format_datetime($a['created_at'])) ?></dd>
    </dl>
    <h3 style="margin-top:1rem">Why they want to join</h3>
    <p><?= nl2br(e($a['why_join'] ?: '—')) ?></p>
  </div>
  <div>
    <div class="card">
      <h3>Review Application</h3>
      <p>Status: <span class="st <?= status_class($a['status']) ?>"><?= e($a['status']) ?></span></p><br>
      <form method="post">
        <?= csrf_field() ?>
        <div class="field"><label>Decision</label>
          <select name="status">
            <?php foreach (['pending'=>'⏳ Pending','reviewed'=>'👁 Reviewed','accepted'=>'✅ Accepted','rejected'=>'❌ Rejected'] as $k=>$v): ?>
            <option value="<?= $k ?>" <?= $a['status']===$k?'selected':'' ?>><?= e($v) ?></option><?php endforeach; ?>
          </select></div>
        <div class="field" style="margin-top:.8rem"><label>Internal Note (optional)</label>
          <textarea name="admin_note" rows="3" placeholder="e.g. Assigned to Lagos chapter…"><?= e($a['admin_note'] ?? '') ?></textarea></div>
        <div style="display:flex;gap:.6rem;margin-top:.8rem;flex-wrap:wrap">
          <button class="btn btn-navy" type="submit">Save</button>
          <a class="btn btn-outline" href="applications.php">← All</a>
          <a class="btn btn-danger" data-confirm="Delete this application?" href="application-view.php?id=<?= (int)$a['id'] ?>&delete=1">Delete</a>
        </div>
      </form>
    </div>
    <div class="card">
      <h3>Contact Applicant</h3>
      <p class="hint">Reach out by phone, SMS or WhatsApp:</p>
      <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-top:.6rem">
        <a class="btn btn-green btn-sm" href="tel:<?= e(preg_replace('/\s/','',$a['phone'])) ?>">📞 Call</a>
        <a class="btn btn-outline btn-sm" href="mailto:<?= e($a['email']) ?>?subject=Your%20Pallottine%20Youth%20Application%20<?= e($a['app_code']) ?>">✉️ Email</a>
      </div>
    </div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
