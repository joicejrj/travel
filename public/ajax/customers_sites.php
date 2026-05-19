<?php
// public/ajax/customers_sites.php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../lib/helpers.php';
require_once __DIR__ . '/../../public/_auth.php';
ob_clean();

header('Content-Type: application/json; charset=utf-8');

$action = $_POST['action'] ?? '';
$customer_id = intval($_POST['customer_id'] ?? 0);

if ($action === 'fetch') {

    $stmt = $mysqli->prepare("
        SELECT id, site_name, site_contact, site_address, site_location
        FROM customers_sites
        WHERE customer_id=?
        ORDER BY id ASC
    ");
    $stmt->bind_param("i", $customer_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $sites = [];

    while ($r = $res->fetch_assoc()) {

        // fetch trades (MySQLi-safe)
        $tstmt = $mysqli->prepare("
            SELECT trade_id FROM customer_site_trades WHERE site_id=?
        ");
        $tstmt->bind_param("i", $r['id']);
        $tstmt->execute();
        $tres = $tstmt->get_result();

        $r['trades'] = [];
        while ($tr = $tres->fetch_assoc()) {
            $r['trades'][] = $tr['trade_id'];
        }

        $sites[] = $r;
    }

    echo json_encode($sites);
    exit;
}

if ($action === 'create') {

    $site_name     = $_POST['site_name'] ?? '';
    $site_contact  = $_POST['site_contact'] ?? '';
    $site_address  = $_POST['site_address'] ?? '';
    $site_location = $_POST['site_location'] ?? '';
    $created_by    = $_SESSION['user_name'] ?? 'Admin';

    $stmt = $mysqli->prepare("
        INSERT INTO customers_sites
        (customer_id, site_name, site_contact, site_address, site_location, created_by)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        "isssss",
        $customer_id,
        $site_name,
        $site_contact,
        $site_address,
        $site_location,
        $created_by
    );

    if ($stmt->execute()) {

        $site_id = $mysqli->insert_id;   // ✅ FIXED

        // save trades
        $trades = $_POST['site_trades'] ?? [];

        $tstmt = $mysqli->prepare("
            INSERT INTO customer_site_trades (site_id, trade_id)
            VALUES (?, ?)
        ");

        foreach ($trades as $tid) {
            $tid = (int)$tid;
            $tstmt->bind_param("ii", $site_id, $tid);
            $tstmt->execute();
        }

        $site->agent_log("New Site {$site_name} [{$site_id}] created", $customer_id);

        echo json_encode([
            'success' => true,
            'site' => [
                'id' => $site_id,
                'site_name' => $site_name,
                'site_contact' => $site_contact,
                'site_address' => $site_address,
                'site_location' => $site_location
            ]
        ]);

    } else {
        echo json_encode(['success'=>false,'error'=>$mysqli->error]);
    }
    exit;
}

if ($action === 'update') {

    $site_id       = intval($_POST['id'] ?? 0);
    $site_name     = $_POST['site_name'] ?? '';
    $site_contact  = $_POST['site_contact'] ?? '';
    $site_address  = $_POST['site_address'] ?? '';
    $site_location = $_POST['site_location'] ?? '';

    $stmt = $mysqli->prepare("
        UPDATE customers_sites
        SET site_name=?, site_contact=?, site_address=?, site_location=?
        WHERE id=? AND customer_id=?
    ");
    $stmt->bind_param(
        "ssssii",
        $site_name,
        $site_contact,
        $site_address,
        $site_location,
        $site_id,
        $customer_id
    );

    if ($stmt->execute()) {

        // new trades from form
        $trades = $_POST['site_trades'] ?? [];
        $trades = array_map('intval', $trades);

        /* =========================================
           DELETE RATES FOR REMOVED TRADES
        ========================================= */
        if (count($trades)) {

            // delete rates not in selected trades
            $placeholders = implode(',', array_fill(0, count($trades), '?'));
            $types = str_repeat('i', count($trades) + 2);

            $sql = "
              DELETE FROM customer_trade_rates
              WHERE site_id=? AND customer_id=?
              AND trade_id NOT IN ($placeholders)
            ";

            $rstmt = $mysqli->prepare($sql);
            $params = array_merge([$site_id, $customer_id], $trades);

            $bind = [];
            $bind[] = &$types;
            foreach ($params as $k => $v) {
                $bind[] = &$params[$k];
            }
            call_user_func_array([$rstmt, 'bind_param'], $bind);
            $rstmt->execute();

        } else {
            // no trades selected → delete all rates for this site
            $rstmt = $mysqli->prepare("
              DELETE FROM customer_trade_rates
              WHERE site_id=? AND customer_id=?
            ");
            $rstmt->bind_param("ii", $site_id, $customer_id);
            $rstmt->execute();
        }

        /* =========================================
           RESET SITE ↔ TRADE LINKS
        ========================================= */
        $dstmt = $mysqli->prepare("
            DELETE FROM customer_site_trades WHERE site_id=?
        ");
        $dstmt->bind_param("i", $site_id);
        $dstmt->execute();

        if (count($trades)) {
            $istmt = $mysqli->prepare("
                INSERT INTO customer_site_trades (site_id, trade_id)
                VALUES (?, ?)
            ");
            foreach ($trades as $tid) {
                $istmt->bind_param("ii", $site_id, $tid);
                $istmt->execute();
            }
        }

        $site->agent_log(
            "Site {$site_name} [{$site_id}] updated (rates cleaned)",
            $customer_id
        );

        echo json_encode([
            'success' => true,
            'site' => [
                'id' => $site_id,
                'site_name' => $site_name,
                'site_contact' => $site_contact,
                'site_address' => $site_address,
                'site_location' => $site_location
            ]
        ]);
    }
    else {
        echo json_encode(['success'=>false,'error'=>$mysqli->error]);
    }
    exit;
}

if ($action === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    $stmt = $mysqli->prepare("DELETE FROM customers_sites WHERE id=? AND customer_id=?");
    $stmt->bind_param("ii", $id, $customer_id);
    $ok = $stmt->execute();
    if ($ok) {
        $site->agent_log("Site with id ".$id." is deleted",$customer_id);
        echo json_encode(['success'=>true]);
    }
    else echo json_encode(['success'=>false, 'error'=>$mysqli->error]);
    exit;
}

/* Bulk save: receive JSON array "sites" - each item may have id (update) or empty id (create).
   We'll iterate and apply create/update. We also delete any persisted sites not present in payload.
*/
if ($action === 'save_bulk') {
    $raw = $_POST['sites'] ?? '[]';
    $items = json_decode($raw, true);
    if (!is_array($items)) { echo json_encode(['success'=>false,'error'=>'Invalid payload']); exit; }

    $savedIds = [];
    foreach ($items as $it) {
        $id = intval($it['id'] ?? 0);
        $site_name = $it['site_name'] ?? '';
        $site_contact = $it['site_contact'] ?? '';
        $site_address = $it['site_address'] ?? '';
        $site_location = $it['site_location'] ?? '';
        if ($id > 0) {
            $stmt = $mysqli->prepare("UPDATE customers_sites SET site_name=?, site_contact=?, site_address=?, site_location=? WHERE id=? AND customer_id=?");
            $stmt->bind_param("ssssii", $site_name, $site_contact, $site_address, $site_location, $id, $customer_id);
            $stmt->execute();
            $savedIds[] = $id;

            $site->agent_log("Details of Site ".$site_name." are updated",$customer_id);

        } else {
            $created_by = $_SESSION['user_name'] ?? 'Admin';
            $stmt = $mysqli->prepare("INSERT INTO customers_sites (customer_id, site_name, site_contact, site_address, site_location, created_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssss", $customer_id, $site_name, $site_contact, $site_address, $site_location, $created_by);
            $stmt->execute();
            $savedIds[] = $mysqli->insert_id;

            $site->agent_log("New Site ".$site_name." is created",$customer_id);
        }
    }

    // Optionally remove rows that are not in savedIds (i.e. user deleted them client-side)
    if (count($savedIds)) {
        $placeholders = implode(',', array_fill(0, count($savedIds), '?'));
        // we'll delete where customer_id = ? AND id NOT IN (savedIds)
        // Prepared statement building
        $types = str_repeat('i', count($savedIds)+1);
        $sql = "DELETE FROM customers_sites WHERE customer_id=? AND id NOT IN ($placeholders)";
        $stmt = $mysqli->prepare($sql);
        // bind params dynamically
        $vals = array_merge([$customer_id], $savedIds);
        $refArr = [];
        foreach ($vals as $k => $v) $refArr[$k] = &$vals[$k];
        array_unshift($refArr, $types); // first param is types string
        call_user_func_array([$stmt, 'bind_param'], $refArr);
        $stmt->execute();

        $site->agent_log("Sites with id ".$placeholders." are deleted",$customer_id);
    } else {
        // no saved ids -> delete all for the customer
        $stmt = $mysqli->prepare("DELETE FROM customers_sites WHERE customer_id=?");
        $stmt->bind_param("i", $customer_id);
        $stmt->execute();

        $site->agent_log("All Sites of the customer are deleted",$customer_id);
    }

    echo json_encode(['success'=>true]);
    exit;
}

if ($action === 'fetch_trades') {

    $res = $mysqli->query("
        SELECT id, trade_name
        FROM customers_trades
        WHERE status = 1
        ORDER BY trade_name
    ");

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }

    echo json_encode($rows);
    exit;
}

echo json_encode(['success'=>false, 'error'=>'Invalid action']);
exit;
?>