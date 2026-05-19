<?php

require_once __DIR__ . '/../_auth.php'; // adjust if your auth path differs
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php'; // optional

$action = $_GET['action'] ?? $_POST['action'] ?? '';

/* =====================================================
   PAYMENT STATS
   action=stats
===================================================== */

if($action == 'stats'){

    $where = "1=1";

    if(!empty($_GET['daterange'])){

        $range = explode(' - ', $_GET['daterange']);

        if(count($range) == 2){

            $from = $mysqli->real_escape_string($range[0]);
            $to   = $mysqli->real_escape_string($range[1]);

            $where .= " AND DATE(created_at) BETWEEN '$from' AND '$to'";
        }
    }

    $row = $mysqli->query("
        SELECT
        COUNT(*) total,
        SUM(status='captured') success,
        SUM(status='pending') pending,
        SUM(status IN ('failed','declined')) failed
        FROM bookings_payments
        WHERE $where
    ")->fetch_assoc();

    echo json_encode([
        'success' => true,
        'data' => $row
    ]);

    exit;
}


/* =====================================================
   PAYMENTS LIST (DATATABLE)
   action=list
===================================================== */

if($action == 'list'){

    $draw  = intval($_GET['draw'] ?? 1);
    $start = intval($_GET['start'] ?? 0);
    $len   = intval($_GET['length'] ?? 10);

    $where = "1=1 AND p.booking_id IS NOT NULL";


    /* STATUS FILTER */

    if(!empty($_GET['status'])){
        $status = $mysqli->real_escape_string($_GET['status']);
        $where .= " AND p.status='$status'";
    }


    /* CURRENCY FILTER */

    if(!empty($_GET['currency'])){
        $currency = $mysqli->real_escape_string($_GET['currency']);
        $where .= " AND p.currency='$currency'";
    }


    /* DATATABLE SEARCH */

    if(!empty($_GET['search']['value'])){

        $s = $mysqli->real_escape_string($_GET['search']['value']);

        $where .= " AND (
            p.reference LIKE '%$s%'
            OR p.booking_id LIKE '%$s%'
            OR p.payment_method LIKE '%$s%'
            OR p.currency LIKE '%$s%'
        )";
    }


    /* DATE RANGE */

    if(!empty($_GET['daterange'])){

        $range = explode(' - ', $_GET['daterange']);

        if(count($range) == 2){

            $from = $mysqli->real_escape_string($range[0]);
            $to   = $mysqli->real_escape_string($range[1]);

            $where .= " AND DATE(p.created_at) BETWEEN '$from' AND '$to'";
        }
    }


    /* COLUMN ORDERING */

    $columns = [
        0 => 'p.booking_id',
        1 => 'p.reference',
        2 => 'p.amount',
        3 => 'p.status',
        4 => 'p.payment_method',
        5 => 'p.created_at'
    ];

    $orderColumn = 'p.created_at';
    $orderDir    = 'DESC';

    if(isset($_GET['order'][0])){

        $colIndex = intval($_GET['order'][0]['column']);
        $dir      = $_GET['order'][0]['dir'] === 'asc' ? 'ASC' : 'DESC';

        if(isset($columns[$colIndex])){
            $orderColumn = $columns[$colIndex];
            $orderDir    = $dir;
        }

    }

    $orderSQL = "$orderColumn $orderDir";


    /* TOTAL */

    $total = $mysqli->query("
        SELECT COUNT(*) c
        FROM bookings_payments
        WHERE booking_id IS NOT NULL
    ")->fetch_assoc()['c'];


    /* FILTERED */

    $filtered = $mysqli->query("
        SELECT COUNT(*) c
        FROM bookings_payments p
        WHERE $where
    ")->fetch_assoc()['c'];


    /* DATA */

    $res = $mysqli->query("
        SELECT p.*
        FROM bookings_payments p
        WHERE $where
        ORDER BY $orderSQL
        LIMIT $start,$len
    ");

    $data = [];

    while($r = $res->fetch_assoc()){
        $data[] = $r;
    }


    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $total,
        "recordsFiltered" => $filtered,
        "data" => $data
    ]);

    exit;
}


/* =====================================================
   INVALID ACTION
===================================================== */

echo json_encode([
    'success' => false,
    'msg' => 'Invalid action'
]);
?>