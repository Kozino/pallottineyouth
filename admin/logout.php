<?php
require_once __DIR__ . '/../config/functions.php';
unset($_SESSION['admin_id']);
session_regenerate_id(true);
header('Location: index.php');
exit;
