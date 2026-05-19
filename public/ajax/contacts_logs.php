<?php
ini_set('display_errors',1);
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header("Content-Type: application/json");

$action = trim($_POST['action'] ?? '');
$agent_id = intval($_SESSION['person_id'] ?? 0);
$person_name = $_SESSION['person_name'] ?? 'Admin';
$response = ['success' => false, 'error' => 'Invalid action'];

switch ($action) {

  /* ==========================================================
     FETCH NOTES
     ========================================================== */
  case 'fetch':
    $supplier_id = intval($_POST['supplier_id'] ?? 0);
    $offset = intval($_POST['offset'] ?? 0);
    $limit = intval($_POST['limit'] ?? 10);

    if ($supplier_id <= 0) {
      echo json_encode(['logs' => []]);
      exit;
    }

    $stmt = $mysqli->prepare("
      SELECT id, name, notes, type, visibility, agent_id,
             DATE_FORMAT(created_at, '%d %b %Y %h:%i %p') AS created_at
      FROM contacts_logs
      WHERE contact_id = ?
        AND (visibility = 'Public' OR (visibility = 'Private' AND agent_id = ?))
      ORDER BY id DESC
      LIMIT ?, ?
    ");
    $stmt->bind_param("iiii", $supplier_id, $agent_id, $offset, $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $data = [];
    while ($r = $res->fetch_assoc()) $data[] = $r;
    $stmt->close();

    echo json_encode(['logs' => $data]);
    exit;



  /* ==========================================================
     SAVE NEW NOTE
     ========================================================== */
  case 'save':
    $contact_id = intval($_POST['supplier_id'] ?? 0);
    $name = trim($_POST['name'] ?? '');
    $notes = trim($_POST['notes'] ?? '');
    $type = trim($_POST['type'] ?? 'General');
    $visibility = trim($_POST['visibility'] ?? 'Public');
    $datetime = date('Y-m-d H:i:s');

    if ($contact_id <= 0 || $name === '' || $notes === '') {
      echo json_encode(['success' => false, 'error' => 'Invalid input']);
      exit;
    }

    // Add admin info (if any)
    if (isset($_SESSION['people_name_admin'])) {
      $role = $_SESSION['people_name_admin']["role"] ?? '';
      $admin_name = $_SESSION['people_name_admin']['name'] ?? '';
      $name .= " ($role-$admin_name)";
    }

    $stmt = $mysqli->prepare("
      INSERT INTO contacts_logs (contact_id, name, notes, agent_id, type, visibility, created_at)
      VALUES (?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ississs", $contact_id, $name, $notes, $agent_id, $type, $visibility, $datetime);
    $success = $stmt->execute();
    $stmt->close();

    if ($success) {
      // Log activity
      $getcontact = $db->get('contacts', ['id' => $contact_id], 'name');
      $contact_name = $getcontact->name ?? '';
      $site->agent_log("Note added (Note: {$notes})",$contact_id,'contact');

      // Maintain daily_followup


      echo json_encode(['success' => true]);
    } else {
      echo json_encode(['success' => false, 'error' => 'Insert failed']);
    }
    exit;



  /* ==========================================================
     UPDATE NOTE
     ========================================================== */
  case 'update':
    $id = intval($_POST['id'] ?? 0);
    $contact_id = intval($_POST['supplier_id'] ?? 0);
    $notes = trim($_POST['notes'] ?? $_POST['notes'] ?? '');
    $type = trim($_POST['type'] ?? $_POST['type'] ?? 'General');
    $visibility = trim($_POST['visibility'] ?? $_POST['visibility'] ?? 'Public');

    if ($id <= 0 || $notes === '') {
      echo json_encode(['success' => false, 'error' => 'Invalid update input']);
      exit;
    }

    $stmt = $mysqli->prepare("UPDATE contacts_logs SET notes=?, type=?, visibility=? WHERE id=? AND (agent_id=? OR visibility='Public')");
    $stmt->bind_param("sssii", $notes, $type, $visibility, $id, $agent_id);
    $success = $stmt->execute();
    $stmt->close();

    if($success) {
      $site->agent_log("Note updated (Note: {$notes})",$contact_id,'contact');
    }

    echo json_encode(['success' => $success]);
    exit;



  /* ==========================================================
     DELETE NOTE
     ========================================================== */
  case 'delete':
    $id = intval($_POST['id'] ?? 0);
    $contact_id = intval($_POST['supplier_id'] ?? 0);
    if ($id <= 0) {
      echo json_encode(['success' => false, 'error' => 'Invalid note ID']);
      exit;
    }

    // Only allow delete by creator
    $stmt = $mysqli->prepare("DELETE FROM contacts_logs WHERE id = ? AND agent_id = ?");
    $site->agent_log("Note deleted [$id]",$contact_id,'contact');
    $stmt->bind_param("ii", $id, $agent_id);
    $success = $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => $success]);
    exit;



  /* ==========================================================
     DEFAULT
     ========================================================== */
  default:
    echo json_encode($response);
    exit;
}
?>