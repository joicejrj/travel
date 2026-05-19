<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['user'])) { header('Location: ?page=login'); exit; }
$CURRENT_USER_ID = (int)$_SESSION['user']['id'];
$CURRENT_USER_NAME = $_SESSION['user']['name'] ?? '';
// if (!function_exists('esc')) { function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
?>