<?php

require_once __DIR__.'/../../config/db.php';

$action = $_REQUEST['action'] ?? '';

/* =====================================================
   LIST AIRPORTS
===================================================== */

if($action == 'list'){

    $where = "1=1";

    if(!empty($_GET['country'])){
        $country = $mysqli->real_escape_string($_GET['country']);
        $where .= " AND country='$country'";
    }

    if($_GET['preferred'] !== '' && isset($_GET['preferred'])){
        $preferred = (int)$_GET['preferred'];
        $where .= " AND is_preferred=$preferred";
    }

    $res = $mysqli->query("
        SELECT id,code,name,country,is_preferred
        FROM airports
        WHERE $where
        ORDER BY name
    ");

    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = $row;
    }

    echo json_encode(['data'=>$data]);
    exit;
}


/* =====================================================
   GET AIRPORT
===================================================== */

if($action == 'get'){

    $id = (int)($_GET['id'] ?? 0);

    $res = $mysqli->query("
        SELECT * FROM airports WHERE id=$id
    ");

    echo json_encode($res->fetch_assoc());
    exit;
}


/* =====================================================
   SAVE AIRPORT
===================================================== */

if($action == 'save'){

    $id      = (int)($_POST['id'] ?? 0);
    $code    = strtoupper(trim($_POST['code']));
    $name    = trim($_POST['name']);
    $country = trim($_POST['country']);

    if(!$code || !$name){
        echo json_encode(['success'=>false,'msg'=>'Missing data']);
        exit;
    }

    if($id){

        $stmt = $mysqli->prepare("
            UPDATE airports
            SET code=?,name=?,country=?
            WHERE id=?
        ");

        $stmt->bind_param("sssi",$code,$name,$country,$id);

    }else{

        $stmt = $mysqli->prepare("
            INSERT INTO airports(code,name,country)
            VALUES(?,?,?)
        ");

        $stmt->bind_param("sss",$code,$name,$country);

    }

    $stmt->execute();

    echo json_encode(['success'=>true]);
    exit;
}


/* =====================================================
   DELETE AIRPORT
===================================================== */

if($action == 'delete'){

    $id = (int)($_POST['id'] ?? 0);

    $mysqli->query("DELETE FROM airports WHERE id=$id");

    echo json_encode(['success'=>true]);
    exit;
}


/* =====================================================
   TOGGLE PREFERRED
===================================================== */

if($action == 'toggle_preferred'){

    $id   = (int)($_POST['id'] ?? 0);
    $flag = (int)($_POST['is_preferred'] ?? 0);

    $stmt = $mysqli->prepare("
        UPDATE airports
        SET is_preferred=?
        WHERE id=?
    ");

    $stmt->bind_param("ii",$flag,$id);
    $stmt->execute();

    echo json_encode(['success'=>true]);
    exit;
}


/* =====================================================
   COUNTRY LIST (FOR SELECT2)
===================================================== */

if($action == 'countries'){

    $search = trim($_GET['search'] ?? '');

    $where = "";

    if($search){
        $search = $mysqli->real_escape_string($search);
        $where = "WHERE country LIKE '%$search%'";
    }

    $res = $mysqli->query("
        SELECT DISTINCT country
        FROM airports
        $where
        ORDER BY country
        LIMIT 50
    ");

    $data = [];

    while($row = $res->fetch_assoc()){
        $data[] = [
            'id'=>$row['country'],
            'text'=>$row['country']
        ];
    }

    echo json_encode($data);
    exit;
}

?>