<?php
// imap_debug.php - run on server (browser/CLI) for debugging IMAP Sent folder visibility
ini_set('display_errors',1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';

$mailboxId = 1; // set the mailbox id you want to test (or 0 to use manual below)

// Option A: load from DB
if ($mailboxId) {
    $mb = $mysqli->query("SELECT * FROM mailboxes WHERE id=".(int)$mailboxId)->fetch_assoc();
    if (!$mb) { echo "Mailbox id {$mailboxId} not found\n"; exit; }
    $host = $mb['host'];
    $user = 'dileep_out';
    $pass = $mb['password'];
    $folder_inbox = $mb['folder_inbox'] ?: 'INBOX';
    $folder_sent  = $mb['folder_sent'] ?: 'Sent';
} else {
    // Option B: manual credentials
    $host = 'imap.example.com';   // if you want to use {host:993/imap/ssl} include braces
    $user = 'user@example.com';
    $pass = 'secret';
    $folder_inbox = 'INBOX';
    $folder_sent = 'Sent';
}

// Normalize host string for listing (we will open a base connection)
$host_raw = trim($host);

// show mailboxes available
echo "<pre>";
echo "==== Mailboxes (imap_list) using $host_raw ====\n";
$base = $host_raw;
if (strpos($base, '{') === false) {
    // try basic secure string
    $base = '{' . $base . ':993/imap/ssl/novalidate-cert}';
}
$imap = @imap_open($base, $user, $pass);
if (!$imap) {
    echo "Cannot open base [$base]: " . imap_last_error() . "\n";
} else {
    $boxes = imap_list($imap, $base, '*');
    if ($boxes === false) {
        echo "imap_list failed: " . imap_last_error() . "\n";
    } else {
        foreach ($boxes as $b) echo "BOX: $b\n";
    }
    imap_close($imap);
}

echo "\n==== Trying Sent folder names to see what's accessible ====\n";
$candidates = [$folder_sent, 'Sent', 'Sent Items', 'Sent Messages', 'INBOX.Sent', 'INBOX/Sent', 'Inbox', 'INBOX'];
foreach ($candidates as $cand) {
    $imap_path = (strpos($host_raw,'{')===0) ? rtrim($host_raw,'}') . '}' . $cand : '{' . $host_raw . ':993/imap/ssl/novalidate-cert}' . $cand;
    echo "\n-- checking: [$imap_path]\n";
    $inbox = @imap_open($imap_path, $user, $pass);
    if (!$inbox) {
        echo "  cannot open: " . imap_last_error() . "\n";
        continue;
    }
    echo "  opened OK. Searching ALL...\n";
    $emails = @imap_search($inbox, 'ALL') ?: [];
    echo "  count: " . count($emails) . "\n";
    if (count($emails)) {
        // show last 30 messages overview
        rsort($emails); // newest first
        $slice = array_slice($emails, 0, 30);
        foreach ($slice as $num) {
            $ov = imap_fetch_overview($inbox, $num, 0);
            $hdr = $ov[0];
            $flags = isset($hdr->seen) ? ($hdr->seen ? '\\Seen':'') : '';
            $mid = $hdr->message_id ?? ($hdr->msgno ?? '');
            $date = $hdr->date ?? '';
            $subject = $hdr->subject ?? '(no-subject)';
            echo "  msgno:{$num} date:{$date} id:{$mid} flags:{$flags}\n    subj: ".trim($subject)."\n";
        }
    }
    imap_close($inbox);
}
echo "\n==== Done ====\n</pre>";
