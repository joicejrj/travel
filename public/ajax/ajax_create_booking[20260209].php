<?php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'invalid_method']);
    exit;
}

$booking_id  = (int)($_POST['booking_id'] ?? 0);
$is_existing = (int)($_POST['is_existing'] ?? 0);

$scenario_id     = !empty($_POST['scenario_id']) ? (int)$_POST['scenario_id'] : null;

if($scenario_id=="1") { //Flights

    function clean_docnum($s) {
      return preg_replace('/[^A-Z0-9]/', '', strtoupper($s ?? ''));
    }

    function iso2($s) {
      return substr(preg_replace('/[^A-Z]/','', strtoupper($s ?? '')), 0, 2);
    }

    try {

        require_once __DIR__ . '/../../lib/amadeus.php';

      /* -------------------------
         1. Read & validate input
      -------------------------- */
      $offerJson = $_POST['selected_offer_json'] ?? '';
      if (!$offerJson) {
        throw new Exception('No flight selected');
      }

      $offer = json_decode($offerJson, true);
      if (!$offer) {
        throw new Exception('Invalid flight data');
      }

        $travellersInput = $_POST['travellers'] ?? [];

        if (!is_array($travellersInput) || count($travellersInput) === 0) {
          throw new Exception('Traveller details are required');
        }

    $people_no = count($travellersInput);
    $class = $_POST['class'] ?? 'ECONOMY';
    $non_stop = $_POST['non_stop'] ?? 0;

      /* -------------------------
         2. Extract route & dates
      -------------------------- */
      $itinOut = $offer['itineraries'][0] ?? null;
      if (!$itinOut || empty($itinOut['segments'])) {
        throw new Exception('Invalid itinerary');
      }

      $firstSeg = $itinOut['segments'][0];
      $lastSeg  = $itinOut['segments'][count($itinOut['segments']) - 1];

      $origin      = $firstSeg['departure']['iataCode'];
      $destination = $lastSeg['arrival']['iataCode'];
      $depDate     = substr($firstSeg['departure']['at'], 0, 10);

      $retDate = null;
      $trip_type = "one_way";
      if (!empty($offer['itineraries'][1]['segments'])) {
        $retSeg = $offer['itineraries'][1]['segments'];
        $retLast = $retSeg[count($retSeg) - 1];
        $retDate = substr($retLast['arrival']['at'], 0, 10);
        $trip_type = "round_trip";
      }

        /* -------------------------
           3. Build traveller block
        -------------------------- */
        $travellers = [];
        $tid = 1;

        foreach ($travellersInput as $t) {

          $name  = trim($t['name'] ?? '');
          $email = trim($t['email'] ?? '');
          $phone = trim($t['phone'] ?? '');
          $dob = trim($t['dob'] ?? '');

          if ($name === '' || $email === '') {
            throw new Exception('Each traveller must have name and email');
          }

          $parts = preg_split('/\s+/', strtoupper($name), 2);
          // $firstName = preg_replace('/[^A-Z]/', '', $parts[0]);
          // $lastName  = preg_replace('/[^A-Z]/', '', $parts[1] ?? 'NA');

            $rawName = strtoupper(trim($t['name']));
            $rawName = preg_replace('/[^A-Z\s]/', '', $rawName);
            $rawName = preg_replace('/\s+/', ' ', $rawName);

            $parts = explode(' ', $rawName, 2);

            $firstName = $parts[0] ?? '';
            $lastName  = $parts[1] ?? $parts[0]; // IMPORTANT

            if (strlen($firstName) < 2 || strlen($lastName) < 2) {
              throw new Exception('Traveller name must contain at least 2 letters');
            }


          $trav = [
            'id' => (string)$tid,
            'name' => [
              'firstName' => $firstName,
              'lastName'  => $lastName
            ],
            'contact' => [
              'emailAddress' => $email,
              'phones' => [[
                'deviceType' => 'MOBILE',
                'countryCallingCode' => '44',
                'number' => preg_replace('/\D+/', '', $phone)
              ]]
            ]
          ];

          /* OPTIONAL PASSPORT */
          if (!empty($t['passport_number'])) {
            $trav['documents'] = [[
              'documentType' => 'PASSPORT',
              'number' => strtoupper(preg_replace('/[^A-Z0-9]/','', $t['passport_number'])),
              'expiryDate' => $t['passport_expiry'] ?? null,
              'nationality' => strtoupper($t['passport_nationality'] ?? 'GB'),
              'holder' => true
            ]];
          }

          $travellers[] = $trav;
          $tid++;
        }


      /* -------------------------
         4. Create Amadeus order
      -------------------------- */
      $payload = [
        'data' => [
          'type' => 'flight-order',
          'flightOffers' => [ $offer ],
          'travelers' => $travellers,
          'remarks' => [
            'general' => [[
              'subType' => 'GENERAL_MISCELLANEOUS',
              'text' => 'ONLINE FLIGHT BOOKING'
            ]]
          ]
        ]
      ];

      $resp = amadeus_request(
        'POST',
        '/v1/booking/flight-orders',
        $payload,
        []
      );

      $order = $resp['data'] ?? null;
      if (!$order) {
        throw new Exception('Booking failed');
      }

      /* -------------------------
         5. Extract PNR & order ID
      -------------------------- */
      $pnr = '';
      if (!empty($order['associatedRecords'])) {
        foreach ($order['associatedRecords'] as $r) {
          if (!empty($r['reference'])) {
            $pnr = $r['reference'];
            break;
          }
        }
      }

      $amadeusOrderId = $order['id'] ?? null;

      /* -------------------------
         6. Price snapshot
      -------------------------- */
      $price = $offer['price']['grandTotal']
            ?? $offer['price']['total']
            ?? '0.00';

      $currency = $offer['price']['currency'] ?? 'GBP';

        $travellersInput = $_POST['travellers'] ?? [];
        $primaryTraveller = $travellersInput[0] ?? [];

        $travellerName  = trim($primaryTraveller['name'] ?? '');
        $travellerEmail = trim($primaryTraveller['email'] ?? '');
        $travellerPhone = trim($primaryTraveller['phone'] ?? '');
        $travellerDob = trim($primaryTraveller['dob'] ?? '');

        $travellersDB = json_encode($travellersInput);

        $passportNumberDB = !empty($primaryTraveller['passport_number'])
            ? strtoupper(preg_replace('/[^A-Z0-9]/', '', $primaryTraveller['passport_number']))
            : null;
        $passportExpiryDB = !empty($primaryTraveller['passport_expiry'])
            ? $primaryTraveller['passport_expiry']
            : null;
        $passportNatDB = !empty($primaryTraveller['passport_nationality'])
            ? iso2($primaryTraveller['passport_nationality'])
            : null;

      /* -------------------------
         7. Save to DB
      -------------------------- */
      $orderJson = json_encode($order);

      if ($booking_id > 0) {

          /* ---------- UPDATE ---------- */
          $stmt = $mysqli->prepare("
              UPDATE bookings_flights SET
                  amadeus_order_id = ?,
                  pnr = ?,
                  origin = ?,
                  destination = ?,
                  departure_date = ?,
                  return_date = ?,
                  trip_type = ?,
                  people_no = ?,
                  class = ?,
                  non_stop = ?,
                  total_amount = ?,
                  currency = ?,
                  traveller_name = ?,
                  traveller_email = ?,
                  traveller_phone = ?,
                  passport_number = ?,
                  passport_expiry = ?,
                  passport_nationality = ?,
                  order_json = ?,
                  travellers = ?
              WHERE booking_id = ?
              LIMIT 1
          ");

          $stmt->bind_param(
              'ssssssssssdsssssssssi',
              $amadeusOrderId,
              $pnr,
              $origin,
              $destination,
              $depDate,
              $retDate,
              $trip_type,
              $people_no,
              $class,
              $non_stop,
              $price,
              $currency,
              $travellerName,
              $travellerEmail,
              $travellerPhone,
              $passportNumberDB,
              $passportExpiryDB,
              $passportNatDB,
              $orderJson,
              $travellersDB,
              $booking_id
          );

          $stmt->execute();
          $stmt->close();

          $flightBookingId = $booking_id;

      } else {

          /* ---------- INSERT ---------- */
          $stmt = $mysqli->prepare("
            INSERT INTO bookings_flights
            (booking_id, amadeus_order_id, pnr, origin, destination,
             departure_date, return_date, trip_type, people_no, class, non_stop, total_amount, currency,
             traveller_name, traveller_email, traveller_phone,
             passport_number, passport_expiry, passport_nationality, order_json, travellers)
            VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
          ");

          $stmt->bind_param(
            'ssssssssssdsssssssss',
            $amadeusOrderId,
            $pnr,
            $origin,
            $destination,
            $depDate,
            $retDate,
            $trip_type,
            $people_no,
            $class,
            $non_stop,
            $price,
            $currency,
            $travellerName,
            $travellerEmail,
            $travellerPhone,
            $passportNumberDB,
            $passportExpiryDB,
            $passportNatDB,
            $orderJson,
            $travellersDB
          );

          $stmt->execute();
          $flightBookingId = $stmt->insert_id;
          $stmt->close();
      }


      /* ---------------------------
         7. Success response
      ---------------------------- */
      // echo json_encode([
      //   'success' => true,
      //   'pnr' => $pnr,
      //   'booking_id' => $booking_id
      // ]);
      // exit;

    } catch (Exception $e) {
      echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
      ]);
      exit;
    }

}
/* ================================
   TOUR BOOKING INSERT / UPDATE
   ================================ */
else {

  try {

    /* 1. Validate selected tour */
    if (empty($_POST['selected_tour_json'])) {
      throw new Exception('Tour package not selected');
    }

    $tour = json_decode($_POST['selected_tour_json'], true);
    if (!is_array($tour) || empty($tour['id'])) {
      throw new Exception('Invalid tour package data');
    }

    /* 2. Read inputs */
    $package_id       = (int)$tour['id'];
    $tour_name        = trim($tour['name'] ?? '');
    $tour_duration    = $tour['duration'] ?? null;
    $tour_price       = (float)($_POST['selected_tour_price'] ?? 0);
    $travellers_count = (int)($_POST['people_no'] ?? 1);

    if ($tour_price <= 0) {
      throw new Exception('Invalid tour price');
    }

    if ($package_id <= 0 || $tour_name === '') {
      throw new Exception('Invalid tour package');
    }

    /* 3. Travellers */
    $travellers = $_POST['travellers'] ?? [];
    if (!is_array($travellers) || empty($travellers)) {
      throw new Exception('Traveller details missing');
    }

    /* Optional consistency check */
    if ($travellers_count !== count($travellers)) {
      $travellers_count = count($travellers);
    }

    /* 4. Ensure booking_id exists */
    // if (empty($booking_id) || $booking_id <= 0) {
    //   throw new Exception('Booking reference missing');
    // }

    /* 5. Prepare JSON snapshots */
    $tour_json       = json_encode($tour, JSON_UNESCAPED_UNICODE);
    $travellers_json = json_encode($travellers, JSON_UNESCAPED_UNICODE);

    if ($booking_id > 0) {

      /* ---------- UPDATE ---------- */
      $stmt = $mysqli->prepare("
        UPDATE bookings_tours SET
          package_id       = ?,
          tour_name        = ?,
          tour_duration    = ?,
          tour_price       = ?,
          travellers_count = ?,
          tour_json        = ?,
          travellers_json  = ?
        WHERE booking_id = ?
        LIMIT 1
      ");

      if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
      }

      $stmt->bind_param(
        "issdissi",
        $package_id,
        $tour_name,
        $tour_duration,
        $tour_price,
        $travellers_count,
        $tour_json,
        $travellers_json,
        $booking_id
      );

      if (!$stmt->execute()) {
        throw new Exception('Update failed: ' . $stmt->error);
      }

      $tourBookingId = $booking_id;

      $stmt->close();

    } else {

      /* ---------- INSERT ---------- */
      $stmt = $mysqli->prepare("
        INSERT INTO bookings_tours
        (
          booking_id,
          package_id,
          tour_name,
          tour_duration,
          tour_price,
          travellers_count,
          tour_json,
          travellers_json
        )
        VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)
      ");

      if (!$stmt) {
        throw new Exception('Prepare failed: ' . $mysqli->error);
      }

      $stmt->bind_param(
        "issdiss",
        $package_id,
        $tour_name,
        $tour_duration,
        $tour_price,
        $travellers_count,
        $tour_json,
        $travellers_json
      );

      if (!$stmt->execute()) {
        throw new Exception('Insert failed: ' . $stmt->error);
      }

      $tourBookingId = $stmt->insert_id;
      $stmt->close();
    }

  } catch (Exception $e) {

    echo json_encode([
      'success' => false,
      'error'   => $e->getMessage()
    ]);
    exit;
  }
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
$assigned_to        = !empty($_POST['owner_id']) ? (int)$_POST['owner_id'] : null;

$itype      = !empty($_POST['itype']) ? $_POST['itype'] : 'IN';

// $status    = $_POST['status'] ?? null;
$status    = $_POST['status'] ?? 'New';
$status = $status==''?'New':$status;
$priority  = $_POST['priority'] ?? null;

$follow_date = $_POST['follow_date'] ?? null;
$follow_time = $_POST['follow_time'] ?? null;

$nature = $_POST['nature'] ?? null;
// if($nature=="") {
//     echo json_encode(['success'=>false,'error'=>'You should select a tag']);
//     exit;
// }

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
   INSERT booking
===================================================== */
$contact_type_id1 = "1";

if ($booking_id > 0) {

    /* ---------- UPDATE ---------- */
    $sql = "
        UPDATE bookings SET
            date = ?,
            time = ?,
            contact_name = ?,
            subject = ?,
            notes = ?,
            channel_id = ?,
            contact_type_id = ?,
            type_id = ?,
            owner_id = ?,
            assigned_to = ?,
            status = ?,
            priority = ?,
            follow_date = ?,
            follow_time = ?,
            contact_entity_id = ?,
            contact_phone = ?,
            contact_email = ?,
            entity_contact_id = ?,
            related_employee_ids = ?,
            related_customer_id = ?,
            itype = ?,
            nature = ?,
            lead_type = ?
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success'=>false,'error'=>'prepare_failed']);
        exit;
    }

    $lead_type = "Booking";

    $stmt->bind_param(
        "sssssiiiisssssissssssssi",
        $date, $time, $contact_name, $subject, $notes,
        $channel_id, $contact_type_id1, $scenario_id, $_SESSION['person_id'], $assigned_to,
        $status, $priority, $follow_date, $follow_time,
        $contact_entity_id, $contact_phone, $contact_email,
        $entity_contact_id, $related_employee_ids, $related_customer_id, $itype, $nature, $lead_type,
        $booking_id
    );

    if (!$stmt->execute()) {
        echo json_encode(['success'=>false,'error'=>'update_failed','db'=>$stmt->error]);
        exit;
    }

    $stmt->close();

} else {

    /* ---------- INSERT ---------- */
    $sql = "
        INSERT INTO bookings
        (date,time,contact_name,subject,notes,channel_id,contact_type_id,type_id,owner_id,assigned_to,
         status,priority,follow_date,follow_time,contact_entity_id,contact_phone,contact_email,
         entity_contact_id,related_employee_ids,related_customer_id,itype,nature)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    ";

    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success'=>false,'error'=>'prepare_failed']);
        exit;
    }

    $stmt->bind_param(
        "sssssiiiisssssisssssss",
        $date, $time, $contact_name, $subject, $notes,
        $channel_id, $contact_type_id1, $scenario_id, $_SESSION['person_id'], $assigned_to,
        $status, $priority, $follow_date, $follow_time,
        $contact_entity_id, $contact_phone, $contact_email,
        $entity_contact_id, $related_employee_ids, $related_customer_id, $itype, $nature
    );

    if (!$stmt->execute()) {
        echo json_encode(['success'=>false,'error'=>'insert_failed','db'=>$stmt->error]);
        exit;
    }

    $booking_id = $stmt->insert_id;
    $stmt->close();
}
// $booking_id = $stmt->insert_id;

if($scenario_id=="1") { //Flights
    $db->update('bookings_flights',array('id'=>$flightBookingId),array('booking_id'=>$booking_id));
}
else if($scenario_id=="2") {
    $db->update('bookings_tours',array('id'=>$tourBookingId),array('booking_id'=>$booking_id));
}


require_once __DIR__ . '/../includes/generate_booking_pdf.php';
$pdfPath = generateBookingPDF($mysqli, $booking_id);

if ($pdfPath) {
    $stmt = $mysqli->prepare("UPDATE bookings SET generated_pdf=? WHERE id=?");
    $fileName = basename($pdfPath);
    $stmt->bind_param("si", $fileName, $booking_id);
    $stmt->execute();
    $stmt->close();
}


/* =====================================================
   GET CONTACT TYPE SLUG (for routing)
===================================================== */
$contact_type_slug = null;
$rurl = '';

// $contact_type_id = "1";
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
$rurl = 'customers_view&id='.$contact_entity_id;
$ufolder = "customers/";
$utype = "customer";
switch ($contact_type_slug) {
    case 'customer':
        $rurl = 'customers_view&id='.$contact_entity_id;
        $ufolder = "customers/";
        $utype = "customer";
        break;
    case 'new':
        // $rurl = 'contacts_view&id='.$contact_entity_id;
        // $ufolder = "contacts/";
        // $utype = "contact";
        $rurl = 'customers_view&id='.$contact_entity_id;
        $ufolder = "customers/";
        $utype = "customer";
    // case 'existing-contact':
    //     $rurl = 'contacts_view&id='.$contact_entity_id;
    //     $ufolder = "contacts/";
    //     $utype = "contact";
    //     break;
    // case 'existing-employee':
    //     $rurl = 'employees_viewr&id='.$contact_entity_id;
    //     $ufolder = "employees/";
    //     $utype = "employee";
    //     break;
    // case 'employee':
    //     $rurl = 'recruiters_view&id='.$contact_entity_id;
    //     $ufolder = "recruiters/";
    //     $utype = "recruiter";
    //     break;
    // case 'vendor':
    //     $rurl = 'suppliers_view&id='.$contact_entity_id;
    //     $ufolder = "suppliers/";
    //     $utype = "supplier";
    //     break;
}


/* =====================================================
   LOGS & REMINDERS
===================================================== */
$res = $mysqli->query("SELECT * FROM bookings WHERE id=".$booking_id." LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
    $agent_id = $_SESSION['user_id'] ?? null;
    save_related_logs_and_reminders($mysqli, $row, $agent_id);
    $res->free();
}

if($contact_type_slug=='new') {
    $contact_entity_id = $mysqli
        ->query("SELECT contact_entity_id FROM bookings WHERE id=".$booking_id." LIMIT 1")
        ->fetch_assoc()['contact_entity_id'] ?? null;
    $rurl = 'customers_view&id='.$contact_entity_id;
}

$site->agent_log("New booking with summary $subject [#$booking_id] is added ",$contact_entity_id,$utype);

/* =====================================================
   SINGLE DOCUMENT UPLOAD (OPTIONAL)
===================================================== */
if (
    !empty($_FILES['document_file']) &&
    $_FILES['document_file']['error'] === UPLOAD_ERR_OK &&
    !empty($contact_entity_id)
) {
    $baseDir        = __DIR__ . '/../../uploads/'.$ufolder;
    $bookingDir = $baseDir . 'bookings/';
    $bookingDir1 = __DIR__ . '/../../uploads/bookings/';
    $entityDir      = $baseDir . 'documents/';

    if (!is_dir($entityDir)) mkdir($entityDir, 0777, true);
    if (!is_dir($bookingDir)) mkdir($bookingDir, 0777, true);

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

    $bookingFile = 'booking_'.$booking_id.'_'.time().'.'.$ext;
    $entityFile      = 'doc_'.$booking_id.'_'.time().'.'.$ext;

    if (move_uploaded_file($_FILES['document_file']['tmp_name'], $bookingDir.$bookingFile)) {

        copy($bookingDir.$bookingFile, $bookingDir1.$bookingFile);
        // copy($bookingDir.$bookingFile, $entityDir.$entityFile);

        /* Insert to bookings documents */
        $document_label = $document_label==null||$document_label==''?'Document':$document_label;
        $stmt = $mysqli->prepare("
            INSERT INTO bookings_documents
            (booking_id, label, file_name, file_type, created_by, created_at)
            VALUES (?,?,?,?,?,NOW())
        ");
        $stmt->bind_param(
            "issss",
            $booking_id,
            $document_label,
            $bookingFile,
            $dtype,
            $_SESSION['person_name']
        );
        $stmt->execute();
        $stmt->close();

        /* ---- update booking with its own copy ---- */
        $upd = $mysqli->prepare("UPDATE bookings SET document_label=?, document_file=? WHERE id=?");
        if ($upd) {
            $upd->bind_param('ssi', $document_label, $bookingFile, $booking_id);
            $upd->execute();
            $upd->close();
        }
    }
}

/* =====================================================
   RESPONSE
===================================================== */
echo json_encode([
    'success' => true,
    'id'      => $booking_id,
    'rurl'    => $rurl
]);
exit;

/**
 * Save related logs & reminders for an booking row.
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
        $s = $mysqli->prepare("SELECT name FROM bookings_types WHERE id = ? LIMIT 1");
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
    // $target = 'contacts';
    // if (strpos($lower, 'existing employee') !== false) $target = 'employees';
    // elseif (strpos($lower, 'existing recruiter') !== false) $target = 'recruiters';
    // elseif (strpos($lower, 'existing supplier') !== false) $target = 'suppliers';
    // elseif (strpos($lower, 'existing customer') !== false) $target = 'customers';
    // elseif (strpos($lower, 'existing contact') !== false) $target = 'contacts';
    $target = 'customers';

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
        // if booking already had an entity_id that points to contacts, use it
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
            $ins = $mysqli->prepare("INSERT INTO customers (agent_id, name, company, phone, email, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
            if ($ins) {
                $ag = $agent_id ? intval($agent_id) : null;
                $company = $contact_name;
                $ins->bind_param('issss', $ag, $contact_name, $company, $contact_phone, $contact_email);
                $ins->execute();
                $contact_record_id = $mysqli->insert_id;
                $ins->close();
                $created_contact = true; // <-- NEW (we created a contacts row as fallback)
            } else {
                $qn = $mysqli->real_escape_string($contact_name);
                $qp = $mysqli->real_escape_string($contact_phone);
                $qe = $mysqli->real_escape_string($contact_email);
                $mysqli->query("INSERT INTO customers (agent_id, name, company, phone, email, created_at) VALUES (" . ($agent_id ? intval($agent_id) : "NULL") . ", '{$qn}', '{$qn}', '{$qp}', '{$qe}', NOW())");
                $contact_record_id = $mysqli->insert_id;
                $created_contact = true; // <-- NEW
            }
        }
    }

    // ---- NEW: If we created a contacts row just now, update the original bookings row so it points to it ----
    if ($created_contact && !empty($it['id'])) {
        $booking_id = intval($it['id']);
        $new_cid = $contact_record_id ? intval($contact_record_id) : null;

        // prefer prepared statement; fallback to escaped query if prepare() fails
        $upd = $mysqli->prepare("UPDATE bookings SET contact_entity_id = ? WHERE id = ?");
        if ($upd) {
            $upd->bind_param('ii', $new_cid, $booking_id);
            $upd->execute();
            $upd->close();
        } else {
            // fallback — make sure values are safely escaped / cast
            $mysqli->query("UPDATE bookings SET contact_entity_id = " . ($new_cid !== null ? intval($new_cid) : "NULL") . " WHERE id = " . $booking_id);
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