<?php
// includes/header_public.php (CDN test)
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
$page_title = $page_title ?? 'Agent sign in';
?><!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>

  <!-- Bootstrap CSS (CDN) -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="" crossorigin="anonymous">

  <!-- Font Awesome (optional) -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

  <!-- Your app CSS fallback (use absolute path if local) -->
  <link href="/assets/css/app.css" rel="stylesheet">
</head>
<body class="bg-light">
  <main class="container py-5">
