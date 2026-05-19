<?php
// public/ajax/recruiters_sites.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
$recruiter_id = intval($_POST['recruiter_id'] ?? 0);

if ($action === 'fetch') {
    $stmt = $mysqli->prepare("SELECT id, site_name, site_contact, site_address, site_location FROM recruiters_sites WHERE recruiter_id=? ORDER BY id ASC");
    $stmt->bind_param("i", $recruiter_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $sites = [];
    while ($r = $res->fetch_assoc()) {
        $sites[] = $r;
    }
    echo json_encode($sites);
    exit;
}

if ($action === 'create') {
    $site_name = $_POST['site_name'] ?? '';
    $site_contact = $_POST['site_contact'] ?? '';
    $site_address = $_POST['site_address'] ?? '';
    $site_location = $_POST['site_location'] ?? '';
    $created_by = $_SESSION['user_name'] ?? 'Admin';

    $stmt = $mysqli->prepare("INSERT INTO recruiters_sites (recruiter_id, site_name, site_contact, site_address, site_location, created_by) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $recruiter_id, $site_name, $site_contact, $site_address, $site_location, $created_by);
    $ok = $stmt->execute();
    if ($ok) {
        $id = $mysqli->insert_id;
        echo json_encode(['success'=>true, 'site'=> ['id'=>$id,'site_name'=>$site_name,'site_contact'=>$site_contact,'site_address'=>$site_address,'site_location'=>$site_location]]);
    } else echo json_encode(['success'=>false, 'error'=>$mysqli->error]);
    exit;
}

if ($action === 'update') {
    $id = intval($_POST['id'] ?? 0);
    $site_name = $_POST['site_name'] ?? '';
    $site_contact = $_POST['site_contact'] ?? '';
    $site_address = $_POST['site_address'] ?? '';
    $site_location = $_POST['site_location'] ?? '';

    $stmt = $mysqli->prepare("UPDATE recruiters_sites SET site_name=?, site_contact=?, site_address=?, site_location=? WHERE id=? AND recruiter_id=?");
    $stmt->bind_param("ssssii", $site_name, $site_contact, $site_address, $site_location, $id, $recruiter_id);
    $ok = $stmt->execute();
    if ($ok) {
        echo json_encode(['success'=>true, 'site'=> ['id'=>$id,'site_name'=>$site_name,'site_contact'=>$site_contact,'site_address'=>$site_address,'site_location'=>$site_location]]);
    } else echo json_encode(['success'=>false, 'error'=>$mysqli->error]);
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $mysqli->prepare("DELETE FROM recruiters_sites WHERE id=? AND recruiter_id=?");
    $stmt->bind_param("ii", $id, $recruiter_id);
    $ok = $stmt->execute();
    if ($ok) echo json_encode(['success'=>true]);
    else echo json_encode(['success'=>false, 'error'=>$mysqli->error]);
    exit;
}

/* Bulk save: receive JSON array "sites" - each item may have id (update) or empty id (create).
   We'll iterate and apply create/update. We also delete any persisted sites not present in payload.
*/
if ($action === 'save_bulk') {
    $raw = $_POST['sites'] ?? '[]';
    $items = json_decode($raw, true);
    if (!is_array($items)) { echo json_encode(['success'=>false,'error'=>'Invalid payload']); exit; }

    $savedIds = [];
    foreach ($items as $it) {
        $id = intval($it['id'] ?? 0);
        $site_name = $it['site_name'] ?? '';
        $site_contact = $it['site_contact'] ?? '';
        $site_address = $it['site_address'] ?? '';
        $site_location = $it['site_location'] ?? '';
        if ($id > 0) {
            $stmt = $mysqli->prepare("UPDATE recruiters_sites SET site_name=?, site_contact=?, site_address=?, site_location=? WHERE id=? AND recruiter_id=?");
            $stmt->bind_param("ssssii", $site_name, $site_contact, $site_address, $site_location, $id, $recruiter_id);
            $stmt->execute();
            $savedIds[] = $id;
        } else {
            $created_by = $_SESSION['user_name'] ?? 'Admin';
            $stmt = $mysqli->prepare("INSERT INTO recruiters_sites (recruiter_id, site_name, site_contact, site_address, site_location, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $recruiter_id, $site_name, $site_contact, $site_address, $site_location, $created_by);
            $stmt->execute();
            $savedIds[] = $mysqli->insert_id;
        }
    }

    // Optionally remove rows that are not in savedIds (i.e. user deleted them client-side)
    if (count($savedIds)) {
        $placeholders = implode(',', array_fill(0, count($savedIds), '?'));
        // we'll delete where recruiter_id = ? AND id NOT IN (savedIds)
        // Prepared statement building
        $types = str_repeat('i', count($savedIds)+1);
        $sql = "DELETE FROM recruiters_sites WHERE recruiter_id=? AND id NOT IN ($placeholders)";
        $stmt = $mysqli->prepare($sql);
        // bind params dynamically
        $vals = array_merge([$recruiter_id], $savedIds);
        $refArr = [];
        foreach ($vals as $k => $v) $refArr[$k] = &$vals[$k];
        array_unshift($refArr, $types); // first param is types string
        call_user_func_array([$stmt, 'bind_param'], $refArr);
        $stmt->execute();
    } else {
        // no saved ids -> delete all for the recruiter
        $stmt = $mysqli->prepare("DELETE FROM recruiters_sites WHERE recruiter_id=?");
        $stmt->bind_param("i", $recruiter_id);
        $stmt->execute();
    }

    echo json_encode(['success'=>true]);
    exit;
}

echo json_encode(['success'=>false, 'error'=>'Invalid action']);
exit;
?>