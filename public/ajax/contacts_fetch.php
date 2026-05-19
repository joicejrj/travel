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
    $rtn['msg'] = "Invalid Contact ID";
    $site->json($rtn);
}

// $getc = $db->get('customers', ['id' => $supplier_id],'name, company, phones, phone, whatsapp, email, address, city, state, country, services, website, type, source, photo, photo1');
$getc = $db->get('contacts', ['id' => $supplier_id],'name, company, phone, email, photo, photo1');

$rtn['success'] = 1;
$rtn['fields']    = $getc?(array)$getc:[];

$site->json($rtn);
?>