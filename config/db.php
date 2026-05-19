<?php
// define('DB_HOST', '127.0.0.1');
// define('DB_USER', 'crmcust8');
// define('DB_PASS', 'AjvA8bbEa9^&kmu2');
// define('DB_NAME', 'crmcust8');

define('DB_HOST', '127.0.0.1');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'jrj_travel');

$time_zone = "Europe/London";

//reset password url encryption - $site->my_encrypt()
$encryption_key = '';

//mail settings
$enable_email = true;
$enable_sms = false;
$test_phones = array('7562498181','7552374886');

// $customers_statuses = ['Suspect' => 'Suspect','Prospect' => 'Prospect','Active' => 'Active','Inactive' => 'Inactive','Work in progress' => 'Work in progress','Archived' => 'Archived'];
$customers_statuses = ['Active' => 'Active','Inactive' => 'Inactive'];
$recruiters_statuses = ['Suspect' => 'Suspect','Prospect' => 'Prospect','Active' => 'Active','Inactive' => 'Inactive','Work in progress' => 'Work in progress','Archived' => 'Archived'];
$employees_statuses = ['Suspect' => 'Suspect','Prospect' => 'Prospect','Active' => 'Active','Inactive' => 'Inactive','Work in progress' => 'Work in progress','Archived' => 'Archived'];
$suppliers_statuses = ['Suspect' => 'Suspect','Prospect' => 'Prospect','Active' => 'Active','Inactive' => 'Inactive','Work in progress' => 'Work in progress','Archived' => 'Archived'];


$currency_symbol = "£ ";

// AMADEUS SETTINGS
define('AMADEUS_BASE_URL', 'https://test.api.amadeus.com');
define('AMADEUS_CLIENT_ID', '0Y4ZQaVGFGxLAuTzzoG8tsAAunXDNNUa');
define('AMADEUS_CLIENT_SECRET', 'jQ6L39f3ZOJk1dWD');

// TRAVELPORT SETTINGS
define('TP_AUTH_URL', 'https://auth.pp.travelport.net/oauth/token');       // Pre-production OAuth
define('TP_BASE_URL', 'https://api.pp.travelport.net');                    // Pre-production API
define('TP_VERSION', '11');                                                  // JSON API version

define('APP_NAME', 'TRAVELPORT'); // used in travelport ticket print

// Fill these with your Travelport test credentials
// define('TP_USERNAME', 'TP57386028');          // Travelport Username
// define('TP_PASSWORD', '8syb4S=G7{>6–}p&');          // Travelport Password
// define('TP_CLIENT_ID', '2C9uuTkO7EC96maT3ewQLANt6tag6knC');         // OAuth Client ID
// define('TP_CLIENT_SECRET', 'WfZbPITTd66c4EgtmHiRmCk1EuTzZQmaROQv0fG-twd0PTcZ_4v86AHN6yuIzDtx');     // OAuth Client Secret
// define('TP_ACCESS_GROUP', '8E0C825F-75F5-4924-BE5D-F04A913FAEC5');      // XAUTH_TRAVELPORT_ACCESSGROUP (PCC/SID)
define('TP_USERNAME', 'TP18065320');          // Travelport Username
define('TP_PASSWORD', 'VPa7i0jT');          // Travelport Password
define('TP_CLIENT_ID', 'eL41qI2QJtlsshhnP7UFhRO6RKHGYEsP');         // OAuth Client ID
define('TP_CLIENT_SECRET', 'xIS-JzTxXzRkO59A40dhQ1SSeyzBTPd8Qn5URsMXFuxUoRXqYmrLeKEA1LfjV3hY');
define('TP_ACCESS_GROUP', '57DE0F79-C4D3-488F-9262-F9067807CBE1'); // new
// define('TP_ACCESS_GROUP', '8E0C825F-75F5-4924-BE5D-F04A913FAEC5'); // old

define('EP_SEARCH',         '/air/catalog/search/catalogproductofferings');
define('EP_SEARCH_BUILD',   '/air/catalog/search/catalogproductofferings/buildoptions');
define('EP_PRICE',          '/air/price/offers/buildfromproducts');
define('EP_PRICE_REF',      '/air/price/offers/buildfromcatalogproductofferings');
define('EP_WORKBENCH',      '/air/book/session/reservationworkbench');
define('EP_WORKBENCH_POST', '/air/book/session/reservationworkbench/buildfromlocator');
define('EP_TRAVELERS',      '/air/book/traveler/reservationworkbench/{id}/travelers');
define('EP_ADD_OFFER',      '/air/book/airoffer/reservationworkbench/{id}/offers/buildfromproducts');
define('EP_ADD_OFFER_REF',  '/air/book/airoffer/reservationworkbench/{id}/offers/buildfromcatalogproductofferings');
define('EP_FOP',            '/air/payment/reservationworkbench/{id}/formofpayment');
define('EP_PAYMENT',        '/air/paymentoffer/reservationworkbench/{id}/payments');
define('EP_COMMIT',         '/air/book/reservation/reservations/{id}');
define('EP_RETRIEVE',       '/air/book/reservation/reservations/{pnr}');
define('EP_CANCEL_ITEMS',   '/book/reservationworkbench/{id}/reservations/cancelitems');
define('EP_TICKET_VOID',    '/air/ticket/tickets/updatestatus/{ticketNumber}');
define('EP_TICKET_LIST',    '/air/receipt/reservations/{pnr}/receipts');
define('EP_TICKET_DISPLAY', '/air/ticket/tickets/{ticketNumber}');
define('EP_UPDATABLE_ITEMS','/air/book/updatableItem/reservationworkbench/{id}/travelerupdatableitems/buildfromtraveler');
define('EP_TRAVELER_UPDATE','/air/book/traveler/reservationworkbench/{id}/travelers/updatefromtravelerupdateditems/{updatableId}');
define('EP_FARE_RULES',         '/air/farerule/farerules/fromcatalogproductofferings');
define('EP_FARE_RULES_RES',     '/air/farerule/farerules/fromreservation');
define('EP_SEAT_MAP',      '/air/search/seat/catalogofferingsancillaries/seatavailabilities');
define('EP_SEAT_MAP_WB',   '/air/search/seat/catalogofferingsancillaries/seatavailabilities');
define('EP_SEAT_BOOK',     '/air/book/airoffer/reservationworkbench/{id}/offers/buildancillaryoffersfromcatalogofferings');


$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_errno) { http_response_code(500); die('DB connection failed: ' . $mysqli->connect_error); }
$mysqli->set_charset('utf8mb4');

// $mysqli->query("SET time_zone = '".$time_zone."'");
// $mysqli->query("SET time_zone = '+00:00'");
date_default_timezone_set($time_zone);
