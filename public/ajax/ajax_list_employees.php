<?php
// public/ajax/ajax_list_employees.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../config/db.php'; // provides $mysqli

// params
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 500;
$limit = ($limit > 0 && $limit <= 5000) ? $limit : 500;

$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// Build base query and params
$sql = "SELECT id,
               COALESCE(emp_id,'') AS emp_id,
               COALESCE(name,'') AS name,
               COALESCE(company,'') AS company,
               COALESCE(phone,'') AS phone,
               COALESCE(phones,'') AS phones,
               COALESCE(email,'') AS email
        FROM employees
        ";
$where = [];
$params = [];
$types = "";

// optional filter by customer_id
if ($customer_id > 0) {
    $where[] = "customer_id = ?";
    $types .= "i";
    $params[] = $customer_id;
}

// optional search q (search name, email, phone, phones, emp_id). If q is numeric allow exact id match.
if ($q !== '') {
    $like = '%' . $q . '%';
    // search across text columns
    $whereParts = "(name LIKE ? OR email LIKE ? OR phone LIKE ? OR phones LIKE ? OR emp_id LIKE ?)";
    $where[] = $whereParts;
    $types .= "sssss";
    $params[] = $like; // name
    $params[] = $like; // email
    $params[] = $like; // phone
    $params[] = $like; // phones
    $params[] = $like; // emp_id

    
}

if (!empty($where)) $sql .= " WHERE " . implode(" AND ", $where);

$sql .= " ORDER BY name LIMIT ?";
$types .= "i";
$params[] = $limit;

$out = ['success' => false];

$stmt = $mysqli->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'db_prepare_failed', 'sql_error' => $mysqli->error]);
    exit;
}

if (!empty($params)) {
    // bind params dynamically (preserve original approach)
    $bind_names = [];
    // Note: bind_param requires variables by reference
    $bind_names[] = & $types;
    for ($i=0; $i < count($params); $i++) {
        $bind_name = 'bind' . $i;
        $$bind_name = $params[$i];
        $bind_names[] = &$$bind_name;
    }
    call_user_func_array([$stmt, 'bind_param'], $bind_names);
}

if (!$stmt->execute()) {
    echo json_encode(['success' => false, 'error' => 'db_execute_failed', 'sql_error' => $stmt->error]);
    $stmt->close();
    exit;
}

$res = $stmt->get_result();
$items = [];
while ($r = $res->fetch_assoc()) {
    $items[] = [
        'id' => $r['id'],
        'emp_id' => $r['emp_id'],
        'name' => $r['name'],
        'company' => $r['company'],
        'phone' => $r['phone'],
        'phones' => $r['phones'],
        'email' => $r['email']
    ];
}
$res->free();
$stmt->close();

$out = ['success' => true, 'items' => $items];
echo json_encode($out);
exit;
