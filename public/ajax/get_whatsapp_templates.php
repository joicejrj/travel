<?php
// ajax/get_whatsapp_templates.php
header('Content-Type: application/json; charset=utf-8');

// bootstrap your app DB connection
// adjust path if your config is elsewhere
require_once __DIR__ . '/../../config/db.php'; // expected to provide $mysqli (mysqli instance)

if (!isset($mysqli) || !($mysqli instanceof mysqli)) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection not available']);
    exit;
}

// --- read request params ---
$q = isset($_REQUEST['q']) ? trim($_REQUEST['q']) : '';
$offset = isset($_REQUEST['offset']) ? max(0, intval($_REQUEST['offset'])) : 0;
$limit = isset($_REQUEST['limit']) ? min(500, max(1, intval($_REQUEST['limit']))) : 100; // cap limit

try {

    if ($q !== '') {
        // search mode: search name or content
        $like = '%' . $q . '%';
        $sql = "SELECT id, name, tmp_id, content, date_added
                FROM whatsapp_templates
                WHERE name LIKE ? OR content LIKE ?
                ORDER BY date_added DESC, id DESC
                LIMIT ? OFFSET ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) throw new Exception('Prepare failed: ' . $mysqli->error);

        // bind: two strings then two integers
        $stmt->bind_param('ssii', $like, $like, $limit, $offset);
    } else {
        // no search: simple list latest templates
        $sql = "SELECT id, name, tmp_id, content, date_added
                FROM whatsapp_templates
                ORDER BY date_added DESC, id DESC
                LIMIT ? OFFSET ?";
        $stmt = $mysqli->prepare($sql);
        if (!$stmt) throw new Exception('Prepare failed: ' . $mysqli->error);

        $stmt->bind_param('ii', $limit, $offset);
    }

    if (!$stmt->execute()) {
        $err = $stmt->error;
        $stmt->close();
        throw new Exception('Execute failed: ' . $err);
    }

    $res = $stmt->get_result();
    $templates = [];
    while ($row = $res->fetch_assoc()) {
        // format date consistently
        $date_added = null;
        if (!empty($row['date_added'])) {
            $ts = strtotime($row['date_added']);
            $date_added = $ts ? date('Y-m-d H:i:s', $ts) : $row['date_added'];
        }

        $templates[] = [
            'id' => (int)$row['id'],
            'name' => isset($row['name']) ? $row['name'] : '',
            'tmp_id' => isset($row['tmp_id']) ? $row['tmp_id'] : '',
            'content' => isset($row['content']) ? $row['content'] : '',
            'date_added' => $date_added
        ];
    }

    $stmt->close();

    echo json_encode([
        'templates' => $templates
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Exception $ex) {
    http_response_code(500);
    echo json_encode(['error' => $ex->getMessage()]);
    exit;
}
?>