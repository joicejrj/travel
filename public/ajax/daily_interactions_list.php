<?php

// public/ajax/document_templates.php
session_start();

require_once __DIR__ . '/../../config/db.php';        // must provide $mysqli
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json');


// helpful utility: send JSON response
function json_exit($arr) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($arr);
    exit;
}

// Whitelist sortable columns for DataTables ordering to avoid SQL injection
$dt_columns = [
    // 0 => 'i.date',           // DataTables column index => DB expression
    0 => "concat(i.date,' ',i.time)", 
    // 1 => 'i.time',
    1 => 'i.contact_name',
    2 => 'ct.name',          // contact_type
    // 3 => 'i.subject',
    3 => 'ch.name',          // channel
    4 => 'sc.name',          // scenario
    5 => 'i.status',          // status
    // 7 => 'p.name'            // owner
];

// Common fields mapping - easy to extend: key => DB column
// If you add new display/edit fields later, add here and update JS columns list
$field_map = [
    'contact_name' => 'i.contact_name',
    'contact_phone' => 'i.contact_phone',
    'contact_email' => 'i.contact_email',
    'subject' => 'i.subject',
    'notes' => 'i.notes',
    'owner_id' => 'i.owner_id',
    'priority' => 'i.priority',
    'follow_date' => 'i.follow_date',
    'follow_time' => 'i.follow_time',
    'channel_id' => 'i.channel_id',
    'contact_type_id' => 'i.contact_type_id',
    'scenario_id' => 'i.scenario_id'
];

/* -------------------------
   AJAX API HANDLER
   ------------------------- */
$action = $_REQUEST['action'] ?? '';

if ($action) {
    // All AJAX responses are JSON
    if ($action === 'list') {
        // DataTables server-side processing
        // Expected GET/POST params: draw, start, length, search[value], order etc.
        $draw = intval($_GET['draw'] ?? $_POST['draw'] ?? 0);
        $start = intval($_GET['start'] ?? $_POST['start'] ?? 0);
        $length = intval($_GET['length'] ?? $_POST['length'] ?? 25);
        $searchValue = trim($_GET['search']['value'] ?? $_POST['search']['value'] ?? '');

        // Filters (from filter UI) - optional
        $dateRange = $_GET['date_range'] ?? $_POST['date_range'] ?? '';
        $channel = $_GET['channel'] ?? $_POST['channel'] ?? '';
        $contact_type = $_GET['contact_type'] ?? $_POST['contact_type'] ?? '';
        $scenario = $_GET['scenario'] ?? $_POST['scenario'] ?? '';
        $owner_id = $_GET['owner_id'] ?? $_POST['owner_id'] ?? '';
        $assigned_to = $_GET['assigned_to'] ?? $_POST['assigned_to'] ?? '';
        $status = $_GET['status'] ?? $_POST['status'] ?? '';

        // Build WHERE clauses & params array
        $where = " WHERE 1=1 ";
        $params = [];
        $types = '';

        // date_range format from daterangepicker: "YYYY-MM-DD - YYYY-MM-DD"
        if ($dateRange) {
            $parts = explode(' - ', $dateRange);
            if (count($parts) === 2) {
                $from = trim($parts[0]);
                $to = trim($parts[1]);
                // inclusive: add whole day to to
                $where .= " AND i.date BETWEEN ? AND ? ";
                $types .= 'ss';
                $params[] = date("Y-m-d",strtotime($from));
                $params[] = date("Y-m-d",strtotime($to));
            }
        }

        if ($channel !== '') {
            $where .= " AND i.channel_id = ? ";
            $types .= 'i';
            $params[] = intval($channel);
        }
        if ($contact_type !== '') {
            $where .= " AND i.contact_type_id = ? ";
            $types .= 'i';
            $params[] = intval($contact_type);
        }
        if ($scenario !== '') {
            $where .= " AND i.scenario_id = ? ";
            $types .= 'i';
            $params[] = intval($scenario);
        }
        if ($owner_id !== '') {
            $where .= " AND i.owner_id = ? ";
            $types .= 'i';
            $params[] = intval($owner_id);
        }
        if ($assigned_to !== '') {
            $where .= " AND i.assigned_to = ? ";
            $types .= 'i';
            $params[] = intval($assigned_to);
        }
        if ($status !== '') {
            $where .= " AND i.status = ? ";
            $types .= 's';
            $params[] = $status;
        }

        // Global search across a few columns
        if ($searchValue !== '') {
            $where .= " AND (i.contact_name LIKE ? OR i.subject LIKE ? OR i.contact_phone LIKE ? OR i.contact_email LIKE ?) ";
            $sv = '%' . $searchValue . '%';
            $types .= 'ssss';
            $params[] = $sv; $params[] = $sv; $params[] = $sv; $params[] = $sv;
        }

        // Ordering
        $order_sql = " ORDER BY i.date DESC, i.time DESC ";
        if (isset($_GET['order']) || isset($_POST['order'])) {
            $order = $_GET['order'] ?? $_POST['order'];
            if (is_array($order) && count($order)) {
                $ord = $order[0];
                $colIdx = intval($ord['column']);
                $dir = strtoupper($ord['dir']) === 'ASC' ? 'ASC' : 'DESC';
                if (isset($dt_columns[$colIdx])) {
                    $order_sql = " ORDER BY {$dt_columns[$colIdx]} $dir ";
                }
            }
        }

        // Count total records
        $sqlCount = "SELECT COUNT(*) AS cnt FROM interactions i";
        $stmt = $mysqli->prepare($sqlCount);
        $stmt->execute();
        $res = $stmt->get_result();
        $totalRecords = 0;
        if ($row = $res->fetch_assoc()) $totalRecords = intval($row['cnt']);
        $stmt->close();

        // Count filtered
        $sqlFiltered = "SELECT COUNT(*) AS cnt
                        FROM interactions i
                        LEFT JOIN channels ch ON i.channel_id = ch.id
                        LEFT JOIN contact_types ct ON i.contact_type_id = ct.id
                        LEFT JOIN scenarios sc ON i.scenario_id = sc.id
                        LEFT JOIN people p ON i.owner_id = p.id
                        LEFT JOIN people ass ON i.assigned_to = ass.id
                        {$where}";
        $stmt = $mysqli->prepare($sqlFiltered);
        if ($types !== '') {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $filteredRecords = 0;
        if ($row = $res->fetch_assoc()) $filteredRecords = intval($row['cnt']);
        $stmt->close();

        // Fetch paginated rows
        $sql = "SELECT i.*, ch.name AS channel_name, ct.name AS contact_type_name, ct.slug as contact_type_slug, ct.edit_url as contact_type_edit_url,
                       sc.name AS scenario_name, p.name AS owner_name, ass.name as assigned_name
                FROM interactions i
                LEFT JOIN channels ch ON i.channel_id = ch.id
                LEFT JOIN contact_types ct ON i.contact_type_id = ct.id
                LEFT JOIN scenarios sc ON i.scenario_id = sc.id
                LEFT JOIN people p ON i.owner_id = p.id
                LEFT JOIN people ass ON i.assigned_to = ass.id
                {$where}
                {$order_sql}
                LIMIT ?, ?";

        // append start/length to params
        $types2 = $types . 'ii';
        $params2 = $params;
        $params2[] = $start;
        $params2[] = $length;

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) {
            json_exit(['error' => 'prepare_failed', 'sql_error' => $mysqli->error]);
        }

        if ($types2 !== '') {
            // bind dynamically
            $bind_names = [];
            $bind_names[] = $types2;
            foreach ($params2 as $k => $v) {
                $bind_names[] = &$params2[$k];
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_names);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($r = $res->fetch_assoc()) {
            $user_name = "";
            $utable = $r['contact_type_slug']=='customer'?'customers':($r['contact_type_slug']=='new'?'contacts':($r['contact_type_slug']=='employee'?'recruiters':($r['contact_type_slug']=='vendor'?'suppliers':($r['contact_type_slug']=='existing-employee'?'employees':($r['contact_type_slug']=='existing-contact'?'contacts':'employees')))));
            if($r['contact_entity_id']!='') {
                $getc = $db->get($utable,array('id'=>$r['contact_entity_id']),'name');
                $user_name = $getc?$getc->name:'';
            }

            // Build row for DataTables (match columns in JS)
            $row = [];
            // $row[] = htmlspecialchars($r['date'] . ' ' . substr($r['time'],0,5));
            $dated = $r['date']==$date?'Today':($r['date']==date("Y-m-d",strtotime("-1 day"))?'Yesterday':date("d M Y",strtotime($r['date'])));
            $row[] = htmlspecialchars($dated." ".date("h:i A",strtotime(substr($r['time'],0,5))));
            // $row[] = htmlspecialchars($r['contact_name'] ?: '—');
            $row[] = htmlspecialchars($user_name ?: '—');
            $row[] = htmlspecialchars($r['subject'] ?: '');
            $channel = htmlspecialchars($r['channel_name'] ?: '');
            $channel .= $r['itype']=='OUT'?'<span class="badge bg-success" style="padding: 0.2em 0.4em !important; margin-left: 0.3em;">OUT</span>':'<span class="badge bg-info" style="padding:0.2em 0.4em !important; margin-left: 0.3em;">IN</span>';
            $row[] = $channel;
            $row[] = htmlspecialchars($r['contact_type_name'] ?: '-');
            $row[] = htmlspecialchars($r['scenario_name'] ?: '-');
            $row[] = htmlspecialchars($r['assigned_name'] ?: '-');
            
            $editUrlAttr = isset($r['contact_type_edit_url']) ? htmlspecialchars($r['contact_type_edit_url'], ENT_QUOTES) : '';
            $entityIdAttr = isset($r['contact_entity_id']) ? (int)$r['contact_entity_id'] : '';
            $editurl = './?page='.$editUrlAttr.'&id='.$entityIdAttr;

            // $actions = '<div class="d-flex gap-1"><button class="btn btn-sm btn-outline-primary" onclick="openFollowups(' . $r['id'] . ')"><i class="fa fa-comments"></i></button><button class="btn btn-sm btn-outline-info" onclick="openInteractionDocuments('.$r['id'].')" title="View Documents"><i class="fa fa-paperclip"></i></button><button type="button" class="btn btn-sm btn-outline-primary btn-view" data-id="'.(int)$r['id'].'" title="View"><i class="fa fa-eye"></i></button><button type="button" class="btn btn-sm btn-outline-success btn-go" data-contactediturl="'.$editUrlAttr.'" data-contactentityid="'.$entityIdAttr.'" data-id="'.(int)$r['id'].'" title="Open Interaction"><i class="fa fa-arrow-up-right-from-square"></i></button><button type="button" class="btn btn-sm btn-outline-warning btn-edit" data-id="'.(int)$r['id'].'" title="Edit"><i class="fa fa-edit"></i></button></div>';
            $actions = '<div class="d-flex gap-1"><button type="button" class="btn btn-sm btn-outline-primary btn-view" data-id="'.(int)$r['id'].'" data-contactediturl="'.$editurl.'" title="Manage"><i class="fa fa-cog"></i> Manage</button>';
            $row[] = $actions;

            $stat = $r['status'] ?: 'open';
            $stclass = $stat=='open'?'primary':($stat=='closed'?'success':'warning');
            $stb = '<div class="badge bg-'.$stclass.'-subtle text-'.$stclass.' rounded-pill px-3 py-2">'.ucfirst($stat).'</div>';
            $row[] = $stb;
            
            $row[] = $r['nature']!=''?'<div class="badge bg-info-subtle text-secondary rounded-pill px-2 py-1" style="font-size: 0.8em;"><i class="fa fa-tag me-1"></i>'.ucfirst($r['nature']).'</div>': '';


            // include raw object for convenience (DataTables allows passing additional data via row object)
            $rowObj = [
                'DT_RowAttr' => ['data-id' => (int)$r['id']],
                'id' => (int)$r['id'],
                'cells' => $row
            ];

            $data[] = $rowObj;
        }
        $stmt->close();

        // DataTables expects 'data' as array of arrays; we need to output the cells arrays.
        $outData = array_map(function($r){ return $r['cells']; }, $data);

        json_exit([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $outData
        ]);
    }

    if ($action === 'load') {
        // Load a single record by id (for view or edit)
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        if ($id <= 0) json_exit(['success' => false, 'error' => 'invalid_id']);

        $sql = "SELECT i.*, ch.name AS channel_name, ct.name AS contact_type_name, ct.slug as contact_type_slug,
                       sc.name AS scenario_name, p.name AS owner_name, ass.name as assigned_name
                FROM interactions i
                LEFT JOIN channels ch ON i.channel_id = ch.id
                LEFT JOIN contact_types ct ON i.contact_type_id = ct.id
                LEFT JOIN scenarios sc ON i.scenario_id = sc.id
                LEFT JOIN people p ON i.owner_id = p.id
                LEFT JOIN people ass ON i.assigned_to = ass.id
                WHERE i.id = ? LIMIT 1";
        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if (!$row) json_exit(['success' => false, 'error' => 'not_found']);

        $user_name = "";
        $contact_name = "";
        $ufolder = "uploads/";
        // $utable = $row['contact_type_slug']=='customer'?'customers':($row['contact_type_slug']=='new'?'contacts':($row['contact_type_slug']=='employee'?'employees':($row['contact_type_slug']=='employee'?'recruiters':($row['contact_type_slug']=='vendor'?'suppliers':($row['contact_type_slug']=='existing-employee'?'employees':($row['contact_type_slug']=='existing-contact'?'contacts':'employees'))))));
        $utable = $row['contact_type_slug']=='customer'?'customers':($row['contact_type_slug']=='new'?'contacts':($row['contact_type_slug']=='employee'?'recruiters':($row['contact_type_slug']=='vendor'?'suppliers':($row['contact_type_slug']=='existing-employee'?'employees':($row['contact_type_slug']=='existing-contact'?'contacts':'employees')))));
        $ufolder .= $utable.'/interactions/';
        if($row['contact_entity_id']!='') {
            $getc = $db->get($utable,array('id'=>$row['contact_entity_id']),'name');
            $user_name = $getc?$getc->name:'';
        }
        $row['contact_name'] = $user_name;
        $uctable = $row['contact_type_slug']=='customer'?'customers_contacts':($row['contact_type_slug']=='new'?'contacts_contacts':($row['contact_type_slug']=='employee'?'recruiters_contacts':($row['contact_type_slug']=='vendor'?'suppliers_contacts':($row['contact_type_slug']=='existing-employee'?'employees_contacts':($row['contact_type_slug']=='existing-contact'?'contacts_contacts':'employees_contacts')))));
        if($row['entity_contact_id']!='') {
            $getc = $db->get($uctable,array('id'=>$row['entity_contact_id']),'name');
            $contact_name = $getc?$getc->name:'';
        }
        $row['related_contact_name'] = $contact_name;
        $row['document_fileurl'] = $row['document_file']!=''?$ufolder.$row['document_file']:'';
        $ext = $row['document_file']!=''?strtolower(pathinfo($row['document_file'], PATHINFO_EXTENSION)):'';
        $dtype = $row['document_file']!=''?(($ext === 'pdf') ? 'pdf' : (in_array($ext, ['mp4','mov','avi','webm']) ? 'video' : 'image')):'';
        $row['document_type'] = $dtype;

        $remployees = [];
        if($row['related_employee_ids']!='') {
            $remps = explode(",",$row['related_employee_ids']);
            if(!empty($remps)) {
                $reids = implode("','",$remps);
                $getremp = $db->get('employees',array('#all'=>1,'#fetch'=>'column','#cus'=>"(id in ('".$reids."'))"),'name');
                $remployees = $getremp->data; // array of related employees names
            }
        }
        $row['related_employees'] = $remployees;

        $related_customer = "";
        if($row['related_customer_id']!='') {
            $getrcus = $db->get('customers',array('id'=>$row['related_customer_id']),'name');
            $related_customer = $getrcus?$getrcus->name:'';
        }
        $row['related_customer'] = $related_customer;
    	
        
        $row['dated'] = $row['date']==$date?'Today':($row['date']==date("Y-m-d",strtotime("-1 day"))?'Yesterday':date("d M Y",strtotime($row['date'])));
        $row['timed'] = date("h:i A",strtotime($row['time']));
    	
        $row['follow_dated'] = $row['follow_date']!=''&&$row['follow_date']!='0000-00-00'?date("d M Y",strtotime($row['follow_date'])):'-';
        $row['follow_timed'] = $row['follow_dated']!='-'&&$row['follow_time']!=''&&$row['follow_time']!='00:00:00'?date("h:i A",strtotime($row['follow_time'])):'';
        $row['follow_time'] = $row['follow_dated']!='-'?date("h:i A",strtotime($row['follow_time'])):'';
        
        // return the row as-is (caller will use fields)
        json_exit(['success' => true, 'interaction' => $row]);
    }

    if ($action === 'update') {
        // Update basic fields from edit modal. Expect JSON POST body or form params.
        // We'll parse JSON body if content-type indicates JSON.
        $input = null;
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($ct, 'application/json') !== false) {
            $body = file_get_contents('php://input');
            $input = json_decode($body, true);
        } else {
            $input = $_POST;
        }

        $id = intval($input['id'] ?? 0);
        if ($id <= 0) json_exit(['success' => false, 'error' => 'invalid_id']);

        // Allowed editable columns — extend $editable_map to add new editable fields
        $editable_map = [
            // 'contact_name' => 'contact_name',
            // 'contact_phone' => 'contact_phone',
            // 'contact_email' => 'contact_email',
            'subject' => 'subject',
            'notes' => 'notes',
            'status' => 'status',
            'nature' => 'nature',
            'assigned_to' => 'assigned_to',
            'priority' => 'priority',
            // 'follow_date' => 'follow_date',
            // 'follow_time' => 'follow_time'
        ];

        $set_clauses = [];
        $params = [];
        $types = '';

        foreach ($editable_map as $k => $col) {
            if (array_key_exists($k, $input)) {
                $val = $input[$k];
                // simple trimming + normalization
                if (is_string($val)) $val = trim($val);
                $set_clauses[] = "`{$col}` = ?";
                $params[] = $val === '' ? null : $val;
                // owner_id is integer
                if (in_array($k, ['owner_id', 'assigned_to'])) {
                    $types .= 'i';  // integer fields
                } else {
                    $types .= 's';  // string fields
                }

                if($col=='assigned_to' && $val!='') {
                    $geta = $db->get('people',array('id'=>$val),'name');
                    $pname = $geta?$geta->name."[$val]":'-';
                    $site->agent_log("Interaction #$id is assigned to $pname");
                }

            }
        }

        if (count($set_clauses) === 0) {
            json_exit(['success' => false, 'error' => 'no_fields']);
        }

        $sql = "UPDATE interactions SET " . implode(', ', $set_clauses) . " WHERE id = ? LIMIT 1";
        $types .= 'i';
        $params[] = $id;

        $stmt = $mysqli->prepare($sql);
        if (!$stmt) json_exit(['success' => false, 'error' => 'prepare_failed', 'sql_error' => $mysqli->error]);

        $site->agent_log("Interaction #$id is updated (summary: ".$input['subject'].")");

        // bind params dynamically
        $bind_names = [];
        $bind_names[] = $types;
        foreach ($params as $k => $v) {
            $bind_names[] = &$params[$k];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);

        $ok = $stmt->execute();
        $err = $stmt->error;
        $stmt->close();

        if ($ok) json_exit(['success' => true]);
        else json_exit(['success' => false, 'error' => 'update_failed', 'sql_error' => $err]);
    }

    if ($action === 'summary') {
        // Extract filters (same as DataTable)
        $dateRange = $_GET['date_range'] ?? '';
        $channel = $_GET['channel'] ?? '';
        $contact_type = $_GET['contact_type'] ?? '';
        $scenario = $_GET['scenario'] ?? '';
        $owner_id = $_GET['owner_id'] ?? '';
        $assigned_to = $_GET['assigned_to'] ?? '';

        $where = " WHERE 1=1 ";
        $params = [];
        $types = "";

        if ($dateRange) {
            $parts = explode(" - ", $dateRange);
            if (count($parts) === 2) {
                $where .= " AND i.date BETWEEN ? AND ? ";
                $types .= "ss";
                $params[] = date("Y-m-d",strtotime($parts[0]));
                $params[] = date("Y-m-d",strtotime($parts[1]));
            }
        }

        if ($channel !== '') { $where .= " AND i.channel_id=? "; $types.='i'; $params[]=$channel; }
        if ($contact_type !== '') { $where .= " AND i.contact_type_id=? "; $types.='i'; $params[]=$contact_type; }
        if ($scenario !== '') { $where .= " AND i.scenario_id=? "; $types.='i'; $params[]=$scenario; }
        if ($owner_id !== '') { $where .= " AND i.owner_id=? "; $types.='i'; $params[]=$owner_id; }
        if ($assigned_to !== '') { $where .= " AND i.assigned_to=? "; $types.='i'; $params[]=$assigned_to; }

        // total interactions
        $sqlTotal = "SELECT COUNT(*) AS c FROM interactions i $where";
        $stmt = $mysqli->prepare($sqlTotal);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
        $stmt->close();

        // open
        $sqlEnquiries = "SELECT COUNT(*) AS c FROM interactions i $where AND i.status = 'open'"; 
        $stmt = $mysqli->prepare($sqlEnquiries);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $open = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
        $stmt->close();

        // closed
        $sqlComplaints = "SELECT COUNT(*) AS c FROM interactions i $where AND i.status = 'closed'";
        $stmt = $mysqli->prepare($sqlComplaints);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $closed = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
        $stmt->close();

        // working
        $sqlPending = "SELECT COUNT(*) AS c FROM interactions i $where AND i.status = 'working'";
        $stmt = $mysqli->prepare($sqlPending);
        if ($types) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $working = $stmt->get_result()->fetch_assoc()['c'] ?? 0;
        $stmt->close();

        json_exit([
            'success' => true,
            'total' => $total,
            'open' => $open,
            'closed' => $closed,
            'working' => $working
        ]);
    }

    //
    // FOLLOWUP: LIST
    //
    if ($action === "followup_list") {

        $interaction = intval($_POST['id'] ?? 0);

        if ($interaction <= 0) {
            echo json_encode(["status" => false, "msg" => "Invalid interaction"]);
            exit;
        }

        $sql = "
            SELECT f.*, 
                   DATE_FORMAT(f.created_at, '%d %b %Y %h:%i %p') AS created_atd
            FROM interactions_followup f
            WHERE f.interaction_id = $interaction
            ORDER BY f.id ASC
        ";

        $res = $mysqli->query($sql);
        $data = [];

        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode(["status" => true, "data" => $data]);
        exit;
    }

    //
    // FOLLOWUP: ADD
    //
    if ($action === "followup_add") {

        $interaction = intval($_POST['interaction_id'] ?? 0);
        $note_text   = trim($_POST['note_text'] ?? '');
        $agent_id    = intval($_POST['created_by'] ?? 0);
        $agent_name  = trim($_POST['created_by_name'] ?? '');

        if ($interaction <= 0 || $note_text === '') {
            echo json_encode(["status" => false, "msg" => "Followup note is required"]);
            exit;
        }

        // Insert followup
        $stmt = $mysqli->prepare("
            INSERT INTO interactions_followup
            (interaction_id, note_text, created_by, created_by_name)
            VALUES (?,?,?,?)
        ");
        $stmt->bind_param("isis", $interaction, $note_text, $agent_id, $agent_name);
        $stmt->execute();

        $new_id = $stmt->insert_id;

        // Get the inserted row (for front-end display)
        $row = $mysqli->query("
            SELECT *, DATE_FORMAT(created_at, '%d %b %Y %h:%i %p') AS created_atd
            FROM interactions_followup
            WHERE id = $new_id
        ")->fetch_assoc();

        $uid = "";
        $getint = $db->get('[interactions=i]',array('i.id'=>$interaction,'#join'=>"left join contact_types as ct on ct.id=i.contact_type_id"),'i.contact_type_id,ct.slug as contact_type_slug,i.contact_entity_id');
        if($getint) {
            $uid = $getint->contact_entity_id;
            switch ($getint->contact_type_slug) {
                case 'customer':
                    $utype = "customer";
                    break;
                case 'employee':
                    $utype = "recruiter"; //check case name employee
                    break;
                case 'vendor':
                    $utype = "supplier";
                    break;
                case 'existing-employee':
                    $utype = "employee";
                    break;
                default:
                    $uid = "";
                    $utype = "";
                    break;
            }
        }
        // Log
        $snippet = substr($note_text, 0, 120);
        $site->agent_log("Added interaction followup for interaction #$interaction - $snippet", $uid, $utype,'notimeline');

        echo json_encode([
            "status" => true,
            "msg" => "Followup added",
            "data" => $row
        ]);
        exit;
    }


    /* ============================
       INTERACTION DOCUMENTS : LIST
    ============================ */
    if ($action === 'documents_list') {

        $interaction_id = intval($_GET['interaction_id'] ?? 0);
        if ($interaction_id <= 0) {
            json_exit(['success' => false, 'error' => 'invalid_interaction']);
        }

        $sql = "
            SELECT 
                id,
                label,
                file_name,
                file_type,
                expiry_date,
                created_by,
                DATE_FORMAT(created_at, '%d %b %Y') AS created_atd
            FROM interactions_documents
            WHERE interaction_id = ?
            ORDER BY id DESC
        ";

        $stmt = $mysqli->prepare($sql);
        $stmt->bind_param("i", $interaction_id);
        $stmt->execute();
        $res = $stmt->get_result();

        $data = [];
        while ($row = $res->fetch_assoc()) {
            $row['file_url'] = 'uploads/interactions/' . $row['file_name'];
            $data[] = $row;
        }
        $stmt->close();

        json_exit([
            'success' => true,
            'data' => $data
        ]);
    }

    /* ============================
       INTERACTION DOCUMENTS : ADD
    ============================ */
    if ($action === 'documents_add') {

        $interaction_id = intval($_POST['interaction_id'] ?? 0);
        $label          = trim($_POST['label'] ?? '');
        $expiry_date    = $_POST['expiry_date'] ?? null;
        $created_by     = $_SESSION['person_name'] ?? 'system';

        if ($interaction_id <= 0 || $label === '') {
            json_exit(['success' => false, 'error' => 'missing_fields']);
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            json_exit(['success' => false, 'error' => 'file_upload_failed']);
        }

        /* Upload directory */
        $uploadDir = __DIR__ . '/../../uploads/interactions/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

        /* Detect file type */
        if ($ext === 'pdf') {
            $file_type = 'pdf';
        } elseif (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
            $file_type = 'image';
        } elseif (in_array($ext, ['mp4','mov','avi','webm'])) {
            $file_type = 'video';
        } else {
            json_exit(['success' => false, 'error' => 'invalid_file_type']);
        }

        $newName = 'interaction_'.$interaction_id.'_'.uniqid().'.'.$ext;
        $dest = $uploadDir . $newName;

        if (!move_uploaded_file($_FILES['file']['tmp_name'], $dest)) {
            json_exit(['success' => false, 'error' => 'save_failed']);
        }

        /* Insert DB record */
        $stmt = $mysqli->prepare("
            INSERT INTO interactions_documents
            (interaction_id, label, file_name, file_type, expiry_date, created_by, created_at)
            VALUES (?,?,?,?,?,?,NOW())
        ");
        $stmt->bind_param(
            "isssss",
            $interaction_id,
            $label,
            $newName,
            $file_type,
            $expiry_date,
            $created_by
        );
        $stmt->execute();
        $stmt->close();

        $site->agent_log("Document added to interaction #$interaction_id ($label)");

        json_exit(['success' => true]);
    }

    /* ============================
       INTERACTION DOCUMENTS : DELETE
    ============================ */
    if ($action === 'documents_delete') {

        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) {
            json_exit(['success' => false, 'error' => 'invalid_id']);
        }

        /* Fetch document */
        $stmt = $mysqli->prepare("
            SELECT interaction_id, file_name
            FROM interactions_documents
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        $doc = $res->fetch_assoc();
        $stmt->close();

        if (!$doc) {
            json_exit(['success' => false, 'error' => 'not_found']);
        }

        /* Delete file */
        $filePath = __DIR__ . '/../../uploads/interactions/' . $doc['file_name'];
        if (is_file($filePath)) {
            @unlink($filePath);
        }

        /* Delete record */
        $stmt = $mysqli->prepare("DELETE FROM interactions_documents WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();

        $site->agent_log("Document deleted from interaction #".$doc['interaction_id']);

        json_exit(['success' => true]);
    }



    // unknown action
    json_exit(['success' => false, 'error' => 'unknown_action']);
}

?>