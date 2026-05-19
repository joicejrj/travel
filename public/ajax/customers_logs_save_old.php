<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header("Content-Type: application/json");

$customer_id = intval($_POST['supplier_id'] ?? 0); // supplier_id = customer_id
$name        = trim($_POST['name'] ?? '');
$type        = trim($_POST['type'] ?? 'General');
$visibility  = trim($_POST['visibility'] ?? 'Public');
$notes       = trim($_POST['notes'] ?? '');

$agent_id    = intval($_SESSION['person_id'] ?? 0); // logged-in agent ID
$person_name = $_SESSION['person_name'] ?? 'Admin';

if ($customer_id > 0 && $name !== '' && $notes !== '') {

    $datetime = date('Y-m-d H:i:s');

    // Append admin info if available
    if (isset($_SESSION['people_name_admin'])) {
        $role = $_SESSION['people_name_admin']["role"] ?? '';
        $admin_name = $_SESSION['people_name_admin']['name'] ?? '';
        $name .= " ($role-$admin_name)";
    }

    // ✅ INSERT NOTE
    $stmt = $mysqli->prepare("
        INSERT INTO customers_logs 
        (customer_id, name, notes, agent_id, type, visibility, created_at, created_by)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    if (!$stmt) {
        echo json_encode(['success' => 0, 'error' => 'Prepare failed: ' . $mysqli->error]);
        exit;
    }

    $stmt->bind_param("ississsi", 
        $customer_id, 
        $name, 
        $notes, 
        $agent_id, 
        $type, 
        $visibility, 
        $datetime,
        $agent_id // created_by = agent_id for visibility filtering
    );

    $success = $stmt->execute();
    $stmt->close();

    if (!$success) {
        echo json_encode(['success' => 0, 'error' => 'Insert failed: ' . $mysqli->error]);
        exit;
    }

    // 🧾 Log activity for admin audit
    // $getcontact = $db->get('customers', ['id' => $customer_id], 'name');
    // $contact_name = $getcontact->name ?? '';
    $site->agent_log("Note added (Note: {$notes})",$customer_id);

    // ✅ Maintain daily_followup
    $today = date('Y-m-d');
    $status = '';

    // Fetch current contact type
    $s = $mysqli->prepare("SELECT type FROM customers WHERE id = ? LIMIT 1");
    if ($s) {
        $s->bind_param("i", $customer_id);
        $s->execute();
        $s->bind_result($status_res);
        if ($s->fetch()) $status = (string)$status_res;
        $s->close();
    }

    // Fetch existing daily_followup row
    $q = $mysqli->prepare("SELECT id, count_of, reminder_done, note_done 
                           FROM daily_followup 
                           WHERE agent_id = ? AND contact_id = ? AND date_followup = ? 
                           LIMIT 1");
    if ($q) {
        $q->bind_param("iis", $agent_id, $customer_id, $today);
        $q->execute();
        $q->bind_result($df_id, $df_count, $df_rem_done, $df_note_done);
        if ($q->fetch()) {
            $q->close();
            // Update only if note not already done
            if ((int)$df_note_done !== 1) {
                $newcount = (int)$df_count + 1;
                $u = $mysqli->prepare("UPDATE daily_followup 
                                       SET note_done = 1, count_of = ? 
                                       WHERE id = ?");
                if ($u) {
                    $u->bind_param("ii", $newcount, $df_id);
                    $u->execute();
                    $u->close();
                }
            }
        } else {
            // No row — insert fresh
            $q->close();
            $rem_done = 0;
            $note_done = 1;
            $startcount = 1;
            $ins = $mysqli->prepare("
                INSERT INTO daily_followup 
                (agent_id, contact_id, date_followup, status_followup, count_of, reminder_done, note_done)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            if ($ins) {
                $ins->bind_param("iissiii", $agent_id, $customer_id, $today, $status, $startcount, $rem_done, $note_done);
                $ins->execute();
                $ins->close();
            }
        }
    }

    echo json_encode(['success' => 1]);
    exit;
} else {
    echo json_encode(['success' => 0, 'error' => 'Invalid input']);
    exit;
}
?>