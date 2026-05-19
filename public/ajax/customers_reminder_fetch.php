<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
// require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');

$customer_id = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;
$offset     = isset($_POST['offset']) ? (int)$_POST['offset'] : 0;
$limit      = isset($_POST['limit']) ? (int)$_POST['limit'] : 10;
$page = $offset; //$offset>0?$offset/$limit:0;

if ($customer_id <= 0) {
    echo json_encode(['reminders' => []]);
    exit;
}

$rno = $db->get('customers_reminders',array('customer_id'=>$customer_id,'#srt'=>'reminder_at desc'),'count(id) as no');
$pgno = intval($rno->no/$limit);
$reminders = $db->get('customers_reminders',array('#all'=>1,'customer_id'=>$customer_id,'#srt'=>'reminder_at desc','#page'=>$page,'#limit'=>10),'id, reminder_at, type, note, contact_id, created_at');
foreach ($reminders->data as $key => $remi) {
    $reminders->data[$key]->contact_name = '';
    if($remi->contact_id!='') {
        $getcontact = $db->get('customers_contacts',array('id'=>$remi->contact_id),'id,name');
        if($getcontact) {
            $reminders->data[$key]->contact_name = $getcontact->name;
        }
    }
    $reminders->data[$key]->reminder_at = date("d M Y h:i A",strtotime($remi->reminder_at));
}
echo json_encode(['reminders' => $reminders->data,'pgno'=>$pgno]);
?>