<?php
// public/download_attachment.php
// Secure download endpoint for attachments stored in DB.
// Usage: download_attachment.php?src=email&id=123   (src=email|inbound)

ini_set('display_errors', 0);
error_reporting(E_ALL);

// Adjust path to your app's include if necessary
require_once __DIR__ . '/includes/header.php';
require_login(); // ensure user is logged in

// Optionally restrict to admin only:
// if (!function_exists('is_admin') || !is_admin()) { http_response_code(403); echo "Forbidden"; exit; }

require_once __DIR__ . '/../config/db.php';

// Simple logger helper (same log file used by imap module)
function dl_log($msg){
  $f = __DIR__ . '/../storage/attachment_downloads.log';
  @file_put_contents($f, "[".date('c')."] $msg\n", FILE_APPEND);
}

// sanitize input
$src = isset($_GET['src']) ? strtolower(trim($_GET['src'])) : 'email';
if (!in_array($src, ['email','inbound'], true)) $src = 'email';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  http_response_code(400);
  echo "Invalid id";
  exit;
}

// choose table and query
if ($src === 'inbound') {
  // inbound attachments table (if you use inbound_attachments)
  // fallback to email_attachments with email_id = inbound_id will be handled by email_view.php logic
  // Here we expect inbound_attachments table with columns: id, inbound_email_id, filename, path
  $sql = "SELECT id, filename, path FROM inbound_attachments WHERE id = ? LIMIT 1";
} else {
  // email attachments table: columns id, email_id, filename, path
  $sql = "SELECT id, filename, path FROM email_attachments WHERE id = ? LIMIT 1";
}

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
  dl_log("DB prepare failed: " . $mysqli->error . " SQL: " . $sql);
  http_response_code(500);
  echo "Server error";
  exit;
}
$stmt->bind_param('i', $id);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$stmt->close();

if (!$row) {
  http_response_code(404);
  echo "Attachment not found";
  exit;
}

$filepath = $row['path'] ?? '';
$filename = $row['filename'] ?? 'attachment';

// Prevent path traversal attacks by ensuring the resolved path is within allowed storage dir(s)
$real = realpath($filepath);
if ($real === false || !file_exists($real)) {
  dl_log("File missing for attachment id {$id} src={$src} path={$filepath}");
  http_response_code(404);
  echo "File not found";
  exit;
}

// Optionally restrict to attachments stored inside a defined attachments root
// For safety define allowed roots (adjust as needed)
$allowed_roots = [
  realpath(__DIR__ . '/../storage/mail_attachments'),
  realpath(__DIR__ . '/../storage'), // if some attachments stored directly under storage
];
$okRoot = false;
foreach ($allowed_roots as $root) {
  if ($root && strpos($real, $root) === 0) { $okRoot = true; break; }
}
if (!$okRoot) {
  // If the file is outside allowed roots, block download
  dl_log("Attachment outside allowed roots: {$real}");
  http_response_code(403);
  echo "Forbidden";
  exit;
}

// Determine mime type
$mime = 'application/octet-stream';
if (function_exists('mime_content_type')) {
  $m = @mime_content_type($real);
  if ($m) $mime = $m;
} elseif (function_exists('finfo_open')) {
  $finfo = @finfo_open(FILEINFO_MIME_TYPE);
  if ($finfo) {
    $m = @finfo_file($finfo, $real);
    if ($m) $mime = $m;
    @finfo_close($finfo);
  }
}

// Serve the file with appropriate headers
// Clear output buffers
while (ob_get_level()) ob_end_clean();

$basename = basename($filename);
$filesize = filesize($real);

// Some browsers may behave better with attachment names encoded; use RFC5987 for non-ascii:
$disposition = "attachment; filename=\"" . str_replace('"', '', $basename) . "\"";
$utf8name = rawurlencode($basename);

header('Content-Description: File Transfer');
header('Content-Type: ' . $mime);
header('Content-Disposition: ' . $disposition . '; filename*=UTF-8\'\'' . $utf8name);
header('Content-Transfer-Encoding: binary');
header('Content-Length: ' . $filesize);
header('Expires: 0');
header('Cache-Control: private, must-revalidate');
header('Pragma: public');

// log the download
dl_log("Serving attachment id {$id} ({$basename}) size={$filesize} mime={$mime} src={$src} by user_id=" . ($_SESSION['user_id'] ?? 'n/a'));

// Output the file
$fp = fopen($real, 'rb');
if ($fp) {
  // stream in chunks to avoid high memory use
  $chunkSize = 8192;
  while (!feof($fp)) {
    echo fread($fp, $chunkSize);
    // flush to client
    @flush();
    @ob_flush();
  }
  fclose($fp);
}
exit;
