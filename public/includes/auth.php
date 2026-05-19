<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/../../config/db.php';
function current_user(){ return $_SESSION['user'] ?? null; }
function require_login(){ if (!current_user()){ header("Location: /index.php"); exit; } }
