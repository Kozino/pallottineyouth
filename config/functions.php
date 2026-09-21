<?php
/**
 * functions.php — shared helpers (frontend + admin)
 * All internal links are RELATIVE (no base URL).
 */
require_once __DIR__ . '/database.php';

// ---------- Settings (cached per request) ----------
function setting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        try {
            foreach (db()->query("SELECT skey, svalue FROM settings")->fetchAll() as $row) {
                $cache[$row['skey']] = (string) $row['svalue'];
            }
        } catch (Throwable $e) { /* table missing before install */ }
        foreach (default_settings() as $k => $v) {
            if (!isset($cache[$k])) $cache[$k] = $v;
        }
    }
    return $cache[$key] ?? $default;
}

function save_setting(string $key, string $value): void {
    $pdo = db();
    if (db_driver() === 'mysql') {
        $stmt = $pdo->prepare("INSERT INTO settings (skey, svalue) VALUES (?, ?) ON DUPLICATE KEY UPDATE svalue = VALUES(svalue)");
        $stmt->execute([$key, $value]);
    } else {
        $stmt = $pdo->prepare("INSERT OR REPLACE INTO settings (skey, svalue) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }
}

// ---------- Output helpers ----------
function e(?string $s): string {
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
function naira($amount): string {
    return APP_CURRENCY . number_format((float) $amount, 2);
}
function naira_short($amount): string {
    return APP_CURRENCY . number_format((float) $amount);
}
function slugify(string $text): string {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-') ?: ('item-' . time());
}
function excerpt(string $text, int $len = 140): string {
    $text = trim(preg_replace('/\s+/', ' ', strip_tags($text)));
    return (mb_strlen($text) > $len) ? mb_substr($text, 0, $len) . '…' : $text;
}
function format_date(?string $dt, string $fmt = 'M j, Y'): string {
    if (!$dt) return '';
    try { return (new DateTime($dt))->format($fmt); } catch (Throwable $e) { return (string) $dt; }
}
function format_datetime(?string $dt): string {
    return format_date($dt, 'M j, Y · h:i A');
}

// ---------- Flash messages ----------
function flash_set(string $type, string $message): void {
    $_SESSION['flash'][] = ['type' => $type, 'msg' => $message];
}
function flash_get(): array {
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

// ---------- CSRF ----------
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}
function csrf_check(): bool {
    return isset($_POST['csrf'], $_SESSION['csrf']) && hash_equals($_SESSION['csrf'], (string) $_POST['csrf']);
}

// ---------- Auth (admin) ----------
function admin_logged_in(): bool {
    return !empty($_SESSION['admin_id']);
}
function current_admin(): ?array {
    if (!admin_logged_in()) return null;
    static $admin = null;
    if ($admin === null) {
        $stmt = db()->prepare("SELECT id, name, email, role, created_at FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['admin_id']]);
        $admin = $stmt->fetch() ?: null;
    }
    return $admin;
}
function require_admin(): void {
    if (!admin_logged_in()) {
        header('Location: index.php');
        exit;
    }
}

// ---------- File uploads ----------
function upload_image(string $field, string $destDir, string $prefix = 'img'): ?string {
    if (empty($_FILES[$field]) || ($_FILES[$field]['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    $file = $_FILES[$field];
    if ($file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > MAX_UPLOAD_SIZE) return null;
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    if (!in_array($mime, ALLOWED_IMAGE_TYPES, true)) return null;
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'][$mime];
    if (!is_dir($destDir)) mkdir($destDir, 0755, true);
    $name = $prefix . '-' . date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . $ext;
    $dest = rtrim($destDir, '/') . '/' . $name;
    return move_uploaded_file($file['tmp_name'], $dest) ? $name : null;
}

// ---------- Cart (session based) ----------
function cart_items(): array {
    return $_SESSION['cart'] ?? [];
}
function cart_count(): int {
    $n = 0;
    foreach (cart_items() as $qty) $n += (int) $qty;
    return $n;
}
function cart_detailed(): array {
    $cart = cart_items();
    if (!$cart) return ['lines' => [], 'subtotal' => 0];
    $ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $stmt = db()->prepare("SELECT * FROM products WHERE id IN ($placeholders) AND status = 'active'");
    $stmt->execute($ids);
    $lines = []; $subtotal = 0;
    foreach ($stmt->fetchAll() as $p) {
        $qty = max(1, min(99, (int) ($cart[$p['id']] ?? 1)));
        $lineTotal = (float) $p['price'] * $qty;
        $subtotal += $lineTotal;
        $lines[] = ['product' => $p, 'qty' => $qty, 'line_total' => $lineTotal];
    }
    return ['lines' => $lines, 'subtotal' => $subtotal];
}
function cart_clear(): void {
    unset($_SESSION['cart']);
}

// ---------- Unique codes ----------
function gen_code(string $prefix): string {
    return $prefix . '-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
}

// ---------- Status badge class ----------
function status_class(string $status): string {
    $map = [
        'pending' => 'st-pending', 'unread' => 'st-pending',
        'confirmed' => 'st-ok', 'accepted' => 'st-ok', 'published' => 'st-ok',
        'active' => 'st-ok', 'upcoming' => 'st-ok', 'read' => 'st-ok',
        'reviewed' => 'st-info', 'shipped' => 'st-info',
        'delivered' => 'st-ok', 'rejected' => 'st-bad', 'cancelled' => 'st-bad',
        'draft' => 'st-muted', 'hidden' => 'st-muted', 'archived' => 'st-muted',
    ];
    return $map[strtolower($status)] ?? 'st-muted';
}

// ---------- Product image URL (relative) ----------
function product_image(?string $img, string $base = ''): string {
    if ($img && file_exists(__DIR__ . '/../uploads/products/' . $img)) {
        return $base . 'uploads/products/' . $img;
    }
    return $base . 'assets/images/placeholder-product.svg';
}
function post_image(?string $img, string $base = ''): string {
    if ($img && file_exists(__DIR__ . '/../uploads/posts/' . $img)) {
        return $base . 'uploads/posts/' . $img;
    }
    return $base . 'assets/images/placeholder-post.svg';
}
function event_image(?string $img, string $base = ''): string {
    if ($img && file_exists(__DIR__ . '/../uploads/posts/' . $img)) {
        return $base . 'uploads/posts/' . $img;
    }
    return $base . 'assets/images/placeholder-post.svg';
}
