<?php
require_once __DIR__ . '/../config/db.php';

function storage_dir() {
  $d = __DIR__ . '/../storage'; if (!is_dir($d)) mkdir($d, 0775, true); return realpath($d);
}
function token_path() { return storage_dir() . '/token.json'; }

function fetch_access_token() {
  $url = AMADEUS_BASE_URL . '/v1/security/oauth2/token';
  $payload = http_build_query([
    'grant_type'=>'client_credentials',
    'client_id'=>AMADEUS_CLIENT_ID,
    'client_secret'=>AMADEUS_CLIENT_SECRET
  ]);
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>30,
    CURLOPT_CUSTOMREQUEST=>'POST',
    CURLOPT_HTTPHEADER=>['Content-Type: application/x-www-form-urlencoded'],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_POSTFIELDS=>$payload
  ]);
  $resp = curl_exec($ch); $http = curl_getinfo($ch, CURLINFO_HTTP_CODE); $err = curl_error($ch); curl_close($ch);
  if ($err) throw new Exception("Auth error: $err");
  $data = json_decode($resp, true);
  if ($http >= 400 || empty($data['access_token'])) throw new Exception('Auth failed: ' . $resp);
  $data['obtained_at'] = time(); $data['expires_at'] = time() + (int)$data['expires_in'] - 30;
  file_put_contents(token_path(), json_encode($data, JSON_PRETTY_PRINT));
  return $data['access_token'];
}

function get_access_token() {
  $p = token_path();
  if (file_exists($p)) {
    $d = json_decode(file_get_contents($p), true);
    if (!empty($d['access_token']) && time() < (int)($d['expires_at'] ?? 0)) return $d['access_token'];
  }
  return fetch_access_token();
}

function amadeus_request($method, $endpoint, $body=null, $query=[]) {
  $token = get_access_token();
  return amadeus_request_with_token($method, $endpoint, $body, $query, $token, true);
}

function amadeus_request_with_token($method, $endpoint, $body, $query, $token, $retry) {
  $url = AMADEUS_BASE_URL . $endpoint;
  if (!empty($query)) $url .= (strpos($url,'?')!==false?'&':'?') . http_build_query($query);
  $headers = ['Authorization: Bearer '.$token];
  $payload = null;
  if ($method === 'POST') {
    $headers[] = 'Content-Type: application/json';
    $payload = is_array($body) ? json_encode($body) : (string)$body;
  }
  $ch = curl_init($url);
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER=>true, CURLOPT_TIMEOUT=>60,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_CUSTOMREQUEST=>$method, CURLOPT_HTTPHEADER=>$headers
  ]);
  if ($method === 'POST') curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
  $resp = curl_exec($ch);
  $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);
  if ($err) throw new Exception("cURL error: $err");
  $decoded = json_decode($resp);
  $expired = ($http==401 and isset($decoded->errors[0]->code) and (string)$decoded->errors[0]->code==='38192');
  if ($expired && $retry) { $new = fetch_access_token(); return amadeus_request_with_token($method,$endpoint,$body,$query,$new,false); }
  if ($http >= 400) throw new Exception("HTTP $http: $resp");
  return json_decode($resp, true);
}
?>
