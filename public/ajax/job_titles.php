<?php
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

/* ============================================================
   1) FETCH ALL JOB TITLES
   ============================================================ */
if ($action === 'fetch') {
    $res = $mysqli->query("SELECT id, title FROM job_titles ORDER BY title ASC");
    $titles = [];
    while ($r = $res->fetch_assoc()) {
        $titles[] = [
            'id'    => $r['id'],
            'title' => $r['title']
        ];
    }
    echo json_encode(['success' => true, 'data' => $titles]);
    exit;
}

/* ============================================================
   2) ADD NEW TITLE
   ============================================================ */
if ($action === 'add') {
    $title = trim($_POST['title'] ?? '');

    if ($title === '') {
        echo json_encode(['success' => false, 'error' => 'Title required']);
        exit;
    }

    // Check duplicate
    $stmt = $mysqli->prepare("SELECT id FROM job_titles WHERE title = ?");
    $stmt->bind_param("s", $title);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Already exists']);
        $stmt->close();
        exit;
    }
    $stmt->close();

    // Insert new job title
    $ins = $mysqli->prepare("INSERT INTO job_titles (title, created_at) VALUES (?, NOW())");
    $ins->bind_param("s", $title);
    $ok = $ins->execute();
    $insertId = $ins->insert_id;
    $ins->close();

    echo json_encode([
        'success' => (bool)$ok,
        'id'      => $insertId,
        'title'   => $title
    ]);
    exit;
}

/* ============================================================
   3) UPDATE / RENAME TITLE
   ============================================================ */
if ($action === 'update') {
    $id    = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');

    if ($id <= 0 || $title === '') {
        echo json_encode(['success' => false, 'error' => 'Invalid data']);
        exit;
    }

    // check duplicate (except this ID)
    $chk = $mysqli->prepare("SELECT id FROM job_titles WHERE title = ? AND id != ?");
    $chk->bind_param("s", $title);
    $chk->execute();
    $chk->store_result();
    if ($chk->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Already exists']);
        $chk->close();
        exit;
    }
    $chk->close();

    $stmt = $mysqli->prepare("UPDATE job_titles SET title=? WHERE id=?");
    $stmt->bind_param("si", $title, $id);
    $ok = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => (bool)$ok, 'id' => $id, 'title' => $title]);
    exit;
}

/* ============================================================
   4) DELETE TITLE
   ============================================================ */
if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid ID']);
        exit;
    }

    $del = $mysqli->prepare("DELETE FROM job_titles WHERE id = ?");
    $del->bind_param("i", $id);
    $ok = $del->execute();
    $del->close();

    echo json_encode(['success' => (bool)$ok, 'id' => $id]);
    exit;
}

/* ============================================================
   DEFAULT: INVALID ACTION
   ============================================================ */
echo json_encode(['success' => false, 'error' => 'Invalid action']);
exit;
?>