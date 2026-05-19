<?php
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (empty($_SESSION['person_id'])) { header('Location: ?page=login'); exit; }
$CURRENT_USER_ID = (int)$_SESSION['person_id'];
$CURRENT_USER_NAME = $_SESSION['person_name'] ?? '';
if (!function_exists('esc')) { function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); } }
?>