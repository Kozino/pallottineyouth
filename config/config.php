<?php
/**
 * ============================================================
 *  PALLOTTINE NIGERIAN YOUTH  |  pallottineyouthnig.com
 *  config.php — Application configuration
 * ------------------------------------------------------------
 *  NOTE: "NO BASE URL" — the site uses RELATIVE paths everywhere
 *  (e.g. "assets/css/style.css", "shop.php", "../index.php").
 *  Nothing here hard-codes the domain, so the same code runs on
 *  localhost, a staging folder, or https://pallottineyouthnig.com
 *  without changing a single line.
 * ============================================================
 */

// ---------- Error reporting (turn off display on live server) ----------
error_reporting(E_ALL);
ini_set('display_errors', '0');          // set to '1' while developing locally
ini_set('log_errors', '1');

// ---------- Session ----------
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------- Timezone ----------
date_default_timezone_set('Africa/Lagos');

// ============================================================
//  DATABASE — MySQL (production on pallottineyouthnig.com)
// ------------------------------------------------------------
//  Fill these in with the cPanel MySQL details, then import
//  database.sql via phpMyAdmin. That is the ONLY file you
//  ever need to edit when moving servers.
// ============================================================
define('DB_HOST', 'localhost');
define('DB_NAME', 'pallotti_youthnig');   // <-- cPanel database name
define('DB_USER', 'pallotti_admin');      // <-- cPanel database user
define('DB_PASS', 'change-this-password');// <-- cPanel database password
define('DB_CHARSET', 'utf8mb4');

// ---------- Fallback for local testing without MySQL ----------
// If MySQL cannot be reached, the app automatically uses a local
// SQLite file so pages still run (useful for developers only).
// On the live server MySQL will always be available.
define('SQLITE_FILE', __DIR__ . '/../database.sqlite');

// ---------- Site defaults (overridden by `settings` table) ----------
define('SITE_NAME_DEFAULT', 'Pallottine Nigerian Youth');
define('SITE_TAGLINE_DEFAULT', 'The Love of Christ Impels Us — 2 Cor 5:14');
define('ADMIN_EMAIL_DEFAULT', 'info@pallottineyouthnig.com');

// ---------- Upload limits ----------
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

// ---------- Pagination ----------
define('POSTS_PER_PAGE', 9);
define('PRODUCTS_PER_PAGE', 12);

// ---------- Currency ----------
define('APP_CURRENCY', '₦');   // (named APP_* to avoid colliding with PHP built-ins)
define('APP_CURRENCY_CODE', 'NGN');
