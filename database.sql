-- ============================================================
--  PALLOTTINE NIGERIAN YOUTH — MySQL Schema (production)
--  Domain: pallottineyouthnig.com
--  Import via cPanel > phpMyAdmin > Import.
--  Then edit config/config.php with your DB details.
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ---------- Settings ----------
CREATE TABLE IF NOT EXISTS `settings` (
  `skey`   VARCHAR(100) NOT NULL PRIMARY KEY,
  `svalue` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Admins ----------
CREATE TABLE IF NOT EXISTS `admins` (
  `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`          VARCHAR(150) NOT NULL,
  `email`         VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role`          VARCHAR(30) NOT NULL DEFAULT 'admin',
  `created_at`    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Products (shop items) ----------
CREATE TABLE IF NOT EXISTS `products` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`        VARCHAR(200) NOT NULL,
  `slug`        VARCHAR(220) NOT NULL UNIQUE,
  `category`    VARCHAR(100) NOT NULL DEFAULT 'General',
  `price`       DECIMAL(12,2) NOT NULL DEFAULT 0,
  `old_price`   DECIMAL(12,2) NULL,
  `stock`       INT NOT NULL DEFAULT 0,
  `short_desc`  VARCHAR(255) NULL,
  `description` TEXT NULL,
  `image`       VARCHAR(255) NULL,
  `featured`    TINYINT(1) NOT NULL DEFAULT 0,
  `status`      VARCHAR(20) NOT NULL DEFAULT 'active',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`status`), INDEX (`category`), INDEX (`featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Orders (bank transfer) ----------
CREATE TABLE IF NOT EXISTS `orders` (
  `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `order_code`     VARCHAR(30) NOT NULL UNIQUE,
  `customer_name`  VARCHAR(150) NOT NULL,
  `email`          VARCHAR(150) NOT NULL,
  `phone`          VARCHAR(50) NOT NULL,
  `address`        TEXT NOT NULL,
  `city`           VARCHAR(100) NULL,
  `state`          VARCHAR(100) NULL,
  `items_json`     TEXT NOT NULL,
  `subtotal`       DECIMAL(12,2) NOT NULL DEFAULT 0,
  `delivery_fee`   DECIMAL(12,2) NOT NULL DEFAULT 0,
  `total`          DECIMAL(12,2) NOT NULL DEFAULT 0,
  `payment_method` VARCHAR(30) NOT NULL DEFAULT 'bank_transfer',
  `sender_name`    VARCHAR(150) NULL,
  `receipt_image`  VARCHAR(255) NULL,
  `notes`          TEXT NULL,
  `status`         VARCHAR(20) NOT NULL DEFAULT 'pending',
  `created_at`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`status`), INDEX (`order_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Applications (membership) ----------
CREATE TABLE IF NOT EXISTS `applications` (
  `id`              INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `app_code`        VARCHAR(30) NOT NULL UNIQUE,
  `full_name`       VARCHAR(150) NOT NULL,
  `dob`             VARCHAR(20) NULL,
  `gender`          VARCHAR(20) NULL,
  `phone`           VARCHAR(50) NOT NULL,
  `email`           VARCHAR(150) NOT NULL,
  `address`         TEXT NULL,
  `state`           VARCHAR(100) NULL,
  `diocese`         VARCHAR(150) NULL,
  `parish`          VARCHAR(150) NULL,
  `education`       VARCHAR(150) NULL,
  `membership_type` VARCHAR(100) NULL,
  `why_join`        TEXT NULL,
  `skills`          TEXT NULL,
  `emergency_name`  VARCHAR(150) NULL,
  `emergency_phone` VARCHAR(50) NULL,
  `passport_image`  VARCHAR(255) NULL,
  `status`          VARCHAR(20) NOT NULL DEFAULT 'pending',
  `admin_note`      TEXT NULL,
  `created_at`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`status`), INDEX (`app_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Posts (news + blog) ----------
CREATE TABLE IF NOT EXISTS `posts` (
  `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title`        VARCHAR(250) NOT NULL,
  `slug`         VARCHAR(270) NOT NULL UNIQUE,
  `type`         VARCHAR(20) NOT NULL DEFAULT 'blog',
  `excerpt`      VARCHAR(300) NULL,
  `content`      TEXT NULL,
  `image`        VARCHAR(255) NULL,
  `author`       VARCHAR(150) NULL,
  `status`       VARCHAR(20) NOT NULL DEFAULT 'published',
  `published_at` DATETIME NULL,
  `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`type`), INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Events ----------
CREATE TABLE IF NOT EXISTS `events` (
  `id`          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `title`       VARCHAR(250) NOT NULL,
  `slug`        VARCHAR(270) NOT NULL UNIQUE,
  `venue`       VARCHAR(200) NULL,
  `event_date`  DATE NULL,
  `event_time`  VARCHAR(50) NULL,
  `description` TEXT NULL,
  `image`       VARCHAR(255) NULL,
  `status`      VARCHAR(20) NOT NULL DEFAULT 'upcoming',
  `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ---------- Messages (contact + livechat) ----------
CREATE TABLE IF NOT EXISTS `messages` (
  `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name`       VARCHAR(150) NOT NULL,
  `email`      VARCHAR(150) NULL,
  `phone`      VARCHAR(50) NULL,
  `subject`    VARCHAR(200) NULL,
  `message`    TEXT NOT NULL,
  `source`     VARCHAR(30) NOT NULL DEFAULT 'contact',
  `status`     VARCHAR(20) NOT NULL DEFAULT 'unread',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
--  SEED DATA
-- ============================================================
INSERT INTO `settings` (`skey`, `svalue`) VALUES
('site_name','Pallottine Nigerian Youth'),
('tagline','The Love of Christ Impels Us — 2 Cor 5:14'),
('site_email','info@pallottineyouthnig.com'),
('site_phone','+234 (0) 800 000 0000'),
('site_whatsapp','2348000000000'),
('site_address','Pallottine Community, Nigeria'),
('facebook','https://facebook.com/'),
('instagram','https://instagram.com/'),
('twitter','https://x.com/'),
('youtube','https://youtube.com/'),
('bank_name','GTBank (Guaranty Trust Bank)'),
('bank_account_name','Pallottine Nigerian Youth'),
('bank_account_no','0123456789'),
('bank_branch',''),
('payment_note','After payment, upload your receipt on the checkout page or send it via WhatsApp / email with your ORDER CODE. Your order is confirmed once payment is verified.'),
('delivery_fee_lagos','2000'),
('delivery_fee_outside','3500'),
('hero_title','Reviving Faith, Rekindling Charity, Forming Apostles'),
('hero_subtitle','The youth arm of the Society of the Catholic Apostolate (Pallottines) in Nigeria — helping young people understand, live and share their faith.'),
('announcement','Pallottine Youth Festival 2026 — Registrations now open! Visit the Apply page to join.'),
('about_home','Inspired by St. Vincent Pallotti, we form young apostles for the Church and society through prayer, formation, service and fellowship across Nigeria.')
ON DUPLICATE KEY UPDATE `svalue` = VALUES(`svalue`);

-- Default admin: admin@pallottineyouthnig.com / admin123  (CHANGE AFTER FIRST LOGIN!)
-- Verified bcrypt hash of "admin123":
INSERT INTO `admins` (`name`, `email`, `password_hash`, `role`) VALUES
('Super Admin','admin@pallottineyouthnig.com','$2y$12$m16KS6uaXFUxDTwXPVxJn.czRkB/G1abWUi3R21raxrevCqWxqN0G','super_admin')
ON DUPLICATE KEY UPDATE `email` = VALUES(`email`);

INSERT INTO `products` (`name`,`slug`,`category`,`price`,`old_price`,`stock`,`short_desc`,`description`,`featured`,`status`) VALUES
('Pallottine Youth T-Shirt (White)','pallottine-youth-t-shirt-white','Apparel',6500.00,8000.00,50,'Premium white T-shirt with the Pallottine Youth crest.','Premium white T-shirt with the Pallottine Youth crest. Available in S-XXL (state your size at checkout).',1,'active'),
('Pallottine Youth T-Shirt (Navy)','pallottine-youth-t-shirt-navy','Apparel',6500.00,NULL,50,'Premium navy T-shirt with gold Pallottine Youth print.','Premium navy T-shirt with gold Pallottine Youth print. Available in S–XXL (state your size at checkout).',1,'active'),
('Face Cap — Love of Christ Impels Us','face-cap-love-of-christ-impels-us','Apparel',4500.00,NULL,40,'Adjustable face cap with embroidered motto.','Adjustable face cap with embroidered motto. One size fits all.',0,'active'),
('Hoodie — Apostles of Christ','hoodie-apostles-of-christ','Apparel',15000.00,18000.00,25,'Warm pullover hoodie for programmes and outings.','Warm pullover hoodie for programmes and outings. Available in S–XXL.',1,'active'),
('Rosary (Wooden Beads)','rosary-wooden-beads','Devotional',2500.00,NULL,100,'Hand-finished wooden rosary in a gift pouch.','Hand-finished wooden rosary in a gift pouch. A perfect gift for every young Catholic.',0,'active'),
('St. Vincent Pallotti Novena Booklet','st-vincent-pallotti-novena-booklet','Books',1500.00,NULL,200,'Novena, prayers and life of St. Vincent Pallotti.','Novena, prayers and a short life of St. Vincent Pallotti. Ideal for personal and group devotion.',1,'active'),
('Daily Prayer & Reflection Journal','daily-prayer-reflection-journal','Books',3500.00,NULL,80,'Guided Catholic journal for young apostles.','Guided Catholic journal for young apostles — daily examen, intentions and reflections.',0,'active'),
('Wristband — Caritas Christi','wristband-caritas-christi','Accessories',1000.00,NULL,300,'Silicone wristband, pack of one.','Silicone wristband with Caritas Christi Urget Nos. Pack of one.',0,'active');

INSERT INTO `posts` (`title`,`slug`,`type`,`excerpt`,`content`,`author`,`status`,`published_at`) VALUES
('Welcome to Pallottine Nigerian Youth','welcome-to-pallottine-nigerian-youth','news','Our new home on the web — news, formation, programmes and our shop in one place.','Our new home on the web — news, formation, programmes and our shop in one place.\n\nExplore the apostolates, apply for membership, shop branded items and follow our stories. Everything is managed by our team through a dedicated admin dashboard.','Admin','published',NOW()),
('Pallottine Youth Festival 2026: What to Expect','pallottine-youth-festival-2026-what-to-expect','news','Music, Mass, adoration, workshops and fellowship — everything you need to know.','Music, Mass, adoration, workshops and fellowship — everything you need to know.\n\nDates, venue and registration details will be announced here and on our events page. Start preparing your parish and campus groups!','Admin','published',NOW()),
('Who Was St. Vincent Pallotti?','who-was-st-vincent-pallotti','blog','The saint of boundless charity and founder of the Union of Catholic Apostolate.','The saint of boundless charity and founder of the Union of Catholic Apostolate.\n\nSt. Vincent Pallotti (1795–1850) believed every Christian is called to be an apostle. His motto — The love of Christ impels us (2 Cor 5:14) — drives everything we do as Pallottine youth.','Admin','published',NOW()),
('5 Ways to Live as a Young Apostle on Campus','5-ways-to-live-as-a-young-apostle-on-campus','blog','Practical steps for students who want to evangelise with joy.','Practical steps for students who want to evangelise with joy.\n\n1. Start with daily prayer. 2. Join or form a campus fellowship. 3. Serve the poor around you. 4. Share wholesome Catholic content online. 5. Invite a friend to Mass. Small acts, great apostolate!','Admin','published',NOW());

INSERT INTO `events` (`title`,`slug`,`venue`,`event_date`,`event_time`,`description`,`status`) VALUES
('Pallottine Youth Festival 2026','pallottine-youth-festival-2026','Pallottine Retreat Centre, Lagos','2026-12-12','9:00 AM','A gathering of Pallottine youths from across Nigeria — Mass, talks, sports, concert and awards night.','upcoming');
