<?php
$adminActive = 'admins';
require_once __DIR__ . '/../config/functions.php';
require_admin();
$me = current_admin();

// Add / update admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && csrf_check()) {
    $id = (int)($_POST['id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = (string)($_POST['password'] ?? '');
    $role = ($_POST['role'] ?? 'admin') === 'super_admin' ? 'super_admin' : 'admin';
    if (strlen($name) < 2 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('error', 'Enter a valid name and email.');
    } elseif (!$id && strlen($pass) < 6) {
        flash_set('error', 'Password must be at least 6 characters.');
    } else {
        try {
            if ($id) {
                if ($pass !== '') {
                    db()->prepare("UPDATE admins SET name=?, email=?, role=?, password_hash=? WHERE id=?")
                      ->execute([$name, $email, $role, password_hash($pass, PASSWORD_DEFAULT), $id]);
                } else {
                    db()->prepare("UPDATE admins SET name=?, email=?, role=? WHERE id=?")->execute([$name, $email, $role, $id]);
                }
                flash_set('success', 'Admin updated.');
            } else {
                db()->prepare("INSERT INTO admins (name, email, password_hash, role) VALUES (?,?,?,?)")
                  ->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT), $role]);
                flash_set('success', 'Admin added.');
            }
        } catch (Throwable $e) {
            flash_set('error', 'That email is already in use.');
        }
    }
    header('Location: admins.php'); exit;
}
if (isset($_GET['delete'])) {
    $delId = (int)$_GET['delete'];
    if ($delId === (int)$me['id']) {
        flash_set('error', 'You cannot delete your own account.');
    } else {
        db()->prepare("DELETE FROM admins WHERE id=?")->execute([$delId]);
        flash_set('success', 'Admin removed.');
    }
    header('Location: admins.php'); exit;
}
$edit = null;
if (isset($_GET['edit'])) {
    $s = db()->prepare("SELECT * FROM admins WHERE id=? LIMIT 1");
    $s->execute([(int)$_GET['edit']]);
    $edit = $s->fetch();
}
$rows = db()->query("SELECT id, name, email, role, created_at FROM admins ORDER BY id ASC")->fetchAll();
$pageTitle = 'Manage Admins';
require_once __DIR__ . '/includes/admin-header.php';
?>
<div class="grid-2">
  <div class="card">
    <h3><?= $edit ? 'Edit Admin' : 'Add Admin' ?></h3>
    <form method="post">
      <?= csrf_field() ?>
      <input type="hidden" name="id" value="<?= (int)($edit['id'] ?? 0) ?>">
      <div class="field"><label>Name</label><input type="text" name="name" value="<?= e($edit['name'] ?? '') ?>" required></div><br>
      <div class="field"><label>Email (login)</label><input type="email" name="email" value="<?= e($edit['email'] ?? '') ?>" required></div><br>
      <div class="field"><label>Password <?= $edit ? '(leave blank to keep)' : '' ?></label><input type="password" name="password" <?= $edit?'':'required' ?> minlength="6"></div><br>
      <div class="field"><label>Role</label><select name="role">
        <option value="admin" <?= (($edit['role'] ?? '')==='admin')?'selected':'' ?>>Admin</option>
        <option value="super_admin" <?= (($edit['role'] ?? '')==='super_admin')?'selected':'' ?>>Super Admin</option>
      </select></div><br>
      <button class="btn btn-navy" type="submit"><?= $edit ? 'Update' : 'Add Admin' ?></button>
      <?php if ($edit): ?><a class="btn btn-outline" href="admins.php">Cancel</a><?php endif; ?>
    </form>
  </div>
  <div class="card">
    <h3>All Admins</h3>
    <div class="tbl-wrap"><table class="tbl">
      <tr><th>Name</th><th>Email</th><th>Role</th><th></th></tr>
      <?php foreach ($rows as $r): ?>
      <tr>
        <td><?= e($r['name']) ?><?= (int)$r['id']===(int)$me['id']?' (you)':'' ?></td>
        <td><?= e($r['email']) ?></td>
        <td><?= e($r['role']) ?></td>
        <td><div class="row-actions">
          <a class="btn btn-outline btn-sm" href="admins.php?edit=<?= (int)$r['id'] ?>">Edit</a>
          <?php if ((int)$r['id']!==(int)$me['id']): ?>
          <a class="btn btn-danger btn-sm" data-confirm="Remove this admin?" href="admins.php?delete=<?= (int)$r['id'] ?>">Remove</a>
          <?php endif; ?>
        </div></td>
      </tr>
      <?php endforeach; ?>
    </table></div>
  </div>
</div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
