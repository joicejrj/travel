<?php
header('Content-Type: application/json; charset=utf-8');

// bootstrap your app DB connection
require_once __DIR__ . '/../../config/db.php'; // should provide $mysqli (mysqli connection)
if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection not available']);
    exit;
}

// read input (allow POST or GET)
$contact_id = isset($_REQUEST['contact_id']) ? intval($_REQUEST['contact_id']) : 0;
$offset = isset($_REQUEST['offset']) ? max(0, intval($_REQUEST['offset'])) : 0;
$limit = isset($_REQUEST['limit']) ? min(200, max(1, intval($_REQUEST['limit']))) : 20; // cap limit to 200

// Read contact_type (string). If not provided, default to 'Employee'
$contact_type = isset($_REQUEST['contact_type']) && trim((string)$_REQUEST['contact_type']) !== '' 
                ? (string)$_REQUEST['contact_type'] 
                : 'Employee';

// normalize whitespace
$contact_type = trim($contact_type);

if ($contact_id <= 0) {
    echo json_encode(['messages' => [], 'hasMore' => false]);
    exit;
}

// we'll fetch one extra row to determine hasMore
$fetchLimit = $limit + 1;

// Use case-insensitive compare: compare LOWER(COALESCE(contact_type,'')) = LOWER(?)
// Note: bind types: contacts_id (i), contact_type (s), fetchLimit (i), offset (i)
$sql = "SELECT id, contacts_id, contact_type, direction, message_body, interactive_reply_title, interactive_reply_description, msg_type,
               media_fileUrl, document_fileUrl, document_caption, date_added
        FROM whatsapp_message_logs
        WHERE contacts_id = ?
          AND LOWER(COALESCE(contact_type, '')) = LOWER(?)
        ORDER BY date_added DESC, id DESC
        LIMIT ? OFFSET ?";

if (!$stmt = $mysqli->prepare($sql)) {
    http_response_code(500);
    echo json_encode(['error' => 'Prepare failed: ' . $mysqli->error]);
    exit;
}

// bind params: i = int, s = string
if (!$stmt->bind_param('isii', $contact_id, $contact_type, $fetchLimit, $offset)) {
    http_response_code(500);
    echo json_encode(['error' => 'Bind params failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

if (!$stmt->execute()) {
    http_response_code(500);
    echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
    $stmt->close();
    exit;
}

$res = $stmt->get_result();
$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}
$stmt->close();

// determine hasMore
$hasMore = false;
if (count($rows) > $limit) {
    $hasMore = true;
    // remove the extra row used for detection
    array_pop($rows);
}

// map/normalize rows to a safe output structure
$messages = array_map(function($r){
    $dir = isset($r['direction']) ? (string)$r['direction'] : '';
    $dirLower = strtolower($dir);
    if (in_array($dirLower, ['in', 'incoming', 'inbound', 'receive', 'received'])) {
        $direction = 'Incoming';
    } elseif (in_array($dirLower, ['out', 'outgoing', 'outbound', 'sent'])) {
        $direction = 'Outgoing';
    } else {
        $direction = ucfirst($dir);
    }

    $date_added = isset($r['date_added']) ? $r['date_added'] : null;
    if ($date_added) {
        $ts = strtotime($date_added);
        $date_added = $ts ? date('Y-m-d H:i:s', $ts) : $date_added;
    } else {
        $date_added = null;
    }

    return [
        'id' => (int)$r['id'],
        'contacts_id' => (int)$r['contacts_id'],
        'contact_type' => isset($r['contact_type']) ? $r['contact_type'] : '',
        'direction' => $direction,
        'message_body' => isset($r['message_body']) ? $r['message_body'] : '',
        'interactive_reply_title' => isset($r['interactive_reply_title']) ? $r['interactive_reply_title'] : '',
        'interactive_reply_description' => isset($r['interactive_reply_description']) ? $r['interactive_reply_description'] : '',
        'msg_type' => isset($r['msg_type']) ? $r['msg_type'] : '',
        'media_fileUrl' => isset($r['media_fileUrl']) ? $r['media_fileUrl'] : '',
        'document_fileUrl' => isset($r['document_fileUrl']) ? $r['document_fileUrl'] : '',
        'document_caption' => isset($r['document_caption']) ? $r['document_caption'] : '',
        'date_added' => $date_added
    ];
}, $rows);

// Return newest-first (client sorts to chronological if needed)
echo json_encode([
    'messages' => $messages,
    'hasMore' => (bool)$hasMore
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

exit;
