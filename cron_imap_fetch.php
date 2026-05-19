<?php
// cron_imap_fetch.php
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);
error_reporting(E_ALL);

register_shutdown_function(function(){
    $err = error_get_last();
    if ($err) {
        $msg = "Shutdown: " . print_r($err, true);
        error_log($msg);
        echo "<pre>Fatal error: " . htmlspecialchars($err['message']) . " in " . htmlspecialchars($err['file']) . " on line " . intval($err['line']) . "</pre>";
    }
});

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/inbound/imap_to_db.php';

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

// Query active mailboxes; this query expects you already have sent_* columns available
$q = $mysqli->query("SELECT id, host, username, password, folder_inbox, folder_sent,
                            sent_host, sent_username, sent_password, sent_folder_inbox
                     FROM mailboxes WHERE active=1");

while ($mb = $q->fetch_assoc()) {
    $mailbox_id = (int)$mb['id'];

    // 1) Primary account: fetch Inbox (UNSEEN) -> mark seen after processing
    $primaryHost = $mb['host'];
    $primaryUser = $mb['username'];
    $primaryPass = $mb['password'];
    $inboxFolder = $mb['folder_inbox'] ?: 'INBOX';
    $imapStream = open_imap_box($primaryHost, $inboxFolder, $primaryUser, $primaryPass);
    if ($imapStream) {
        // Search for UNSEEN only to reduce load
        $emails = imap_search($imapStream, 'UNSEEN') ?: [];
        rsort($emails); // newest first (optional)
        foreach ($emails as $num) {
            // fetch header+body raw (use existing processing that handles attachments)
            $header = imap_fetchheader($imapStream, $num);
            $body = imap_body($imapStream, $num, FT_PEEK);
            $raw = $header . "\r\n" . $body;
            process_raw_email_to_db($raw, $mysqli, [
                'mailbox_id' => $mailbox_id,
                'source_folder' => $inboxFolder,
                'imap_stream' => $imapStream,
                'imap_msgno' => $num,
                'is_sent' => 0,            // inbound
                'sent_via' => 'primary'
            ]);
            // mark as seen for inbox messages
            @imap_setflag_full($imapStream, (string)$num, "\\Seen");
        }
        imap_close($imapStream);
    }

    // 2) Sent-account (optional): if configured, log in and read its INBOX (or configured sent_folder_inbox)
    if (!empty($mb['sent_username']) && !empty($mb['sent_password'])) {
        $sentHost = $mb['sent_host'] ?: $mb['host']; // fallback to primary host if not set
        $sentUser = $mb['sent_username'];
        $sentPass = $mb['sent_password'];
        $sentFolder = $mb['sent_folder_inbox'] ?: 'INBOX';
        $sentStream = open_imap_box($sentHost, $sentFolder, $sentUser, $sentPass);
        if ($sentStream) {
            // We fetch ALL messages from the sent-account INBOX; dedupe will skip duplicates by Message-ID
            $emails = imap_search($sentStream, 'ALL') ?: [];
            rsort($emails);
            foreach ($emails as $num) {
                $header = imap_fetchheader($sentStream, $num);
                $body = imap_body($sentStream, $num, FT_PEEK);
                $raw = $header . "\r\n" . $body;
                process_raw_email_to_db($raw, $mysqli, [
                    'mailbox_id' => $mailbox_id,
                    'source_folder' => $sentFolder,
                    'imap_stream' => $sentStream,
                    'imap_msgno' => $num,
                    'is_sent' => 1,          // mark as sent
                    'sent_via' => 'sent_account'
                ]);
                // do NOT change flags on the sent account by default
            }
            imap_close($sentStream);
        }
    } else {
        // If no sent-account, still attempt to get sent messages from folder_sent (on primary) if configured
        if (!empty($mb['folder_sent'])) {
            $sentFolderOnPrimary = $mb['folder_sent'];
            $imapStream2 = open_imap_box($primaryHost, $sentFolderOnPrimary, $primaryUser, $primaryPass);
            if ($imapStream2) {
                $emails = imap_search($imapStream2, 'ALL') ?: [];
                rsort($emails);
                foreach ($emails as $num) {
                    $header = imap_fetchheader($imapStream2, $num);
                    $body = imap_body($imapStream2, $num, FT_PEEK);
                    $raw = $header . "\r\n" . $body;
                    process_raw_email_to_db($raw, $mysqli, [
                        'mailbox_id' => $mailbox_id,
                        'source_folder' => $sentFolderOnPrimary,
                        'imap_stream' => $imapStream2,
                        'imap_msgno' => $num,
                        'is_sent' => 1,
                        'sent_via' => 'sent_folder_primary'
                    ]);
                }
                imap_close($imapStream2);
            }
        }
    }
}
