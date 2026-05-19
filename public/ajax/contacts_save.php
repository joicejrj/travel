<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
// require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$supplier_id = intval($_POST['supplier_id'] ?? 0);
$field       = $_POST['field'] ?? '';
$value       = $_POST['value'] ?? '';
$status_note = trim($_POST['status_note'] ?? '');

$allowed = ['company','name','phone','email','agent_id','enable_email','enable_whatsapp']; // add photo

$response = ['success' => false, 'msg' => 'Invalid request'];

// === Handle photo upload 'photo' ===
if ($supplier_id > 0 && $field == 'photo' && isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $file = $_FILES['photo'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg','jpeg','png','gif'];

    if (in_array($ext, $allowedExts) && $file['size'] <= 10*1024*1024) { // max 10MB (as previously)
        $newName = $site->upload_img($file,'../uploads/contacts','random',800);
        if($newName!='') {
            $geti = $db->get('contacts', array('id' => $supplier_id), 'photo');
            $upd  = $db->update('contacts', ['id' => $supplier_id], ['photo' => $newName]);
            if ($upd) {
                if ($geti && $geti->photo != '') {
                    $site->remove_file('../../uploads/contacts/' . $geti->photo);
                }
                // $getcontact = $db->get('customers',array('id'=>$supplier_id),'name');
                $site->agent_log("Contacts Address ID 1 is updated",$supplier_id,'contact');
                $response = ['success' => true, 'msg' => 'Photo uploaded successfully', 'photo' => $newName];
            } else {
                $response['msg'] = 'Database update failed';
            }
        } else {
            $response['msg'] = 'Failed to process uploaded image';
        }
    } else {
        $response['msg'] = 'Invalid file type or size';
    }
}
// === Handle photo1 upload ===
else if ($supplier_id > 0 && $field == 'photo1' && isset($_FILES['photo1']) && $_FILES['photo1']['error'] == 0) {
    $file = $_FILES['photo1'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowedExts = ['jpg','jpeg','png','gif'];

    if (in_array($ext, $allowedExts) && $file['size'] <= 10*1024*1024) {
        $newName = $site->upload_img($file,'../uploads/contacts','random',800);
        if($newName!='') {
            $geti = $db->get('contacts', array('id' => $supplier_id), 'photo1');
            $upd  = $db->update('contacts', ['id' => $supplier_id], ['photo1' => $newName]);
            if ($upd) {
                // $getcontact = $db->get('customers',array('id'=>$supplier_id),'name');
                $site->agent_log("Contacts Address ID 2 is updated",$supplier_id,'contact');
                if ($geti && $geti->photo1 != '') {
                    $site->remove_file('../../uploads/contacts/' . $geti->photo1);
                }
                $response = ['success' => true, 'msg' => 'Photo uploaded successfully', 'photo' => $newName];
            } else {
                $response['msg'] = 'Database update failed';
            }
        } else {
            $response['msg'] = 'Failed to process uploaded image';
        }
    } else {
        $response['msg'] = 'Invalid file type or size';
    }
}
// === Handle normal field updates ===
else if ($supplier_id > 0 && in_array($field, $allowed)) {

    // Special handling for status/type changes: require a note and log the change
     if ($field === 'enable_email' || $field === 'enable_whatsapp') {

        $prettyField = ($field === 'enable_email') ? 'Email Feature' : 'WhatsApp Feature';
        $newStatus = ($value == 1 || $value === '1' || $value === true || $value === 'true') ? 'Enabled' : 'Disabled';

        // Get previous state
        $prev = $db->get('contacts', ['id' => $supplier_id], $field);
        $prev_value = $prev ? ($prev->$field=='1' ? 'Enabled' : 'Disabled') : 'Disabled';

        if($prev_value==$newStatus) {
            $response = ['success' => true, 'msg' => ucfirst($field) . " updated"];
            $site->json($response);
            exit;
        }

        // Update database
        $upd = $db->update('contacts', ['id' => $supplier_id], [$field => $value]);
        if ($upd) {
            // Get contact name for log
            // $getcontact = $db->get('customers', ['id' => $supplier_id], 'name');
            // $contact_name = $getcontact->name ?? '';

            // Write agent log
            $site->agent_log("{$prettyField} status changed to {$newStatus}",$supplier_id,'contact');

            // Prepare log data for customers_logs
            $logData = [
                'contact_id' => $supplier_id,
                'name'        => isset($CURRENT_USER_NAME) ? $CURRENT_USER_NAME : ($_SESSION['person_name'] ?? ''),
                'notes'       => "{$prettyField} changed from '{$prev_value}' to '{$newStatus}'",
                'created_at'  => date('Y-m-d H:i:s')
            ];

            // Try to insert log into customers_logs table
            if (method_exists($db, 'insert')) {
                $inserted = $db->insert('contacts_logs', $logData);
                if ($inserted === false) {
                    // fallback to contact_logs if customers_logs not found
                    @$db->insert('contact_logs', $logData);
                }
            } else {
                // fallback: direct mysqli insert
                $stmt = $mysqli->prepare("INSERT INTO contacts_logs (contact_id, name, notes, created_at) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("isss", $supplier_id, $logData['name'], $logData['notes'], $logData['created_at']);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            $response = ['success' => true, 'msg' => "{$prettyField} {$newStatus} successfully."];
        } else {
            $response = ['success' => false, 'msg' => "Failed to update {$prettyField}."];
        }

        $site->json($response);
        exit;
    } else {
        // Normal update path
        $getcontact = $db->get('contacts', ['id' => $supplier_id], "name, $field");

        if ($getcontact && $getcontact->{$field} == $value) {
            $response = ['success' => true, 'msg' => ucfirst($field) . " updated"];
            $site->json($response);
            exit;
        }

        $upd = $db->update('contacts', array('id' => $supplier_id), array($field => $value));
        if ($upd) {

            // update existing contact number to customers phones json array
            
    

            $site->agent_log("Contact ".$field." is updated to ".$value,$supplier_id,'contact');

            $response = ['success' => true, 'msg' => ucfirst($field) . " updated"];
        } else {
            $response = ['success' => false, 'msg' => "Update failed"];
        }
    }
}

$site->json($response);
