<?php

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/config/functions.php';

$scenario_id = 2; // Tours
$channel_id  = 7; // Create new channel like "Calendly"
$agent_id    = 1;
$agent_name  = "System";

/* Get raw POST */
$payload = file_get_contents("php://input");
$data = json_decode($payload, true);

/* Log for debugging */
file_put_contents("calendly_log.txt", $payload . PHP_EOL, FILE_APPEND);

if (!empty($data['event']) && $data['event'] === 'invitee.created') {

    // try {

    //     $invitee  = $data['payload']['invitee'];
    //     $event    = $data['payload']['event'];
    //     $tracking = $data['payload']['tracking'] ?? [];

    //     $name  = trim($invitee['name'] ?? '');
    //     $email = trim($invitee['email'] ?? '');
    //     $meeting_time = $event['start_time'] ?? '';
    //     $package_id = intval($tracking['utm_package_id'] ?? 0);

    //     if (!$name || !$email) {
    //         throw new Exception("Missing invitee data");
    //     }

    //     /* ===================================
    //        1. FETCH PACKAGE (IF EXISTS)
    //     =================================== */

    //     $package = null;

    //     if ($package_id > 0) {
    //         $stmt = $mysqli->prepare("SELECT * FROM packages WHERE id=? LIMIT 1");
    //         $stmt->bind_param("i", $package_id);
    //         $stmt->execute();
    //         $package = $stmt->get_result()->fetch_assoc();
    //         $stmt->close();
    //     }

    //     /* ===================================
    //        2. CREATE bookings_tours
    //     =================================== */

    //     $adults = 0;
    //     $children = 0;
    //     $rooms = 0;

    //     $tour_name = $package ? $package['name'] : "Calendly Call Enquiry";
    //     $pduration = $package 
    //         ? $package['duration_days']."D / ".$package['duration_nights']."N"
    //         : null;

    //     $tour_json = $package ? json_encode($package, JSON_UNESCAPED_UNICODE) : null;

    //     $stmt = $mysqli->prepare("
    //         INSERT INTO bookings_tours
    //         (
    //           booking_id, package_id, tour_name, tour_duration,
    //           tour_price, original_amount, discount_id, discount_code,
    //           discount_name, discount_type, discount_value,
    //           discount_amount, final_amount,
    //           travellers_count, adults, children, rooms,
    //           travel_date, tour_json, travellers_json
    //         )
    //         VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    //     ");

    //     $zero = 0;
    //     $null = null;
    //     $travellers_count = 0;
    //     $travellers_json = json_encode([]);

    //     $stmt->bind_param(
    //         "issddisssdddiiiisss",
    //         $package_id,
    //         $tour_name,
    //         $pduration,
    //         $zero,
    //         $zero,
    //         $null,
    //         $null,
    //         $null,
    //         $null,
    //         $null,
    //         $zero,
    //         $zero,
    //         $travellers_count,
    //         $adults,
    //         $children,
    //         $rooms,
    //         $meeting_time,
    //         $tour_json,
    //         $travellers_json
    //     );

    //     $stmt->execute();
    //     $tourBookingId = $stmt->insert_id;
    //     $stmt->close();

    //     /* ===================================
    //        3. CHECK / CREATE CUSTOMER
    //     =================================== */

    //     $stmt = $mysqli->prepare("SELECT id FROM customers WHERE email=? LIMIT 1");
    //     $stmt->bind_param("s", $email);
    //     $stmt->execute();
    //     $customer = $stmt->get_result()->fetch_assoc();
    //     $stmt->close();

    //     if ($customer) {
    //         $contact_entity_id = $customer['id'];
    //     } else {
    //         $stmt = $mysqli->prepare("
    //             INSERT INTO customers (name, email, created_at)
    //             VALUES (?, ?, NOW())
    //         ");
    //         $stmt->bind_param("ss", $name, $email);
    //         $stmt->execute();
    //         $contact_entity_id = $stmt->insert_id;
    //         $stmt->close();
    //     }

    //     /* ===================================
    //        4. CREATE bookings MASTER
    //     =================================== */

    //     $today = date('Y-m-d');
    //     $time  = date('H:i:s');
    //     $status = "New";
    //     $itype  = "IN";

    //     $subject = $package
    //         ? "Calendly Call - ".$package['name']
    //         : "Calendly Scheduled Call";

    //     $notes = "Meeting scheduled via Calendly on ".$meeting_time;

    //     $stmt = $mysqli->prepare("
    //         INSERT INTO bookings
    //         (date,time,contact_name,subject,notes,
    //          channel_id,contact_type_id,type_id,
    //          owner_id,assigned_to,status,
    //          contact_entity_id,contact_email,itype)
    //         VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
    //     ");

    //     $contact_type_id = 1;
    //     $owner_id = 1;

    //     $stmt->bind_param(
    //         "sssssiiiisssss",
    //         $today,
    //         $time,
    //         $name,
    //         $subject,
    //         $notes,
    //         $channel_id,
    //         $contact_type_id,
    //         $scenario_id,
    //         $owner_id,
    //         $owner_id,
    //         $status,
    //         $contact_entity_id,
    //         $email,
    //         $itype
    //     );

    //     $stmt->execute();
    //     $booking_id = $stmt->insert_id;
    //     $stmt->close();

    //     /* ===================================
    //        5. LINK TOUR TO BOOKING
    //     =================================== */

    //     $stmt = $mysqli->prepare("UPDATE bookings_tours SET booking_id=? WHERE id=?");
    //     $stmt->bind_param("ii", $booking_id, $tourBookingId);
    //     $stmt->execute();
    //     $stmt->close();

    //     /* ===================================
    //        6. ADD FOLLOWUP ENTRY
    //     =================================== */

    //     $note_text = "Calendly meeting scheduled for ".$meeting_time;

    //     $stmt = $mysqli->prepare("
    //         INSERT INTO bookings_followup
    //         (booking_id, note_text, created_by, created_by_name)
    //         VALUES (?,?,?,?)
    //     ");
    //     $stmt->bind_param("isis", $booking_id, $note_text, $agent_id, $agent_name);
    //     $stmt->execute();
    //     $stmt->close();

    //     /* ===================================
    //        7. AGENT LOG
    //     =================================== */

    //     if (isset($site) && method_exists($site, 'agent_log')) {
    //         $snippet = substr($note_text, 0, 120);
    //         $site->agent_log(
    //             "Calendly booking created #$booking_id - $snippet",
    //             $contact_entity_id,
    //             "customer"
    //         );
    //     }

    // } catch (Exception $e) {
    //     file_put_contents("calendly_error.txt", $e->getMessage().PHP_EOL, FILE_APPEND);
    // }
}

http_response_code(200);
echo "OK";
?>