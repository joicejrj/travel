<?php
// public/ajax_sync.php
header('Content-Type: application/json');
set_time_limit(300);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../inbound/imap_to_db.php';

/**
 * Build a safe IMAP path from a host value stored in DB and a folder name.
 */
function build_imap_path(string $host_value, string $folder): string {
    $h = trim($host_value);

    // If host already contains a {..} block, use that block as base
    if (preg_match('/^\{[^}]+\}/', $h, $m)) {
        $base = $m[0]; // {host:...}
        return $base . $folder;
    }

    // If host looks like host:port/imap or contains /imap or /ssl, wrap in braces
    if (stripos($h, '/imap') !== false || stripos($h, '/ssl') !== false || preg_match('/:\d+\/imap/i', $h)) {
        return '{' . $h . '}' . $folder;
    }

    // Default: assume SSL on 993 and don't validate certs (matches existing behavior)
    return '{' . $h . ':993/imap/ssl/novalidate-cert}' . $folder;
}

/**
 * Safe wrapper for imap_open (returns resource or false). Accepts host string and folder.
 */
function open_imap_box(string $host_value, string $folder, string $user, string $pass) {
    $path = build_imap_path($host_value, $folder);
    $stream = @imap_open($path, $user, $pass, OP_READONLY, 1);
    if (!$stream) {
        error_log("IMAP open failed for {$user}@{$host_value} folder {$folder}: " . imap_last_error());
    }
    return $stream;
}

/**
 * Fetch a "raw" representation of the message header + decoded text body parts.
 * Tries to find and decode text/plain or text/html parts in multipart messages.
 * It intentionally does NOT inline attachments into the returned text; attachments
 * are left for process_raw_email_to_db() to handle with the imap_stream/msgno it receives.
 */
function fetch_full_raw_message($imapStream, int $msgno): string {
    $header = @imap_fetchheader($imapStream, $msgno);
    $structure = @imap_fetchstructure($imapStream, $msgno);

    // fallback: if structure missing, use plain body
    if (!$structure || (empty($structure->parts) && !property_exists($structure, 'parts'))) {
        $body = @imap_body($imapStream, $msgno, FT_PEEK);
        return $header . "\r\n" . $body;
    }

    $decode_part = function($text, $encoding) {
        switch ((int)$encoding) {
            case 3: // BASE64
                return base64_decode($text);
            case 4: // QUOTED-PRINTABLE
                return quoted_printable_decode($text);
            default:
                return $text;
        }
    };

    $convert_charset = function($text, $partObj = null) {
        if (!$partObj) return $text;
        $charset = null;
        if (!empty($partObj->parameters)) {
            foreach ($partObj->parameters as $p) {
                if (isset($p->attribute) && strtolower($p->attribute) === 'charset') {
                    $charset = $p->value;
                    break;
                }
            }
        }
        if ($charset && strtoupper($charset) !== 'UTF-8' && function_exists('mb_convert_encoding')) {
            $converted = @mb_convert_encoding($text, 'UTF-8', $charset);
            if ($converted !== false) return $converted;
        }
        return $text;
    };

    $text_parts = '';

    // recursive walker to handle nested multiparts reliably
    $walker = function($part, $partno = '', &$walker) use ($imapStream, $decode_part, $convert_charset, &$text_parts) {
        // If this part has subparts, iterate them
        if (!empty($part->parts)) {
            foreach ($part->parts as $idx => $subpart) {
                $sub_no = ($partno === '') ? (string)($idx + 1) : ($partno . '.' . ($idx + 1));
                $walker($subpart, $sub_no, $walker);
            }
            return;
        }

        // Determine type/subtype
        $type = isset($part->type) ? (int)$part->type : null; // 0=text, 1=multipart, 2=message, 3=application, 4=image...
        $subtype = isset($part->subtype) ? strtolower($part->subtype) : '';

        // If it's a text part, fetch and decode
        if ($type === 0 && in_array($subtype, ['plain', 'html'])) {
            $raw = @imap_fetchbody($imapStream, $partno === '' ? '1' : $partno, FT_PEEK);
            if ($raw !== false && $raw !== '') {
                $decoded = $decode_part($raw, $part->encoding ?? 0);
                $decoded = $convert_charset($decoded, $part);
                $text_parts .= ($subtype === 'html') ? $decoded . "\r\n" : $decoded . "\r\n";
            }
            return;
        }

        // Non-text (attachments etc) -> ignore here (process_raw_email_to_db will handle attachments)
    };

    $walker($structure, '', $walker);

    if (trim($text_parts) !== '') {
        return $header . "\r\n" . $text_parts;
    }

    // final fallback: top-level body
    $body = @imap_body($imapStream, $msgno, FT_PEEK);
    return $header . "\r\n" . $body;
}

// Response skeleton
$resp = [
    'ok' => false,
    'processed_total' => 0,
    'mailboxes' => [],
    'errors' => []
];

// sanity check for required function
if (!function_exists('process_raw_email_to_db')) {
    http_response_code(500);
    echo json_encode(['ok'=>false, 'error'=>'process_raw_email_to_db not found']);
    exit;
}

// Fetch all active mailboxes (include sent-account columns)
$q = $mysqli->query("SELECT id, name, host, username, password, folder_inbox, folder_sent,
                            sent_host, sent_username, sent_password, sent_folder_inbox
                     FROM mailboxes WHERE active=1");

if (!$q) {
    $resp['errors'][] = "DB error fetching mailboxes: " . $mysqli->error;
    echo json_encode($resp);
    exit;
}

while ($mb = $q->fetch_assoc()) {
    $mailbox_id = (int)$mb['id'];
    $mb_report = [
        'id' => $mailbox_id,
        'name' => $mb['name'] ?? ('mb_'.$mailbox_id),
        'folders' => [],
        'errors' => []
    ];

    // 1️⃣ Primary account — INBOX (UNSEEN only)
    $primaryHost = $mb['host'];
    $primaryUser = $mb['username'];
    $primaryPass = $mb['password'];
    $inboxFolder = $mb['folder_inbox'] ?: 'INBOX';
    $imapStream = open_imap_box($primaryHost, $inboxFolder, $primaryUser, $primaryPass);

    if ($imapStream) {
        $emails = @imap_search($imapStream, 'UNSEEN') ?: [];
        rsort($emails);
        $processed = 0;
        foreach ($emails as $num) {
            try {
                // Use multipart-aware fetch helper
                $raw = fetch_full_raw_message($imapStream, $num);

                process_raw_email_to_db($raw, $mysqli, [
                    'mailbox_id' => $mailbox_id,
                    'source_folder' => $inboxFolder,
                    'imap_stream' => $imapStream,
                    'imap_msgno' => $num,
                    'is_sent' => 0,
                    'sent_via' => 'primary'
                ]);

                @imap_setflag_full($imapStream, (string)$num, "\\Seen");
                $processed++;
                $resp['processed_total']++;
            } catch (Throwable $ex) {
                $err = "Error processing mailbox {$mailbox_id} (INBOX): " . $ex->getMessage();
                error_log($err);
                $mb_report['errors'][] = $err;
            }
        }
        @imap_close($imapStream);
        $mb_report['folders'][] = [
            'folder' => $inboxFolder,
            'processed' => $processed,
            'error' => null
        ];
    } else {
        $mb_report['errors'][] = "Failed to open IMAP INBOX for mailbox {$mailbox_id}";
    }

    // 2️⃣ Sent-account (if configured)
    if (!empty($mb['sent_username']) && !empty($mb['sent_password'])) {
        $sentHost = $mb['sent_host'] ?: $mb['host'];
        $sentUser = $mb['sent_username'];
        $sentPass = $mb['sent_password'];
        $sentFolder = $mb['sent_folder_inbox'] ?: 'INBOX';

        $sentStream = open_imap_box($sentHost, $sentFolder, $sentUser, $sentPass);
        if ($sentStream) {
            $emails = @imap_search($sentStream, 'ALL') ?: [];
            rsort($emails);
            $processed = 0;
            foreach ($emails as $num) {
                try {
                    $raw = fetch_full_raw_message($sentStream, $num);

                    process_raw_email_to_db($raw, $mysqli, [
                        'mailbox_id' => $mailbox_id,
                        'source_folder' => $sentFolder,
                        'imap_stream' => $sentStream,
                        'imap_msgno' => $num,
                        'is_sent' => 1,
                        'sent_via' => 'sent_account'
                    ]);
                    $processed++;
                    $resp['processed_total']++;
                } catch (Throwable $ex) {
                    $err = "Error processing SENT mailbox {$mailbox_id}: " . $ex->getMessage();
                    error_log($err);
                    $mb_report['errors'][] = $err;
                }
            }
            @imap_close($sentStream);
            $mb_report['folders'][] = [
                'folder' => $sentFolder,
                'processed' => $processed,
                'error' => null
            ];
        } else {
            $mb_report['errors'][] = "Failed to open SENT account IMAP for mailbox {$mailbox_id}";
        }
    }
    // 3️⃣ If no separate sent-account, check folder_sent on primary
    elseif (!empty($mb['folder_sent'])) {
        $sentFolderOnPrimary = $mb['folder_sent'];
        $imapStream2 = open_imap_box($primaryHost, $sentFolderOnPrimary, $primaryUser, $primaryPass);
        if ($imapStream2) {
            $emails = @imap_search($imapStream2, 'ALL') ?: [];
            rsort($emails);
            $processed = 0;
            foreach ($emails as $num) {
                try {
                    $raw = fetch_full_raw_message($imapStream2, $num);

                    process_raw_email_to_db($raw, $mysqli, [
                        'mailbox_id' => $mailbox_id,
                        'source_folder' => $sentFolderOnPrimary,
                        'imap_stream' => $imapStream2,
                        'imap_msgno' => $num,
                        'is_sent' => 1,
                        'sent_via' => 'sent_folder_primary'
                    ]);
                    $processed++;
                    $resp['processed_total']++;
                } catch (Throwable $ex) {
                    $err = "Error processing sent-folder {$sentFolderOnPrimary}: " . $ex->getMessage();
                    error_log($err);
                    $mb_report['errors'][] = $err;
                }
            }
            @imap_close($imapStream2);
            $mb_report['folders'][] = [
                'folder' => $sentFolderOnPrimary,
                'processed' => $processed,
                'error' => null
            ];
        } else {
            $mb_report['errors'][] = "Failed to open sent-folder ({$sentFolderOnPrimary}) on primary IMAP for mailbox {$mailbox_id}";
        }
    }

    $resp['mailboxes'][] = $mb_report;
}

$resp['ok'] = true;
echo json_encode($resp, JSON_PRETTY_PRINT);
exit;
