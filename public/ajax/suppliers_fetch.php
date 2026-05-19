<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
// require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

$rtn = ['success' => 0, 'fields' => []];

$supplier_id = isset($_POST['supplier_id']) ? (int)$_POST['supplier_id'] : 0;

if ($supplier_id <= 0) {
    $rtn['msg'] = "Invalid supplier ID";
    $site->json($rtn);
}

// $getc = $db->get('suppliers', ['id' => $supplier_id],'name, company, phones, phone, whatsapp, email, address, city, state, country, services, website, type, source, photo, photo1');
$getc = $db->get('suppliers', ['id' => $supplier_id],'name, company, industry, phone, email, country, type, photo, photo1');

$getc->tclass = "bg-info";
if($getc->type!='') {
    $getc->tclass = $getc->type == 'Won' ? 'bg-success' :
    ($getc->type == 'Opportunity' ? 'bg-primary' :
    ($getc->type == 'Lead (Active)' ? 'bg-warning' :
    ($getc->type == 'Suspect' ? 'bg-warning' :
    ($getc->type == 'Archive' ? 'bg-secondary' : 'bg-light text-dark'))));
}

$rtn['success'] = 1;
$rtn['fields']    = $getc?(array)$getc:[];

$site->json($rtn);
?>