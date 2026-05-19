<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../lib/helpers.php';
$email = trim(post('email','')); $pass = (string)post('password','');
if (!$email || !$pass){ header("Location: /index.php?err=1"); exit; }
$stmt = $mysqli->prepare("SELECT id,name,email,password_hash,role FROM users WHERE email=? LIMIT 1");
$stmt->bind_param("s", $email); $stmt->execute(); $res = $stmt->get_result();
if ($user = $res->fetch_assoc()){ if (password_verify($pass, $user['password_hash'])){ $_SESSION['user'] = ['id'=>(int)$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role']]; header("Location: ?page=users"); exit; } }
header("Location: /index.php?err=1"); exit;
