<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
// require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

// Pagination parameters
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

$id = (int)($_GET['id'] ?? 0);

$response = [
    'emails' => [],
    'hasMore' => false
];

if(!($id > 0)) {
    echo json_encode($response);
    exit();
}
$gete = $db->get('recruiters',array('id'=>$id),'fil_domains,fil_emails');


$domains = array_filter(array_map('trim', explode(',', $gete->fil_domains ?? '')));
$emails  = array_filter(array_map('trim', explode(',', $gete->fil_emails ?? '')));

if(empty($emails)) { // empty($domains)
    echo json_encode($response);
    exit();
}

$mailbox_id = isset($_SESSION['person_mailbox_id'])?$_SESSION['person_mailbox_id']:0;

$cond = array('#all'=>1, '#show'=>1,'mailbox_id'=>$mailbox_id, '#limit' => $limit,'#page' => $page - 1,'#srt' => 'id DESC');
$cond["#cus"] = " (1=2) ";

// exact email match
if (!empty($emails)) {
    $in = implode(',',$emails);
    $in_esc = $mysqli->real_escape_string($in);
    // $cond["#cus"] .= " or ( (is_sent=0 and from_email IN ('$in')) or (is_sent=1 and to_emails IN ('$in')) )";
    $cond["#cus"] .= " or ( ( ((LOWER(folder) = 'inbox' OR folder IS NULL OR folder = '') AND (sent_via IS NULL OR LOWER(sent_via) <> 'sent_account')) and from_email IN ('$in')) or ( ((LOWER(folder) = 'sent' OR LOWER(sent_via) = 'mailer' OR LOWER(sent_via) = 'sent_account')) and to_emails IN ('$in')) or body_text LIKE '%$in_esc%' )";
}

// domain match
if (!empty($domains)) {
    foreach ($domains as $d) {
        $cond["#cus"] .= " or ( ( ((LOWER(folder) = 'inbox' OR folder IS NULL OR folder = '') AND (sent_via IS NULL OR LOWER(sent_via) <> 'sent_account')) and from_email LIKE '%@$d') or ( (LOWER(folder) = 'sent' OR LOWER(sent_via) = 'mailer' OR LOWER(sent_via) = 'sent_account') and to_emails LIKE '%@$d') )";
    }
}

// Fetch paginated emails
$emails = $db->get('email_log',$cond,"*, CASE  WHEN  (LOWER(folder) = 'inbox' OR folder IS NULL OR folder = '') AND (sent_via IS NULL OR LOWER(sent_via) <> 'sent_account') THEN 'inbound' ELSE 'outbound' END AS type");
// ((LOWER(folder) = 'sent' OR LOWER(sent_via) = 'mailer' OR LOWER(sent_via) = 'sent_account'))

// If data exists
if ($emails && !empty($emails->data)) {
    foreach ($emails->data as $e) {
        $response['emails'][] = [
            'id' => $e->id,
            'subject' => $e->subject,
            'from' => $e->from_email,
            'to' => $e->to_emails,
            'date' => date('d M Y h:i A', strtotime($e->created_at)),
            // 'direction' => ($e->is_sent==1?'outbound':'inbound')
            'direction' => $e->type
        ];
    }

    // Check if more pages exist
    if (isset($emails->pages) && isset($emails->current)) {
        $response['hasMore'] = ($emails->current + 1) < $emails->pages;
    }
}

echo json_encode($response);
?>