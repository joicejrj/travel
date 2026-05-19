<?php
function esc($s){ return htmlspecialchars((string)$s ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function post($k,$d=null){ return $_POST[$k] ?? $d; }
function system_kv_get(mysqli $db, string $key){
  $stmt=$db->prepare("SELECT v FROM system_kv WHERE k=? LIMIT 1");
  $stmt->bind_param("s",$key); $stmt->execute(); $stmt->bind_result($v); 
  return $stmt->fetch() ? $v : null;
}
function human_dt($ts){
  if(!$ts) return '—';
  try{ $dt = new DateTime($ts); return $dt->format('Y-m-d H:i'); } catch(Exception $e){ return $ts; }
}
