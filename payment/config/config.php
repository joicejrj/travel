<?php
require_once __DIR__."/../../config/db.php";
// ─────────────────────────────────────────────────────────────────────────────
// config/config.php  –  Edit this file before running the app
// ─────────────────────────────────────────────────────────────────────────────

// ── Checkout.com Credentials ─────────────────────────────────────────────────
define('CKO_SECRET_KEY',    'sk_sbox_etiz6cd7bircxbarox4rk2c6k4=');   // Server-side secret key
define('CKO_PUBLIC_KEY',    'pk_sbox_kzy5rauqyxf5bzlsibo2ey7p74k');   // Client-side public key
define('CKO_PROCESSING_CHANNEL', 'pc_gcrttokkv3ee7oybl6jr4syqri');

// ── Checkout.com API ──────────────────────────────────────────────────────────
// Replace {prefix} with your actual sandbox prefix (e.g. "abc" → abc.api.sandbox.checkout.com)
define('CKO_API_PREFIX',    'f4ov36c6');
define('CKO_API_BASE',      'https://' . CKO_API_PREFIX . '.api.sandbox.checkout.com');

// ── Application URLs ─────────────────────────────────────────────────────────
$isLocal = in_array($_SERVER['HTTP_HOST'], ['localhost','127.0.0.1']);
if($isLocal){
    define('APP_BASE_URL', 'http://' . $_SERVER['HTTP_HOST'] . '/jrj/travel/payment');
}else{

    define('APP_BASE_URL', 'https://travel.jrjapp.com/payment');
}
// define('APP_BASE_URL',      'https://travel.jrjapp.com/checkout_payment');   // No trailing slash
define('SUCCESS_URL',       APP_BASE_URL . '/result.php?status=success');
define('FAILURE_URL',       APP_BASE_URL . '/result.php?status=failure');
define('CANCEL_URL',        APP_BASE_URL . '/result.php?status=cancel');

// ── MySQL ─────────────────────────────────────────────────────────────────────
// define('DB_HOST',   'localhost');
// define('DB_PORT',   3306);
// define('DB_NAME',   'checkout_payment');
// define('DB_USER',   'admin_checkout');
// define('DB_PASS',   'WyklXz87Yseeo$?8');
define('DB_CHARSET','utf8mb4');

// ── App Settings ──────────────────────────────────────────────────────────────
// define('APP_NAME',      'Checkout Sandbox Tester');
define('APP_CURRENCY',  'GBP');   // Default currency (ISO 4217)
define('APP_ENV',       'sandbox');
define('DEBUG_MODE',    true);    // Set false in production
