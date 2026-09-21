<?php
require_once __DIR__ . '/../config/functions.php';
require_admin();
$id = (int)($_GET['id'] ?? 0);
db()->prepare("DELETE FROM events WHERE id=?")->execute([$id]);
flash_set('success', 'Event deleted.');
header('Location: events.php'); exit;
