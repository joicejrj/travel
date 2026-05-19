<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'invalid_method']);
    exit;
}

/* =====================================================
   READ INPUT (FormData)
===================================================== */
$date        = $_POST['date'] ?? null;
$time        = $_POST['time'] ?? null;
$contact_name = $_POST['contact_name'] ?? null;
$subject     = $_POST['subject'] ?? null;
$notes       = $_POST['notes'] ?? null;

$channel_id      = !empty($_POST['channel_id']) ? (int)$_POST['channel_id'] : null;
$contact_type_id = !empty($_POST['contact_type_id']) ? (int)$_POST['contact_type_id'] : null;
$scenario_id     = !empty($_POST['scenario_id']) ? (int)$_POST['scenario_id'] : null;
$assigned_to        = !empty($_POST['owner_id']) ? (int)$_POST['owner_id'] : null;

$itype      = !empty($_POST['itype']) ? $_POST['itype'] : 'IN';

// $status    = $_POST['status'] ?? null;
$status    = $_POST['status'] ?? 'open';
$status = $status==''?'open':$status;
$priority  = $_POST['priority'] ?? null;

$follow_date = $_POST['follow_date'] ?? null;
$follow_time = $_POST['follow_time'] ?? null;

$nature = $_POST['nature'] ?? "";
if($nature=="") {
    echo json_encode(['success'=>false,'error'=>'You should select a tag']);
    exit;
}

// $contact_entity_id = !empty($_POST['contact_entity_id']) ? (int)$_POST['contact_entity_id'] : null;
$contact_entity_id = !empty($_POST['customer_id']) ? (int)$_POST['customer_id'] : null;

$contact_entity_type = $_POST['contact_entity_type'] ?? '';
if($contact_entity_type=='employees' && isset($_POST['contact_entity_id'])) {
    $contact_entity_id = !empty($_POST['contact_entity_id']) ? (int)$_POST['contact_entity_id'] : null;
}

$contact_phone     = $_POST['contact_phone'] ?? '';
$contact_email     = $_POST['contact_email'] ?? '';

$entity_contact_id    = $_POST['customer_contact_id'] ?? null;
$related_employee_ids = $_POST['related_employee_ids'] ?? null;
$related_customer_id  = $_POST['related_customer_id'] ?? null;

$document_label = !empty($_POST['document_type']) ? $_POST['document_type'] : null;

/* =====================================================
   INSERT INTERACTION
===================================================== */
$sql = "INSERT INTO interactions
(date,time,contact_name,subject,notes,channel_id,contact_type_id,scenario_id,owner_id,assigned_to,
 status,priority,follow_date,follow_time,contact_entity_id,contact_phone,contact_email,
 entity_contact_id,related_employee_ids,related_customer_id,itype,nature)
VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['success'=>false,'error'=>'prepare_failed']);
    exit;
}

$stmt->bind_param(
    "sssssiiiisssssisssssss",
    $date, $time, $contact_name, $subject, $notes,
    $channel_id, $contact_type_id, $scenario_id, $_SESSION['person_id'], $assigned_to,
    $status, $priority, $follow_date, $follow_time,
    $contact_entity_id, $contact_phone, $contact_email,
    $entity_contact_id, $related_employee_ids, $related_customer_id, $itype, $nature
);

if (!$stmt->execute()) {
    echo json_encode(['success'=>false,'error'=>'insert_failed','db'=>$stmt->error]);
    exit;
}

$interaction_id = $stmt->insert_id;
$stmt->close();


/* =====================================================
   GET CONTACT TYPE SLUG (for routing)
===================================================== */
$contact_type_slug = null;
$rurl = '';

if ($contact_type_id) {
    $s = $mysqli->prepare("SELECT slug FROM contact_types WHERE id=? LIMIT 1");
    if ($s) {
        $s->bind_param('i', $contact_type_id);
        $s->execute();
        $s->bind_result($contact_type_slug);
        $s->fetch();
        $s->close();
    }
}

/* redirect target */
$ufolder = "";
$utype = "";
switch ($contact_type_slug) {
    case 'customer':
        $rurl = 'customers_view&id='.$contact_entity_id;
        $ufolder = "customers/";
        $utype = "customer";
        break;
    case 'new':
        $rurl = 'contacts_view&id='.$contact_entity_id;
        $ufolder = "contacts/";
        $utype = "contact";
    case 'existing-contact':
        $rurl = 'contacts_view&id='.$contact_entity_id;
        $ufolder = "contacts/";
        $utype = "contact";
        break;
    case 'existing-employee':
        $rurl = 'employees_viewr&id='.$contact_entity_id;
        $ufolder = "employees/";
        $utype = "employee";
        break;
    case 'employee':
        $rurl = 'recruiters_view&id='.$contact_entity_id;
        $ufolder = "recruiters/";
        $utype = "recruiter";
        break;
    case 'vendor':
        $rurl = 'suppliers_view&id='.$contact_entity_id;
        $ufolder = "suppliers/";
        $utype = "supplier";
        break;
}

$site->agent_log("New interaction with summary $subject [#$interaction_id] is added ",$contact_entity_id,$utype);

/* =====================================================
   SINGLE DOCUMENT UPLOAD (OPTIONAL)
===================================================== */
if (
    !empty($_FILES['document_file']) &&
    $_FILES['document_file']['error'] === UPLOAD_ERR_OK &&
    !empty($contact_entity_id)
) {
    $baseDir        = __DIR__ . '/../../uploads/'.$ufolder;
    $interactionDir = $baseDir . 'interactions/';
    $interactionDir1 = __DIR__ . '/../../uploads/interactions/';
    $entityDir      = $baseDir . 'documents/';

    if (!is_dir($entityDir)) mkdir($entityDir, 0777, true);
    if (!is_dir($interactionDir)) mkdir($interactionDir, 0777, true);

    $ext = strtolower(pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION));
    // $dtype = ($ext === 'pdf') ? 'pdf' : 'image';
    if ($ext === 'pdf') {
        $dtype = 'pdf';
    } elseif (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
        $dtype = 'image';
    } elseif (in_array($ext, ['mp4','mov','avi','webm'])) {
        $dtype = 'video';
    } else {
        json_exit(['success' => false, 'error' => 'invalid_file_type']);
    }

    $interactionFile = 'interaction_'.$interaction_id.'_'.time().'.'.$ext;
    $entityFile      = 'doc_'.$interaction_id.'_'.time().'.'.$ext;

    if (move_uploaded_file($_FILES['document_file']['tmp_name'], $interactionDir.$interactionFile)) {

        copy($interactionDir.$interactionFile, $interactionDir1.$interactionFile);
        // copy($interactionDir.$interactionFile, $entityDir.$entityFile);

        /* ---- determine documents table ---- */
        // $utable = null;
        // $idCol  = null;

        // switch ($contact_type_slug) {
        //     case 'customer':
        //         $utable='customers_documents'; $idCol='customer_id'; break;
        //     case 'new':
        //     case 'existing-contact':
        //         $utable='contacts_documents'; $idCol='contact_id'; break;
        //     case 'existing-employee':
        //         $utable='employees_documents'; $idCol='employee_id'; break;
        //     case 'employee':
        //         $utable='recruiters_documents'; $idCol='recruiter_id'; break;
        //     case 'vendor':
        //         $utable='suppliers_documents'; $idCol='supplier_id'; break;
        // }

        // if ($utable && $idCol) {
        //     $ins = $mysqli->prepare("
        //         INSERT INTO {$utable}
        //         ({$idCol}, label, file_name, file_type, created_by, created_at)
        //         VALUES (?, ?, ?, ?, ?, NOW())
        //     ");
        //     if ($ins) {
        //         $document_label = $document_label==null||$document_label==''?'Document':$document_label;
        //         $ins->bind_param('issss', $contact_entity_id, $document_label, $entityFile, $dtype, $_SESSION['person_name']);
        //         $ins->execute();
        //         $ins->close();

        //         $site->agent_log("New document with label $document_label($entityFile) is added ",$contact_entity_id,$utype,'notimeline');
        //     }
        // }

        /* Insert to interactions documents */
        $document_label = $document_label==null||$document_label==''?'Document':$document_label;
        $stmt = $mysqli->prepare("
            INSERT INTO interactions_documents
            (interaction_id, label, file_name, file_type, created_by, created_at)
            VALUES (?,?,?,?,?,NOW())
        ");
        $stmt->bind_param(
            "issss",
            $interaction_id,
            $document_label,
            $interactionFile,
            $dtype,
            $_SESSION['person_name']
        );
        $stmt->execute();
        $stmt->close();

        /* ---- update interaction with its own copy ---- */
        $upd = $mysqli->prepare("UPDATE interactions SET document_label=?, document_file=? WHERE id=?");
        if ($upd) {
            $upd->bind_param('ssi', $document_label, $interactionFile, $interaction_id);
            $upd->execute();
            $upd->close();
        }
    }
}

/* =====================================================
   LOGS & REMINDERS
===================================================== */
$res = $mysqli->query("SELECT * FROM interactions WHERE id=".$interaction_id." LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $agent_id = $_SESSION['user_id'] ?? null;
    save_related_logs_and_reminders($mysqli, $row, $agent_id);
    $res->free();
}

if($contact_type_slug=='new') {
    $contact_entity_id = $mysqli
        ->query("SELECT contact_entity_id FROM interactions WHERE id=".$interaction_id." LIMIT 1")
        ->fetch_assoc()['contact_entity_id'] ?? null;
    $rurl = 'contacts_view&id='.$contact_entity_id;
}


/* =====================================================
   RESPONSE
===================================================== */
echo json_encode([
    'success' => true,
    'id'      => $interaction_id,
    'rurl'    => $rurl
]);
exit;

/**
 * Save related logs & reminders for an interaction row.
 * The function will:
 *  - decide target by contact type name (Existing Employee/Customer/Recruiter/Supplier => respective target)
 *  - if not existing, create a contacts row (if none exists) and use it for contacts_logs & contacts_reminders
 *  - insert into {target}_logs and {target}_reminders as required
 */
function save_related_logs_and_reminders($mysqli, $it, $agent_id = null) {
    // normalize
    $channel_id = isset($it['channel_id']) ? intval($it['channel_id']) : null;
    $scenario_id = isset($it['scenario_id']) ? intval($it['scenario_id']) : null;
    $ct_id = isset($it['contact_type_id']) ? intval($it['contact_type_id']) : null;
    $entity_type = isset($it['contact_entity_type']) ? $mysqli->real_escape_string($it['contact_entity_type']) : null;
    $entity_id = isset($it['contact_entity_id']) && $it['contact_entity_id'] !== '' ? intval($it['contact_entity_id']) : null;

    $contact_name = isset($it['contact_name']) ? $mysqli->real_escape_string($it['contact_name']) : '';
    $contact_phone = isset($it['contact_phone']) ? $mysqli->real_escape_string($it['contact_phone']) : '';
    $contact_email = isset($it['contact_email']) ? $mysqli->real_escape_string($it['contact_email']) : '';

    $summary = isset($it['subject']) ? $mysqli->real_escape_string($it['subject']) : '';
    $notes = isset($it['notes']) ? $mysqli->real_escape_string($it['notes']) : '';
    $assigned_to = isset($it['assigned_to']) && $it['assigned_to'] !== '' ? intval($it['assigned_to']) : null;
    $priority = isset($it['priority']) ? $mysqli->real_escape_string($it['priority']) : '';

    // fetch channel/scenario names (optional)
    $channel_name = '';
    if ($channel_id) {
        $s = $mysqli->prepare("SELECT name FROM channels WHERE id = ? LIMIT 1");
        if ($s) { $s->bind_param('i', $channel_id); $s->execute(); $s->bind_result($cn); if ($s->fetch()) $channel_name = $cn; $s->close(); }
    }
    $scenario_name = '';
    if ($scenario_id) {
        $s = $mysqli->prepare("SELECT name FROM scenarios WHERE id = ? LIMIT 1");
        if ($s) { $s->bind_param('i', $scenario_id); $s->execute(); $s->bind_result($sn); if ($s->fetch()) $scenario_name = $sn; $s->close(); }
    }

    // fetch contact_type name to determine target
    $contact_type_name = '';
    if ($ct_id) {
        $s = $mysqli->prepare("SELECT name FROM contact_types WHERE id = ? LIMIT 1");
        if ($s) { $s->bind_param('i', $ct_id); $s->execute(); $s->bind_result($ctn); if ($s->fetch()) $contact_type_name = $ctn; $s->close(); }
    }

    // decide target string (plural table-prefix used earlier)
    $lower = strtolower($contact_type_name);
    $target = 'contacts';
    if (strpos($lower, 'existing employee') !== false) $target = 'employees';
    elseif (strpos($lower, 'existing recruiter') !== false) $target = 'recruiters';
    elseif (strpos($lower, 'existing supplier') !== false) $target = 'suppliers';
    elseif (strpos($lower, 'existing customer') !== false) $target = 'customers';
    elseif (strpos($lower, 'existing contact') !== false) $target = 'contacts';

    // helper: get columns list for a table
    $get_table_columns = function($table) use ($mysqli) {
        $cols = [];
        $res = $mysqli->query("SHOW COLUMNS FROM `{$table}`");
        if ($res) {
            while ($r = $res->fetch_assoc()) $cols[] = $r['Field'];
            $res->free();
        }
        return $cols;
    };

    // helper: find FK column for logs table
    $determine_fk_column = function($table, $target_plural) use ($mysqli, $get_table_columns) {
        $cols = $get_table_columns($table);
        if (empty($cols)) return null;

        // candidate1: singular form (strip trailing s if present)
        $sing = (substr($target_plural, -1) === 's') ? substr($target_plural, 0, -1) : $target_plural;
        $c1 = $sing . '_id';
        $c2 = $target_plural . '_id';

        if (in_array($c1, $cols)) return $c1;
        if (in_array($c2, $cols)) return $c2;

        // fallback: pick first column that ends with _id and is not 'id' and not 'agent_id'
        foreach ($cols as $col) {
            if ($col === 'id' || $col === 'agent_id') continue;
            if (substr($col, -3) === '_id') return $col;
        }
        // no fk found
        return null;
    };

    // Now decide contact_record_id:
    $contact_record_id = null;
    $created_contact = false; // <-- NEW: track if we created a contacts row here

    if ($target === 'contacts') {
        // if interaction already had an entity_id that points to contacts, use it
        if ($entity_id && ($entity_type === 'contacts' || $entity_type === null)) {
            $contact_record_id = $entity_id;
        } else {
            // try dedupe by email then phone
            $found = false;
            if (!empty($contact_email)) {
                $s = $mysqli->prepare("SELECT id FROM contacts WHERE email = ? LIMIT 1");
                if ($s) { $s->bind_param('s', $contact_email); $s->execute(); $s->bind_result($cid); if ($s->fetch()) { $contact_record_id = $cid; $found = true; } $s->close(); $created_contact = true; }
            }
            if (!$found && !empty($contact_phone)) {
                $s = $mysqli->prepare("SELECT id FROM contacts WHERE phone = ? LIMIT 1");
                if ($s) { $s->bind_param('s', $contact_phone); $s->execute(); $s->bind_result($cid); if ($s->fetch()) { $contact_record_id = $cid; $found = true; } $s->close(); $created_contact = true; }
            }
            if (!$found) {
                // create
                $ins = $mysqli->prepare("INSERT INTO contacts (agent_id, name, company, phone, email, date_added) VALUES (?, ?, ?, ?, ?, NOW())");
                if ($ins) {
                    $ag = $agent_id ? intval($agent_id) : null;
                    $company = '';
                    $ins->bind_param('issss', $ag, $contact_name, $company, $contact_phone, $contact_email);
                    $ins->execute();
                    $contact_record_id = $mysqli->insert_id;
                    $ins->close();
                    $created_contact = true; // <-- NEW
                } else {
                    $qn = $mysqli->real_escape_string($contact_name);
                    $qp = $mysqli->real_escape_string($contact_phone);
                    $qe = $mysqli->real_escape_string($contact_email);
                    $mysqli->query("INSERT INTO contacts (agent_id, name, company, phone, email, date_added) VALUES (" . ($agent_id ? intval($agent_id) : "NULL") . ", '{$qn}', '', '{$qp}', '{$qe}', NOW())");
                    $contact_record_id = $mysqli->insert_id;
                    $created_contact = true; // <-- NEW
                }
            }
        }
    } else {
        // existing entity target (employees/customers/etc) - use entity_id if provided
        if ($entity_id) $contact_record_id = $entity_id;
        else {
            // fallback: create a contact record and use it
            $ins = $mysqli->prepare("INSERT INTO contacts (agent_id, name, company, phone, email, date_added) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($ins) {
                $ag = $agent_id ? intval($agent_id) : null;
                $company = '';
                $ins->bind_param('issss', $ag, $contact_name, $company, $contact_phone, $contact_email);
                $ins->execute();
                $contact_record_id = $mysqli->insert_id;
                $ins->close();
                $created_contact = true; // <-- NEW (we created a contacts row as fallback)
            } else {
                $qn = $mysqli->real_escape_string($contact_name);
                $qp = $mysqli->real_escape_string($contact_phone);
                $qe = $mysqli->real_escape_string($contact_email);
                $mysqli->query("INSERT INTO contacts (agent_id, name, company, phone, email, date_added) VALUES (" . ($agent_id ? intval($agent_id) : "NULL") . ", '{$qn}', '', '{$qp}', '{$qe}', NOW())");
                $contact_record_id = $mysqli->insert_id;
                $created_contact = true; // <-- NEW
            }
        }
    }

    // ---- NEW: If we created a contacts row just now, update the original interactions row so it points to it ----
    if ($created_contact && !empty($it['id'])) {
        $interaction_id = intval($it['id']);
        $new_cid = $contact_record_id ? intval($contact_record_id) : null;

        // prefer prepared statement; fallback to escaped query if prepare() fails
        $upd = $mysqli->prepare("UPDATE interactions SET contact_entity_id = ? WHERE id = ?");
        if ($upd) {
            $upd->bind_param('ii', $new_cid, $interaction_id);
            $upd->execute();
            $upd->close();
        } else {
            // fallback — make sure values are safely escaped / cast
            $mysqli->query("UPDATE interactions SET contact_entity_id = " . ($new_cid !== null ? intval($new_cid) : "NULL") . " WHERE id = " . $interaction_id);
        }
    }

    // Compose readable note:
    $assign_name = '';
    if ($assigned_to) {
        $s = $mysqli->prepare("SELECT name FROM people WHERE id = ? LIMIT 1");
        if ($s) { $s->bind_param('i', $assigned_to); $s->execute(); $s->bind_result($pn); if ($s->fetch()) $assign_name = $pn; $s->close(); }
    }
    $parts = [];
    if ($channel_name) $parts[] = "Channel: {$channel_name}";
    if ($scenario_name) $parts[] = "Scenario: {$scenario_name}";
    if ($summary) $parts[] = "Summary: {$summary}";
    if ($notes) $parts[] = "Notes: {$notes}";
    if ($assign_name) $parts[] = "Assigned to: {$assign_name}";
    if ($priority) $parts[] = "Priority: {$priority}";
    $note_text = implode('. ', $parts);
    if ($note_text !== '') $note_text .= '.';

    $log_type = 'General';
    $visibility = 'Public';
    $created_at = date('Y-m-d H:i:s');

    // Insert into logs table for the determined target
    $target_logs_table = $target . '_logs'; // e.g. employees_logs
    $cols = $get_table_columns($target_logs_table);

    // Special handling for contacts_logs (different schema)
    if ($target === 'contacts') {
    // contacts_logs columns (from your schema): contact_id, agent_id, name, notes, type, visibility, created_at
    $cid = $contact_record_id ? intval($contact_record_id) : null;
    $ag  = $agent_id ? intval($agent_id) : null;

    // Prepare insert - use prepared statement
    $ins = $mysqli->prepare("INSERT INTO contacts_logs (contact_id, agent_id, name, notes, type, visibility, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    if ($ins) {
        $ins->bind_param('iisssss', $cid, $ag, $contact_name, $note_text, $log_type, $visibility, $created_at);
        $ins->execute();
        // optional: check $ins->error for debugging
        $ins->close();
    } else {
        // fallback if prepare() fails - escape values
        $qn = $mysqli->real_escape_string($contact_name);
        $qnotes = $mysqli->real_escape_string($note_text);
        $qtype = $mysqli->real_escape_string($log_type);
        $qvis = $mysqli->real_escape_string($visibility);
        $qcreated = $mysqli->real_escape_string($created_at);
        $mysqli->query(
          "INSERT INTO contacts_logs (contact_id, agent_id, name, notes, type, visibility, created_at) VALUES (" .
           ($cid !== null ? intval($cid) : "NULL") . ", " .
           ($ag !== null ? intval($ag) : "NULL") . ", " .
           "'{$qn}', '{$qnotes}', '{$qtype}', '{$qvis}', '{$qcreated}')" );
    }
}

    // Insert reminder if follow date/time provided (same detection logic for reminders table)
    $reminder_at = null;
    if (!empty($it['follow_date'])) {
        $ft = isset($it['follow_time']) && $it['follow_time'] !== '' ? $it['follow_time'] : '00:00:00';
        $follow_date = $it['follow_date'];
        $reminder_at = date('Y-m-d H:i:s', strtotime($follow_date . ' ' . $ft));
    }

    if ($reminder_at && $follow_date!='0000-00-00') {
        $reminders_table = $target . '_reminders';
        $note_for_reminder = $note_text;
        $rem_type = 'General';
        $rem_cols = $get_table_columns($reminders_table);

        if ($target === 'contacts') {
            // contacts_reminders likely uses contact_id
            if (in_array('contact_id', $rem_cols)) {
                $ins = $mysqli->prepare("INSERT INTO contacts_reminders (contact_id, reminder_at, type, note, created_at) VALUES (?, ?, ?, ?, ?)");
                if ($ins) {
                    $cid = $contact_record_id ? intval($contact_record_id) : null;
                    $ins->bind_param('issss', $cid, $reminder_at, $rem_type, $note_for_reminder, $created_at);
                    $ins->execute();
                    $ins->close();
                } else {
                    $qnote = $mysqli->real_escape_string($note_for_reminder);
                    $mysqli->query("INSERT INTO contacts_reminders (contact_id, reminder_at, type, note, created_at) VALUES (" . ($contact_record_id ? intval($contact_record_id) : "NULL") . ", '{$reminder_at}', '{$rem_type}', '{$qnote}', '{$created_at}')");
                }
            } else {
                // fallback: try generic insert with best guess columns
                $qnote = $mysqli->real_escape_string($note_for_reminder);
                $mysqli->query("INSERT INTO contacts_reminders (contact_id, reminder_at, type, note, created_at) VALUES (" . ($contact_record_id ? intval($contact_record_id) : "NULL") . ", '{$reminder_at}', '{$rem_type}', '{$qnote}', '{$created_at}')");
            }
        } else {
            // generic reminders (e.g. employees_reminders) — find FK (employee_id etc)
            $fk_col = $determine_fk_column($reminders_table, $target);
            if ($fk_col === null) {
                // cannot determine — skip reminder insert
            } else {
                // Many reminders tables include columns: <fk_col>, reminder_at, type, contact_id, note, created_at
                // If contact_id exists in reminders table, set it to contact_record_id; otherwise pass null
                $has_contact_id = in_array('contact_id', $rem_cols);
                if ($has_contact_id) {
                    $sql = "INSERT INTO `{$reminders_table}` (`{$fk_col}`, reminder_at, type, contact_id, note, created_at) VALUES (?, ?, ?, ?, ?, ?)";
                    $ins = $mysqli->prepare($sql);
                    if ($ins) {
                        $tid = $contact_record_id ? intval($contact_record_id) : null;
                        $cid = $contact_record_id ? intval($contact_record_id) : null;
                        $ins->bind_param('ississ', $tid, $reminder_at, $rem_type, $cid, $note_for_reminder, $created_at);
                        $ins->execute();
                        $ins->close();
                    } else {
                        $qnote = $mysqli->real_escape_string($note_for_reminder);
                        $mysqli->query("INSERT INTO `{$reminders_table}` (`{$fk_col}`, reminder_at, type, contact_id, note, created_at) VALUES (" . ($contact_record_id ? intval($contact_record_id) : "NULL") . ", '{$reminder_at}', '{$rem_type}', " . ($contact_record_id ? intval($contact_record_id) : "NULL") . ", '{$qnote}', '{$created_at}')");
                    }
                } else {
                    // no contact_id column: try simple insert with fk + reminder_at + type + note + created_at
                    $sql = "INSERT INTO `{$reminders_table}` (`{$fk_col}`, reminder_at, type, note, created_at) VALUES (?, ?, ?, ?, ?)";
                    $ins = $mysqli->prepare($sql);
                    if ($ins) {
                        $tid = $contact_record_id ? intval($contact_record_id) : null;
                        $ins->bind_param('issss', $tid, $reminder_at, $rem_type, $note_for_reminder, $created_at);
                        $ins->execute();
                        $ins->close();
                    } else {
                        $qnote = $mysqli->real_escape_string($note_for_reminder);
                        $mysqli->query("INSERT INTO `{$reminders_table}` (`{$fk_col}`, reminder_at, type, note, created_at) VALUES (" . ($contact_record_id ? intval($contact_record_id) : "NULL") . ", '{$reminder_at}', '{$rem_type}', '{$qnote}', '{$created_at}')");
                    }
                }
            }
        }
    }

    return true;
}

?>