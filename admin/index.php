<?php
/**
 * Admin login
 * Default (from seeder): admin@pallottineyouthnig.com / admin123
 * IMPORTANT: change immediately in Admins > Edit.
 */
require_once __DIR__ . '/../config/functions.php';
if (admin_logged_in()) { header('Location: dashboard.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = (string)($_POST['password'] ?? '');
    $stmt = db()->prepare("SELECT * FROM admins WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $a = $stmt->fetch();
    if ($a && password_verify($pass, $a['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = (int)$a['id'];
        header('Location: dashboard.php'); exit;
    }
    $error = 'Invalid email or password.';
}
$publicPage = true;
$pageTitle = 'Admin Login';
require_once __DIR__ . '/includes/admin-header.php';
?>
<div class="login-card">
  <svg viewBox="0 0 48 48" width="52" height="52" style="margin:0 auto"><circle cx="24" cy="24" r="22" fill="#0B1F4B"/><path d="M24 8v24M15 17h18" stroke="#fff" stroke-width="3.4" stroke-linecap="round"/><path d="M14 33c3-2.5 6.4-3.6 10-3.6s7 1.1 10 3.6" stroke="#C9A227" stroke-width="2.6" fill="none" stroke-linecap="round"/></svg>
  <h1>Pallottine Youth — Admin</h1>
  <p>Sign in to manage shop, orders, applications, news &amp; settings.</p>
  <?php if ($error): ?><div class="flash flash-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" action="index.php">
    <div><label>Email</label><input type="email" name="email" required autofocus></div>
    <div><label>Password</label><input type="password" name="password" required></div>
    <button class="btn btn-navy btn-block" type="submit">Login →</button>
  </form>
  <p style="margin-top:1rem"><a href="../index.php">← Back to website</a></p>
</div>
<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
