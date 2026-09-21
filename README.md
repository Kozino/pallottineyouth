# Pallottine Nigerian Youth — pallottineyouthnig.com

A complete website + shop + membership system built with **HTML, CSS, JavaScript, PHP & MySQL**.
**No base URL** — every link is relative, so the same code runs on localhost and on
`https://pallottineyouthnig.com` with zero changes.

Inspired by [churchlifeafrica.org](https://churchlifeafrica.org),
[pallottineyouth.com](https://pallottineyouth.com) and
[catholicapostolatecenter.org](https://www.catholicapostolatecenter.org).

---

## ✨ What's Inside

### Public Website
| Page | File | Description |
|---|---|---|
| Home | `index.php` | Hero, bank-transfer card, apostolates, featured shop items, news, events |
| About | `about.php` | Charism, mission/vision, St. Vincent Pallotti |
| Apostolates | `programs.php` | 6 pillars: prayer, formation, charity, leadership, fellowship, media |
| News & Blog | `news.php` + `news-single.php` | Filterable, paginated, managed from admin |
| Events | `events.php` | Upcoming + past events |
| **Shop** | `shop.php` + `product.php` | Categories, search, product pages, AJAX add-to-cart |
| Cart | `cart.php` | Update qty / remove, stock-aware |
| **Checkout** | `checkout.php` | **Bank transfer + receipt upload** (JPG/PNG) |
| Order success / Track | `order-success.php`, `track-order.php` | Order code + live status tracking |
| **Apply / Join** | `apply.php` + `apply-success.php` | Membership application + passport upload |
| Contact | `contact.php` | Contact form + bank details + WhatsApp links |
| Donate | `donate.php` | Bank-transfer giving page |
| Live chat bubble | (in footer) | WhatsApp + email + quick message form (lands in admin inbox) |

### Admin Panel (`/admin/`)
| Area | File | What the admin sees/does |
|---|---|---|
| Login | `admin/index.php` | Secure login (hashed passwords, sessions) |
| **Dashboard** | `admin/dashboard.php` | Pending orders/apps/messages, revenue, stock watch, latest activity |
| Orders | `admin/orders.php`, `admin/order-view.php` | View receipt image, verify payment, update status (pending → confirmed → shipped → delivered) |
| Applications | `admin/applications.php`, `admin/application-view.php` | Review, accept/reject, internal notes, contact links |
| Products | `admin/products.php`, `admin/product-form.php` | Full CRUD: name, price, discount, stock, category, photo, featured, hide/show |
| News & Blog | `admin/posts.php`, `admin/post-form.php` | Full CRUD with cover images |
| Events | `admin/events.php`, `admin/event-form.php` | Full CRUD |
| Messages | `admin/messages.php` | Contact + live-chat inbox (read/reply/delete) |
| **Settings** | `admin/settings.php` | **Bank details**, delivery fees, contacts, socials, homepage texts — no code needed |
| Admins | `admin/admins.php` | Add/remove admin users, change passwords |

### Payment Flow (Bank Transfer — as requested)
1. Buyer adds items → checkout shows **organization bank details** (editable in Settings).
2. Buyer transfers, then uploads **receipt** + sender name on the same page.
3. Order saved as `pending` with an **order code** (e.g. `PYN-2026-X7K2P9`).
4. Admin opens the order, views the receipt, verifies the credit alert, clicks **Confirmed**.
5. Buyer tracks status anytime on **Track Order** page.
6. No receipt at checkout? Buyer can send it later via **WhatsApp / email / live chat** with the order code.

> Paystack can be added later without rebuilding — the orders table already has a
> `payment_method` column; just add a `paystack` option at checkout.

---

## 🚀 Deploy to pallottineyouthnig.com (cPanel)

1. **Upload** — Copy everything in this folder to `public_html/` (File Manager or FTP).
2. **Database** — cPanel → *MySQL Databases* → create database + user, add user to DB (ALL privileges).
3. **Config** — Edit `config/config.php`: set `DB_HOST` (usually `localhost`), `DB_NAME`, `DB_USER`, `DB_PASS`.
4. **Import** — cPanel → *phpMyAdmin* → select DB → *Import* → choose `database.sql` → Go.
   - *Or* visit `https://pallottineyouthnig.com/install.php` and click **Import automatically**.
5. **Perms** — Make sure `uploads/` and subfolders are writable (755).
6. **Login** — Visit `https://pallottineyouthnig.com/admin/`:
   - Email: `admin@pallottineyouthnig.com` · Password: `admin123`
   - ⚠️ **Change the password immediately** (Admins → Edit). ⚠️ **Delete `install.php`.**
7. **Settings** — Admin → *Settings & Bank*: enter the **real bank account**, phone, WhatsApp, social links.
8. **SSL** — cPanel → *SSL/TLS Status* → enable AutoSSL, then uncomment the HTTPS rules in `.htaccess`.

## 💻 Run Locally (for developers)

No MySQL needed for a quick preview — the app falls back to a local SQLite file:

```bash
cd pallottineyouthnig
php -S localhost:8000
# open http://localhost:8000  ·  admin at http://localhost:8000/admin/
# (admin@pallottineyouthnig.com / admin123)
```

With MySQL: create DB, import `database.sql`, edit `config/config.php`.

## 📁 Structure

```
├── index.php, about.php, programs.php, news*.php, events.php
├── shop.php, product.php, cart.php, checkout.php, order-success.php, track-order.php
├── apply.php, apply-success.php, contact.php, donate.php, 404.php
├── config/      config.php · database.php (MySQL + SQLite fallback) · functions.php
├── includes/    header.php · footer.php (+ live chat bubble)
├── assets/      css/style.css · css/admin.css · js/main.js · js/admin.js · images/
├── admin/       dashboard + orders + applications + products + posts + events + messages + settings + admins
├── uploads/     receipts/ · products/ · posts/ · passports/  (auto-created)
├── database.sql install.php  .htaccess  README.md
```

## 🔒 Security Notes
- All queries use **PDO prepared statements**; all output escaped.
- Passwords hashed with `password_hash()`; admin routes session-guarded.
- CSRF tokens on all forms; uploads restricted to images ≤ 5MB; `uploads/.htaccess` blocks script execution.
- `display_errors` is off by default in `config.php`.

## 🆘 Troubleshooting
| Symptom | Fix |
|---|---|
| "Database connection failed" | Check `config/config.php` credentials; ensure `database.sql` was imported |
| Blank page | Enable `display_errors='1'` temporarily; check PHP ≥ 7.4 |
| Images won't upload | Set `uploads/` to 755 (or 775); check `upload_max_filesize` ≥ 5M |
| 404 on all pages | Ensure files are in `public_html/` directly, not a subfolder |

Built with 💛 for the Pallottine Nigerian Youth — *Caritas Christi Urget Nos.*
