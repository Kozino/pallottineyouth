<?php
/**
 * database.php — PDO connection (MySQL first, SQLite fallback for local dev)
 * All queries in this project use PDO prepared statements.
 */
require_once __DIR__ . '/config.php';

function db(): PDO {
    static $pdo = null;
    static $driver = null;
    if ($pdo instanceof PDO) return $pdo;

    // --- 1) Try MySQL (production) ---
    try {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
        $driver = 'mysql';
        return $pdo;
    } catch (Throwable $e) {
        // fall through to SQLite (local preview / dev)
    }

    // --- 2) Fallback: SQLite (auto-created + seeded) ---
    try {
        $pdo = new PDO('sqlite:' . SQLITE_FILE, null, null, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $driver = 'sqlite';
        ensure_sqlite_schema($pdo);
        return $pdo;
    } catch (Throwable $e) {
        http_response_code(500);
        exit('Database connection failed. Please check config/config.php');
    }
}

function db_driver(): string {
    db();
    // detect driver from connection
    try {
        $pdo = db();
        return (string) $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    } catch (Throwable $e) {
        return 'mysql';
    }
}

/**
 * Create SQLite tables (same shape as database.sql) + seed demo data.
 * Only used when MySQL is unreachable (local preview).
 */
function ensure_sqlite_schema(PDO $pdo): void {
    $check = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='settings'")->fetch();
    if ($check) return;

    $sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS settings (
    skey VARCHAR(100) PRIMARY KEY,
    svalue TEXT
);
CREATE TABLE IF NOT EXISTS admins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(30) NOT NULL DEFAULT 'admin',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    category VARCHAR(100) NOT NULL DEFAULT 'General',
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    old_price DECIMAL(12,2) NULL,
    stock INTEGER NOT NULL DEFAULT 0,
    short_desc VARCHAR(255) NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    featured INTEGER NOT NULL DEFAULT 0,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_code VARCHAR(30) NOT NULL UNIQUE,
    customer_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    address TEXT NOT NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    items_json TEXT NOT NULL,
    subtotal DECIMAL(12,2) NOT NULL DEFAULT 0,
    delivery_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
    total DECIMAL(12,2) NOT NULL DEFAULT 0,
    payment_method VARCHAR(30) NOT NULL DEFAULT 'bank_transfer',
    sender_name VARCHAR(150) NULL,
    receipt_image VARCHAR(255) NULL,
    notes TEXT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS applications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    app_code VARCHAR(30) NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    dob VARCHAR(20) NULL,
    gender VARCHAR(20) NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(150) NOT NULL,
    address TEXT NULL,
    state VARCHAR(100) NULL,
    diocese VARCHAR(150) NULL,
    parish VARCHAR(150) NULL,
    education VARCHAR(150) NULL,
    membership_type VARCHAR(100) NULL,
    why_join TEXT NULL,
    skills TEXT NULL,
    emergency_name VARCHAR(150) NULL,
    emergency_phone VARCHAR(50) NULL,
    passport_image VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending',
    admin_note TEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(250) NOT NULL,
    slug VARCHAR(270) NOT NULL UNIQUE,
    type VARCHAR(20) NOT NULL DEFAULT 'blog',
    excerpt VARCHAR(300) NULL,
    content TEXT NULL,
    image VARCHAR(255) NULL,
    author VARCHAR(150) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'published',
    published_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS events (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title VARCHAR(250) NOT NULL,
    slug VARCHAR(270) NOT NULL UNIQUE,
    venue VARCHAR(200) NULL,
    event_date DATE NULL,
    event_time VARCHAR(50) NULL,
    description TEXT NULL,
    image VARCHAR(255) NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'upcoming',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    phone VARCHAR(50) NULL,
    subject VARCHAR(200) NULL,
    message TEXT NOT NULL,
    source VARCHAR(30) NOT NULL DEFAULT 'contact',
    status VARCHAR(20) NOT NULL DEFAULT 'unread',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);
SQL;
    $pdo->exec($sql);

    // Seed settings
    $settings = default_settings();
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO settings (skey, svalue) VALUES (?, ?)");
    foreach ($settings as $k => $v) $stmt->execute([$k, $v]);

    // Seed admin: admin@pallottineyouthnig.com / admin123
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT OR IGNORE INTO admins (name, email, password_hash, role) VALUES (?,?,?,?)")
        ->execute(['Super Admin', 'admin@pallottineyouthnig.com', $hash, 'super_admin']);

    seed_demo_content($pdo);
}

function default_settings(): array {
    return [
        'site_name'        => 'Pallottine Nigerian Youth',
        'tagline'          => 'The Love of Christ Impels Us — 2 Cor 5:14',
        'site_email'       => 'info@pallottineyouthnig.com',
        'site_phone'       => '+234 (0) 800 000 0000',
        'site_whatsapp'    => '2348000000000',
        'site_address'     => 'Pallottine Community, Nigeria',
        'facebook'         => 'https://facebook.com/',
        'instagram'        => 'https://instagram.com/',
        'twitter'          => 'https://x.com/',
        'youtube'          => 'https://youtube.com/',
        // Bank transfer details (shown at checkout — editable in Admin > Settings)
        'bank_name'        => 'GTBank (Guaranty Trust Bank)',
        'bank_account_name'=> 'Pallottine Nigerian Youth',
        'bank_account_no'  => '0123456789',
        'bank_branch'      => '',
        'payment_note'     => 'After payment, upload your receipt on the checkout page or send it via WhatsApp / email with your ORDER CODE. Your order is confirmed once payment is verified.',
        'delivery_fee_lagos'   => '2000',
        'delivery_fee_outside' => '3500',
        'hero_title'       => 'Reviving Faith, Rekindling Charity, Forming Apostles',
        'hero_subtitle'    => 'The youth arm of the Society of the Catholic Apostolate (Pallottines) in Nigeria — helping young people understand, live and share their faith.',
        'announcement'     => 'Pallottine Youth Festival 2026 — Registrations now open! Visit the Apply page to join.',
        'about_home'       => 'Inspired by St. Vincent Pallotti, we form young apostles for the Church and society through prayer, formation, service and fellowship across Nigeria.',
    ];
}

function seed_demo_content(PDO $pdo): void {
    // Demo products
    $products = [
        ['Pallottine Youth T-Shirt (White)', 'Apparel', 6500, 8000, 50, 'Premium white T-shirt with the Pallottine Youth crest.', 1],
        ['Pallottine Youth T-Shirt (Navy)', 'Apparel', 6500, null, 50, 'Premium navy T-shirt with gold Pallottine Youth print.', 1],
        ['Face Cap — Love of Christ Impels Us', 'Apparel', 4500, null, 40, 'Adjustable face cap with embroidered motto.', 0],
        ['Hoodie — Apostles of Christ', 'Apparel', 15000, 18000, 25, 'Warm pullover hoodie for programmes andgy outings.', 1],
        ['Rosary (Wooden Beads)', 'Devotional', 2500, null, 100, 'Hand-finished wooden rosary in a gift pouch.', 0],
        ['St. Vincent Pallotti Novena Booklet', 'Books', 1500, null, 200, 'Novena, prayers and life of St. Vincent Pallotti.', 1],
        ['Daily Prayer & Reflection Journal', 'Books', 3500, null, 80, 'Guided Catholic journal for young apostles.', 0],
        ['Wristband — Caritas Christi', 'Accessories', 1000, null, 300, 'Silicone wristband, pack of one.', 0],
    ];
    $stmt = $pdo->prepare("INSERT INTO products (name, slug, category, price, old_price, stock, short_desc, description, featured, status) VALUES (?,?,?,?,?,?,?,?,?, 'active')");
    foreach ($products as $p) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $p[0]), '-'));
        $stmt->execute([$p[0], $slug, $p[1], $p[2], $p[3], $p[4], $p[5], $p[5], $p[6]]);
    }
    // Demo posts
    $posts = [
        ['Welcome to Pallottine Nigerian Youth', 'news', 'Our new home on the web — news, formation, programmes and our shop in one place.'],
        ['Pallottine Youth Festival 2026: What to Expect', 'news', 'Music, Mass, adoration, workshops and fellowship — everything you need to know.'],
        ['Who Was St. Vincent Pallotti?', 'blog', 'The saint of boundless charity and founder of the Union of Catholic Apostolate.'],
        ['5 Ways to Live as a Young Apostle on Campus', 'blog', 'Practical steps for students who want to evangelise with joy.'],
    ];
    $stmt = $pdo->prepare("INSERT INTO posts (title, slug, type, excerpt, content, author, status, published_at) VALUES (?,?,?,?,?,?, 'published', CURRENT_TIMESTAMP)");
    foreach ($posts as $p) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $p[0]), '-'));
        $content = $p[2] . "\n\nFull article content can be edited from Admin > Posts. This is starter text to show how the news page looks.";
        $stmt->execute([$p[0], $slug, $p[1], $p[2], $content, 'Admin']);
    }
    // Demo event
    $pdo->prepare("INSERT INTO events (title, slug, venue, event_date, event_time, description, status) VALUES (?,?,?,?,?,?,'upcoming')")
        ->execute(['Pallottine Youth Festival 2026', 'pallottine-youth-festival-2026', 'Pallottine Retreat Centre, Lagos', '2026-12-12', '9:00 AM', 'A gathering of Pallottine youths from across Nigeria — Mass, talks, sports, concert and awards night.', ]);
}
