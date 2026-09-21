<?php
/**
 * install.php — One-click setup checker for pallottineyouthnig.com
 * Upload all files, visit https://pallottineyouthnig.com/install.php,
 * follow the steps, then DELETE this file.
 */
require_once __DIR__ . '/config/config.php';
$steps = [];
$ok = true;

// 1. PHP version
$steps[] = ['PHP version ' . PHP_VERSION, version_compare(PHP_VERSION, '7.4', '>=')];

// 2. PDO MySQL
$hasMysql = extension_loaded('pdo_mysql');
$steps[] = ['PDO MySQL extension', $hasMysql];

// 3. Writable uploads
$writable = is_writable(__DIR__ . '/uploads');
@mkdir(__DIR__ . '/uploads/receipts', 0755, true);
@mkdir(__DIR__ . '/uploads/products', 0755, true);
@mkdir(__DIR__ . '/uploads/posts', 0755, true);
@mkdir(__DIR__ . '/uploads/passports', 0755, true);
$steps[] = ['uploads/ folder writable', $writable];

// 4. MySQL connection
$connected = false; $tables = [];
try {
    $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $connected = true;
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $ok = false;
}
$steps[] = ['MySQL connection (' . DB_USER . '@' . DB_HOST . ' / ' . DB_NAME . ')', $connected];
$needImport = $connected && !in_array('settings', $tables);
$steps[] = ['Database tables imported', $connected && !$needImport];

// Auto-import database.sql if requested
$msg = '';
if ($connected && $needImport && isset($_GET['import'])) {
    $sql = file_get_contents(__DIR__ . '/database.sql');
    // naive split on ";\n" boundaries outside of procedures (this file has none)
    $statements = array_filter(array_map('trim', preg_split('/;\s*\n/', $sql)));
    $count = 0;
    try {
        foreach ($statements as $st) {
            if ($st === '' || str_starts_with($st, '--') && strpos($st, "\nCREATE") === false && strpos($st, "\nINSERT") === false && strpos($st, "\nSET") === false) {
                // strip full-line comments then check
                $lines = array_filter(explode("\n", $st), fn($l) => !str_starts_with(trim($l), '--') && trim($l) !== '' && !str_starts_with(trim($l), 'SET '));
                $st = implode("\n", $lines);
                if (trim($st) === '') continue;
            }
            $pdo->exec($st);
            $count++;
        }
        $msg = "Imported $count statement(s) successfully. Default admin: admin@pallottineyouthnig.com / admin123 — change it immediately!";
        $needImport = false;
    } catch (Throwable $e) {
        $msg = 'Import error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Install — Pallottine Nigerian Youth</title>
<style>body{font-family:Arial,sans-serif;background:#f3f4f8;padding:2rem} .box{background:#fff;max-width:640px;margin:0 auto;border-radius:14px;padding:2rem;box-shadow:0 10px 30px rgba(0,0,0,.1)} li{margin:.5rem 0} .ok{color:green} .bad{color:#c0392b} .btn{display:inline-block;background:#E63946;color:#fff;padding:.7rem 1.4rem;border-radius:10px;text-decoration:none;font-weight:700;margin-top:1rem}</style>
</head><body><div class="box">
<h1>🛠️ Installation Check</h1>
<?php if ($msg): ?><p><strong><?= htmlspecialchars($msg) ?></strong></p><?php endif; ?>
<ul>
<?php foreach ($steps as [$label, $pass]): ?>
  <li class="<?= $pass ? 'ok' : 'bad' ?>"><?= $pass ? '✅' : '❌' ?> <?= htmlspecialchars($label) ?></li>
<?php endforeach; ?>
</ul>
<?php if (!$connected): ?>
  <p>⚠️ MySQL failed. Edit <code>config/config.php</code> with your cPanel database name, user &amp; password, then refresh this page.</p>
<?php elseif ($needImport): ?>
  <p>⚠️ Tables not found. <a class="btn" href="install.php?import=1">Click here to import database.sql automatically</a></p>
  <p>Or import manually via cPanel → phpMyAdmin → Import → choose <code>database.sql</code>.</p>
<?php else: ?>
  <p>🎉 <strong>All good!</strong> Now <strong style="color:#c0392b">DELETE install.php</strong> from the server, then:</p>
  <p><a class="btn" href="index.php">View Website</a> <a class="btn" href="admin/index.php">Open Admin Login</a></p>
  <p>Default admin: <code>admin@pallottineyouthnig.com</code> / <code>admin123</code> — change immediately in Admins.</p>
<?php endif; ?>
</div></body></html>
