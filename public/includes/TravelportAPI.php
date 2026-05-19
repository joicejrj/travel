<?php
/**
 * SkyVoyage - Travelport JSON API v11 Wrapper
 * 
 * Handles OAuth authentication and all Air API calls:
 * Search, Price, Book (Workbench → Traveler → Offer → FOP → Payment → Commit),
 * Retrieve, Cancel, and Ticket Void.
 */

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../public/_auth.php';


// ============================================================
// TRAVELPORT Helper Functions
// ============================================================

/**
 * Generate a unique booking reference
 */
function generateBookingRef() {
    return 'SV' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

/**
 * Format currency
 */
function formatCurrency($amount, $currency = 'USD') {
    $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'INR' => '₹', 'AUD' => 'A$'];
    $sym = $symbols[$currency] ?? $currency . ' ';
    return $sym . number_format((float)$amount, 2);
}

/**
 * Format duration string
 */
function formatDuration($minutes) {
    $h = floor($minutes / 60);
    $m = $minutes % 60;
    return $h . 'h ' . $m . 'm';
}

/**
 * Sanitize input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Flash messages
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirect helper
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * JSON response helper
 */
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Log API interactions
 */
function logAPI($endpoint, $method, $request, $response, $httpStatus, $executionTime, $bookingId = null) {
    global $mysqli;
    $stmt = $mysqli->prepare("INSERT INTO travelport_api_logs (booking_id, endpoint, method, request_body, response_body, http_status, execution_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $reqJson = is_string($request) ? $request : json_encode($request);
    $resJson = is_string($response) ? $response : json_encode($response);
    $stmt->bind_param("issssid", $bookingId, $endpoint, $method, $reqJson, $resJson, $httpStatus, $executionTime);
    $stmt->execute();
    $stmt->close();
}

/**
 * Airline name lookup (common airlines)
 */
function getAirlineName($code) {
    $airlines = [
        'AA' => 'American Airlines', 'UA' => 'United Airlines', 'DL' => 'Delta Air Lines',
        'WN' => 'Southwest Airlines', 'B6' => 'JetBlue Airways', 'AS' => 'Alaska Airlines',
        'NK' => 'Spirit Airlines', 'F9' => 'Frontier Airlines', 'HA' => 'Hawaiian Airlines',
        'SY' => 'Sun Country Airlines', 'G4' => 'Allegiant Air',
        'BA' => 'British Airways', 'LH' => 'Lufthansa', 'AF' => 'Air France',
        'KL' => 'KLM Royal Dutch', 'EK' => 'Emirates', 'QR' => 'Qatar Airways',
        'SQ' => 'Singapore Airlines', 'CX' => 'Cathay Pacific', 'QF' => 'Qantas',
        'NZ' => 'Air New Zealand', 'JL' => 'Japan Airlines', 'NH' => 'ANA',
        'TK' => 'Turkish Airlines', 'LX' => 'Swiss Intl', 'OS' => 'Austrian',
        'SK' => 'SAS Scandinavian', 'AY' => 'Finnair', 'IB' => 'Iberia',
        'EI' => 'Aer Lingus', 'TP' => 'TAP Air Portugal', 'AZ' => 'ITA Airways',
        'AI' => 'Air India', '6E' => 'IndiGo', 'SG' => 'SpiceJet',
        'UK' => 'Vistara', 'AC' => 'Air Canada', 'WS' => 'WestJet',
        'LA' => 'LATAM Airlines', 'AM' => 'Aeromexico', 'CM' => 'Copa Airlines',
        'ET' => 'Ethiopian Airlines', 'SA' => 'South African Airways',
        'MH' => 'Malaysia Airlines', 'TG' => 'Thai Airways', 'GA' => 'Garuda Indonesia',
        'PR' => 'Philippine Airlines', 'OZ' => 'Asiana Airlines', 'KE' => 'Korean Air',
        'CI' => 'China Airlines', 'BR' => 'EVA Air', 'CZ' => 'China Southern',
        'CA' => 'Air China', 'MU' => 'China Eastern', 'HU' => 'Hainan Airlines',
        'FJ' => 'Fiji Airways', 'UL' => 'SriLankan Airlines', 'PK' => 'PIA',
        'WY' => 'Oman Air', 'GF' => 'Gulf Air', 'SV' => 'Saudia',
        'MS' => 'EgyptAir', 'RJ' => 'Royal Jordanian', 'ME' => 'MEA',
    ];
    return $airlines[$code] ?? $code;
}

function parseSearchResults($data) {
    $results = [];
    if (!$data) return $results;

    // Top-level response
    $response = $data['CatalogProductOfferingsResponse'] ?? $data;
    $catalogOfferings = $response['CatalogProductOfferings'] ?? $data['CatalogProductOfferings'] ?? null;

    if (!$catalogOfferings) return $results;

    // ================================================================
    // Step 1: Build reference lookup maps from ReferenceList
    // ReferenceList is at CatalogProductOfferingsResponse level
    // It's an array of typed lists: ReferenceListFlight, ReferenceListProduct, etc.
    // ================================================================

    $flightMap  = [];  // "s7" → Flight object
    $productMap = [];  // "p0" → Product object
    $brandMap   = [];  // "b0" → Brand object

    // ReferenceList can be at response level OR inside CatalogProductOfferings
    $referenceLists = $response['ReferenceList']
                   ?? $catalogOfferings['ReferenceList']
                   ?? [];

    foreach ($referenceLists as $refList) {
        $refType = $refList['@type'] ?? '';

        // ---- ReferenceListFlight ----
        if ($refType === 'ReferenceListFlight' || stripos($refType, 'Flight') !== false) {
            $flights = $refList['Flight'] ?? [];
            foreach ($flights as $flight) {
                $fid = $flight['id'] ?? null;
                if ($fid) {
                    $flightMap[$fid] = $flight;
                }
            }
        }

        // ---- ReferenceListProduct ----
        if ($refType === 'ReferenceListProduct' || stripos($refType, 'Product') !== false) {
            $products = $refList['Product'] ?? [];
            foreach ($products as $product) {
                $pid = $product['id'] ?? null;
                if ($pid) {
                    $productMap[$pid] = $product;
                }
            }
        }

        // ---- ReferenceListBrand ----
        if ($refType === 'ReferenceListBrand' || stripos($refType, 'Brand') !== false) {
            $brands = $refList['Brand'] ?? [];
            foreach ($brands as $brand) {
                $bid = $brand['id'] ?? null;
                if ($bid) {
                    $brandMap[$bid] = $brand;
                }
            }
        }
    }

    // ================================================================
    // Step 2: Parse each CatalogProductOffering
    // ================================================================

    $catalogOfferingList = $catalogOfferings['CatalogProductOffering'] ?? [];

    foreach ($catalogOfferingList as $idx => $offering) {
        $offeringId = $offering['id'] ?? 'o' . $idx;
        $departure  = $offering['Departure'] ?? '';
        $arrival    = $offering['Arrival'] ?? '';
        $sequence   = $offering['sequence'] ?? 1;

        // ProductBrandOptions → contains flightRefs and brand/price combos
        $productBrandOptions = $offering['ProductBrandOptions'] ?? [];
        // Normalize: ensure it's always an array of PBOs
        if (isset($productBrandOptions['@type'])) {
            $productBrandOptions = [$productBrandOptions];
        }

        foreach ($productBrandOptions as $pbo) {
            // ---- Resolve flight segments from flightRefs ----
            $flightRefs = $pbo['flightRefs'] ?? [];
            if (is_string($flightRefs)) $flightRefs = explode(' ', $flightRefs);

            $segments = [];
            foreach ($flightRefs as $fRef) {
                $flight = $flightMap[$fRef] ?? null;
                if (!$flight) continue;

                $dep = $flight['Departure'] ?? [];
                $arr = $flight['Arrival'] ?? [];

                $segments[] = [
                    'carrier'       => $flight['carrier'] ?? '--',
                    'flightNumber'  => $flight['number'] ?? $flight['flightNumber'] ?? '',
                    'origin'        => $dep['location'] ?? $departure,
                    'destination'   => $arr['location'] ?? $arrival,
                    'departureDate' => $dep['date'] ?? '',
                    'departureTime' => $dep['time'] ?? '',
                    'arrivalDate'   => $arr['date'] ?? '',
                    'arrivalTime'   => $arr['time'] ?? '',
                    'duration'      => $flight['duration'] ?? '',
                    'cabin'         => '',   // filled from Product below
                    'classOfService'=> '',   // filled from Product below
                    'stops'         => 0,
                    'aircraft'      => $flight['equipment'] ?? '',
                    'flightRef'     => $fRef,
                    'operatingCarrier' => $flight['operatingCarrier'] ?? '',
                    'departureTerminal' => $dep['terminal'] ?? '',
                    'arrivalTerminal'   => $arr['terminal'] ?? '',
                    'availabilitySourceCode' => $flight['AvailabilitySourceCode'] ?? '',
                ];
            }

            // Fallback if no flights resolved
            if (empty($segments) && $departure && $arrival) {
                $segments[] = [
                    'carrier' => '--', 'flightNumber' => '', 'origin' => $departure,
                    'destination' => $arrival, 'departureDate' => '', 'departureTime' => '',
                    'arrivalDate' => '', 'arrivalTime' => '', 'duration' => '',
                    'cabin' => '', 'classOfService' => '', 'stops' => 0, 'aircraft' => '',
                    'flightRef' => '', 'operatingCarrier' => '', 'departureTerminal' => '',
                    'arrivalTerminal' => '',
                ];
            }

            // ---- Parse each ProductBrandOffering (one per fare brand) ----
            $brandOfferings = $pbo['ProductBrandOffering'] ?? [];
            if (isset($brandOfferings['@type'])) $brandOfferings = [$brandOfferings];

            foreach ($brandOfferings as $bo) {
                // Price from BestCombinablePrice
                $bcp = $bo['BestCombinablePrice'] ?? null;
                if (!$bcp) continue;

                $totalPrice = (float)($bcp['TotalPrice'] ?? 0);
                $basePrice  = (float)($bcp['Base'] ?? 0);
                $totalTaxes = (float)($bcp['TotalTaxes'] ?? 0);
                $totalFees  = (float)($bcp['TotalFees'] ?? 0);
                $currency   = $bcp['CurrencyCode']['value'] ?? 'USD';

                // Resolve Brand name from ReferenceListBrand
                $brandRef  = $bo['Brand']['BrandRef'] ?? '';
                $brandInfo = $brandMap[$brandRef] ?? null;
                $brandName = '';
                $brandTier = '';
                if ($brandInfo) {
                    $brandName = $brandInfo['name'] ?? $brandInfo['Name'] ?? '';
                    $brandTier = $brandInfo['tier'] ?? $brandInfo['Tier'] ?? '';
                }

                // Resolve Product → get cabin and classOfService from PassengerFlight/FlightProduct
                $cabin = '';
                $classOfService = '';
                $fareBasisCode = '';
                $productRefs = $bo['Product'] ?? [];
                if (isset($productRefs['@type'])) $productRefs = [$productRefs];

                foreach ($productRefs as $pRefObj) {
                    $prodId = $pRefObj['productRef'] ?? '';
                    $prodInfo = $productMap[$prodId] ?? null;
                    if (!$prodInfo) continue;

                    // PassengerFlight[].FlightProduct has cabin and classOfService
                    $passengerFlights = $prodInfo['PassengerFlight'] ?? [];
                    if (isset($passengerFlights['passengerTypeCode'])) {
                        $passengerFlights = [$passengerFlights]; // single PF
                    }

                    foreach ($passengerFlights as $pf) {
                        $flightProducts = $pf['FlightProduct'] ?? [];
                        if (isset($flightProducts['segmentSequence'])) {
                            $flightProducts = [$flightProducts]; // single FP
                        }

                        foreach ($flightProducts as $fp) {
                            if (!$cabin) $cabin = $fp['cabin'] ?? '';
                            if (!$classOfService) $classOfService = $fp['classOfService'] ?? '';
                            if (!$fareBasisCode) $fareBasisCode = $fp['fareBasisCode'] ?? '';
                        }
                    }

                    // Also get total duration from product
                    // $prodInfo['totalDuration'] is available if needed
                }

                // Apply cabin/class from product to segments
                $segmentsWithCabin = $segments;
                foreach ($segmentsWithCabin as &$s) {
                    if (!$s['cabin'] && $cabin) $s['cabin'] = $cabin;
                    if (!$s['classOfService'] && $classOfService) $s['classOfService'] = $classOfService;
                }
                unset($s);

                // PriceBreakdown for per-passenger details
                $priceBreakdown = $bcp['PriceBreakdown'] ?? [];

                $results[] = [
                    'index'          => $idx,
                    'offeringId'     => $offeringId,
                    'sequence'       => $sequence,
                    'totalPrice'     => $totalPrice,
                    'currency'       => $currency,
                    'basePrice'      => $basePrice,
                    'taxes'          => $totalTaxes,
                    'fees'           => $totalFees,
                    'segments'       => $segmentsWithCabin,
                    'cabin'          => $cabin,
                    'classOfService' => $classOfService,
                    'fareBasisCode'  => $fareBasisCode,
                    'brandName'      => $brandName,
                    'brandTier'      => $brandTier,
                    'brandRef'       => $brandRef,
                    'flightRefs'     => $flightRefs,
                    'contentSource'  => $bo['ContentSource'] ?? 'GDS',
                    'priceBreakdown' => $priceBreakdown,
                    'rawOffering'    => $offering,
                    'rawBrandOffering' => $bo,
                ];
            }
        }
    }

    // Sort by price (cheapest first)
    usort($results, function($a, $b) {
        return $a['totalPrice'] <=> $b['totalPrice'];
    });

    return $results;
}

function sumDurations($segments){
    $totalMin = 0;

    foreach ($segments as $s) {
        $d = $s['duration'] ?? '';

        if (preg_match('/PT(?:(\d+)H)?(?:(\d+)M)?/', $d, $m)) {
            $h = (int)($m[1] ?? 0);
            $min = (int)($m[2] ?? 0);
            $totalMin += ($h * 60) + $min;
        }
        elseif (preg_match('/(\d+)h\s*(\d+)?/', $d, $m)) {
            $h = (int)($m[1] ?? 0);
            $min = (int)($m[2] ?? 0);
            $totalMin += ($h * 60) + $min;
        }
    }

    $h = floor($totalMin / 60);
    $m = $totalMin % 60;

    return "PT{$h}H{$m}M";
}

function extractTicketNumbers($resData): array {
    $tickets = [];
    $reservation = $resData['ReservationResponse']['Reservation'] ?? $resData['Reservation'] ?? $resData;

    // Path 1: Receipt → Payment → Document → Number
    // (from Ticketing Commit response and Reservation Retrieve)
    $receipts = $reservation['Receipt'] ?? [];
    if (isset($receipts['@type'])) $receipts = [$receipts];
    foreach ($receipts as $receipt) {
        // Receipt/Payment/Document/Number
        $payments = $receipt['Payment'] ?? [];
        if (isset($payments['@type'])) $payments = [$payments];
        foreach ($payments as $payment) {
            $documents = $payment['Document'] ?? [];
            if (isset($documents['@type'])) $documents = [$documents];
            foreach ($documents as $doc) {
                $num = $doc['Number'] ?? $doc['number'] ?? null;
                if ($num && !in_array($num, $tickets)) $tickets[] = $num;
            }
        }

        // Receipt/Document/Number (direct)
        $documents = $receipt['Document'] ?? [];
        if (isset($documents['@type'])) $documents = [$documents];
        foreach ($documents as $doc) {
            $num = $doc['Number'] ?? $doc['number'] ?? null;
            if ($num && !in_array($num, $tickets)) $tickets[] = $num;
        }
    }

    // Path 2: Offer → Price → Document → Number
    $offers = $reservation['Offer'] ?? [];
    if (isset($offers['@type'])) $offers = [$offers];
    foreach ($offers as $offer) {
        $price = $offer['Price'] ?? [];
        $documents = $price['Document'] ?? [];
        if (isset($documents['@type'])) $documents = [$documents];
        foreach ($documents as $doc) {
            $num = $doc['Number'] ?? $doc['number'] ?? null;
            if ($num && !in_array($num, $tickets)) $tickets[] = $num;
        }
    }

    // Path 3: Regex fallback — find all 13-digit "Number" values in the response
    if (empty($tickets)) {
        $json = json_encode($resData);
        if (preg_match_all('/"Number"\s*:\s*"(\d{13})"/', $json, $matches)) {
            $tickets = array_values(array_unique($matches[1]));
        }
    }

    return array_values($tickets);
}

class TravelportAPI {

    private $accessToken = null;
    private $tokenExpiry  = null;
    private $baseUrl;
    private $version;

    public function __construct() {
        $this->baseUrl = TP_BASE_URL;
        $this->version = TP_VERSION;
    }

    // ================================================================
    // OAuth 2.0 Authentication
    // ================================================================

    /**
     * Get a valid OAuth access token (cached in DB)
     */
    public function getToken() {
        global $mysqli;
        // Check memory cache
        if ($this->accessToken && $this->tokenExpiry && new DateTime() < $this->tokenExpiry) {
            return $this->accessToken;
        }

        $stmt = $mysqli->prepare("SELECT access_token, expires_at FROM travelport_oauth_tokens WHERE expires_at > NOW() ORDER BY id DESC LIMIT 1");
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $this->accessToken = $row['access_token'];
            $this->tokenExpiry = new DateTime($row['expires_at']);
            $stmt->close();
            return $this->accessToken;
        }
        $stmt->close();

        // Request new token
        return $this->requestNewToken();
    }

    /**
     * Request a new OAuth token from Travelport
     */
    private function requestNewToken() {
        global $mysqli;

        $postData = http_build_query([
            'grant_type'    => 'password',
            'username'      => TP_USERNAME,
            'password'      => TP_PASSWORD,
            'client_id'     => TP_CLIENT_ID,
            'client_secret' => TP_CLIENT_SECRET,
        ]);

        $ch = curl_init(TP_AUTH_URL);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Cache-Control: no-cache',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING       => '',
        ]);

        $startTime = microtime(true);
        $response  = curl_exec($ch);
        $execTime  = microtime(true) - $startTime;
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("OAuth request failed: $error");
        }
        curl_close($ch);

        logAPI(TP_AUTH_URL, 'POST', '(credentials)', $response, $httpCode, $execTime);

        $data = json_decode($response, true);
        if (!$data || !isset($data['access_token'])) {
            throw new Exception("OAuth token retrieval failed. HTTP $httpCode. Response: " . substr($response, 0, 500));
        }

        $this->accessToken = $data['access_token'];
        $expiresIn = $data['expires_in'] ?? 3600;
        $expiresAt = (new DateTime())->modify("+{$expiresIn} seconds")->format('Y-m-d H:i:s');
        $this->tokenExpiry = new DateTime($expiresAt);

        $stmt = $mysqli->prepare("INSERT INTO travelport_oauth_tokens (access_token, token_type, expires_at) VALUES (?, ?, ?)");
        $type = $data['token_type'] ?? 'Bearer';
        $stmt->bind_param("sss", $this->accessToken, $type, $expiresAt);
        $stmt->execute();
        $stmt->close();

        return $this->accessToken;
    }

    // ================================================================
    // Core HTTP Request
    // ================================================================

    /**
     * Make an authenticated API request
     */
    private function apiRequest($endpoint, $method = 'POST', $body = null, $bookingId = null) {
        $token = $this->getToken();
        $url   = $this->baseUrl . '/' . $this->version . $endpoint;

        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'Accept-Version: ' . $this->version,
            'Content-Version: ' . $this->version,
            'XAUTH_TRAVELPORT_ACCESSGROUP: ' . TP_ACCESS_GROUP,
            'taxBreakDown: false',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_ENCODING       => '',  // Auto-decode gzip, deflate, br
        ]);

        if (in_array($method, ['POST', 'PUT'])) {
            $jsonBody = ($body !== null) ? (is_string($body) ? $body : json_encode($body)) : '{}';
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonBody);
        }

        $startTime = microtime(true);
        $response  = curl_exec($ch);
        $execTime  = microtime(true) - $startTime;
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new Exception("API request failed: $error");
        }
        curl_close($ch);

        logAPI($endpoint, $method, $body, $response, $httpCode, $execTime, $bookingId);

        $decoded = json_decode($response, true);

        if ($httpCode >= 400) {
            $errorMsg = '';

            if ($decoded) {
                // Travelport v11 error formats
                if (isset($decoded['Errors'])) {
                    $errs = $decoded['Errors'];
                    if (is_array($errs)) {
                        foreach ($errs as $err) {
                            $errorMsg .= ($err['Message'] ?? $err['message'] ?? $err['Description'] ?? json_encode($err)) . '; ';
                        }
                    }
                } elseif (isset($decoded['errors'])) {
                    foreach ($decoded['errors'] as $err) {
                        $errorMsg .= ($err['detail'] ?? $err['message'] ?? $err['title'] ?? json_encode($err)) . '; ';
                    }
                } elseif (isset($decoded['FaultMessage'])) {
                    $fm = $decoded['FaultMessage'];
                    $errorMsg = ($fm['Message'] ?? '') . ' ' . ($fm['Description'] ?? '') . ' ' . ($fm['DetailDescription'] ?? '');
                } elseif (isset($decoded['message'])) {
                    $errorMsg = $decoded['message'];
                } elseif (isset($decoded['error_description'])) {
                    $errorMsg = $decoded['error_description'];
                } elseif (isset($decoded['error'])) {
                    $errorMsg = is_string($decoded['error']) ? $decoded['error'] : json_encode($decoded['error']);
                }
            }

            // Fallback: detect HTML error pages vs unknown errors
            if (empty(trim($errorMsg))) {
                if (stripos($response, '<html') !== false) {
                    // Travelport returned an HTML error page instead of JSON
                    if ($httpCode === 404) {
                        $errorMsg = 'Endpoint not found (404). This feature may not be available for this request.';
                    } elseif ($httpCode === 500) {
                        $errorMsg = 'Server error (500). The request could not be processed. This may indicate the feature is not supported for the selected content.';
                    } else {
                        $errorMsg = "Server returned HTML error page (HTTP $httpCode).";
                    }
                } else {
                    $errorMsg = 'Raw response: ' . substr($response, 0, 500);
                }
            }

            throw new Exception("API Error (HTTP $httpCode): " . trim($errorMsg));
        }

        return [
            'status'   => $httpCode,
            'data'     => $decoded,
            'raw'      => $response,
            'time'     => $execTime,
        ];
    }

    // ================================================================
    // 1. SEARCH FLIGHTS
    // ================================================================

    /**
     * Search for available flights
     */
    public function searchFlights($params) {
        $passengerCriteria = [];

        // Adults
        if (($params['adults'] ?? 1) > 0) {
            $passengerCriteria[] = [
                '@type'             => 'PassengerCriteria',
                'number'            => (int)$params['adults'],
                'passengerTypeCode' => 'ADT',
            ];
        }

        // Children
        if (($params['children'] ?? 0) > 0) {
            $passengerCriteria[] = [
                '@type'             => 'PassengerCriteria',
                'number'            => (int)$params['children'],
                'passengerTypeCode' => 'CNN',
                'age'               => 8,
            ];
        }

        // Infants
        if (($params['infants'] ?? 0) > 0) {
            $passengerCriteria[] = [
                '@type'             => 'PassengerCriteria',
                'number'            => (int)$params['infants'],
                'passengerTypeCode' => 'INF',
            ];
        }

        // Flight criteria
        $searchCriteria = [];
        $tripType = $params['trip_type'] ?? 'one_way';

        if ($tripType === 'multi_city' && !empty($params['legs'])) {
            // Multi-city: one SearchCriteriaFlight per leg (up to 6 for GDS)
            foreach ($params['legs'] as $leg) {
                $searchCriteria[] = [
                    '@type'         => 'SearchCriteriaFlight',
                    'departureDate' => $leg['date'],
                    'From'          => ['value' => strtoupper($leg['origin'])],
                    'To'            => ['value' => strtoupper($leg['destination'])],
                ];
            }
        } else {
            // One-way or round-trip
            $searchCriteria[] = [
                '@type'         => 'SearchCriteriaFlight',
                'departureDate' => $params['departure_date'],
                'From'          => ['value' => strtoupper($params['origin'])],
                'To'            => ['value' => strtoupper($params['destination'])],
            ];

            if ($tripType === 'round_trip' && !empty($params['return_date'])) {
                $searchCriteria[] = [
                    '@type'         => 'SearchCriteriaFlight',
                    'departureDate' => $params['return_date'],
                    'From'          => ['value' => strtoupper($params['destination'])],
                    'To'            => ['value' => strtoupper($params['origin'])],
                ];
            }
        }

        // Multi-city with 3+ legs: max 2 upsells per API docs
        // Max upsells: lower = smaller response. Default 2 instead of 4.
        // Can be forced to 0 via $params['no_upsells'] for minimal responses.
        $maxUpsells = 2;
        if (!empty($params['no_upsells'])) $maxUpsells = 0;
        if ($tripType === 'multi_city') {
            $legCount = count($searchCriteria);
            if ($legCount >= 4) $maxUpsells = 0;
            elseif ($legCount === 3) $maxUpsells = 1;
        }

        // Max results per page (smaller = smaller response)
        $maxResults = (int)($params['max_results'] ?? 50);
        if ($maxResults < 5) $maxResults = 5;
        if ($maxResults > 200) $maxResults = 200;

        // Build request body
        $requestBody = [
            '@type' => 'CatalogProductOfferingsQueryRequest',
            'CatalogProductOfferingsRequest' => [
                '@type'                      => 'CatalogProductOfferingsRequestAir',
                'maxNumberOfUpsellsToReturn'  => $maxUpsells,
                'offersPerPage'               => $maxResults,
                'contentSourceList'           => ['GDS'],
                'PassengerCriteria'           => $passengerCriteria,
                'SearchCriteriaFlight'        => $searchCriteria,
            ],
        ];

        // Build SearchModifiersAir — combines cabin preference + carrier preference + flight type
        $searchModifiers = ['@type' => 'SearchModifiersAir'];

        // Cabin preference
        $cabin = $params['cabin_class'] ?? 'Economy';
        if ($cabin !== 'Any') {
            $searchModifiers['CabinPreference'] = [
                [
                    '@type'          => 'CabinPreference',
                    'preferenceType' => 'Preferred',
                    'cabins'         => [ucfirst(strtolower($cabin))],
                ]
            ];
        }

        // Carrier preference — MAJOR response size reducer when set
        // "Permitted" restricts results to ONLY this airline
        $preferredAirline = strtoupper(trim($params['preferred_airline'] ?? ''));
        if ($preferredAirline) {
            $searchModifiers['CarrierPreference'] = [
                [
                    '@type'          => 'CarrierPreference',
                    'preferenceType' => 'Permitted',
                    'carriers'       => [$preferredAirline],
                ]
            ];
        }

        // Max stops (FlightType)
        $maxStops = $params['max_stops'] ?? '';
        if ($maxStops !== '' && $maxStops !== null) {
            $stopsNum = (int)$maxStops;
            if ($stopsNum === 0) {
                $searchModifiers['FlightType'] = [
                    '@type'                 => 'FlightType',
                    'nonStopDirects'        => 'Required',
                ];
            } else {
                $searchModifiers['FlightType'] = [
                    '@type'       => 'FlightType',
                    'maxStops'    => $stopsNum,
                ];
            }
        }

        // Only attach SearchModifiersAir if it has any preferences beyond @type
        if (count($searchModifiers) > 1) {
            $requestBody['CatalogProductOfferingsRequest']['SearchModifiersAir'] = $searchModifiers;
        }

        // Journey-based for round-trip and multi-city: returns separate offerings per leg
        if ($tripType === 'round_trip' || $tripType === 'multi_city') {
            $requestBody['CatalogProductOfferingsRequest']['CustomResponseModifiersAir'] = [
                '@type'                => 'CustomResponseModifiersAir',
                'SearchRepresentation' => 'Journey',
            ];
        }

        // print_r(json_encode($requestBody));
        // die();

        $r = $this->apiRequest(EP_SEARCH, 'POST', $requestBody);
        return $r;
    }

    // ================================================================
    // 2. PRICE OFFER
    // ================================================================

    /**
     * Price selected flights using BuildFromProducts (Full Payload)
     */
    public function priceOffer($pricePayload) {
        return $this->apiRequest(EP_PRICE, 'POST', $pricePayload);
    }

    /**
     * Price using Reference Payload (recommended for GDS + NDC compatibility)
     * POST /air/price/offers/buildfromcatalogproductofferings
     * 
     * @param string $catalogIdentifier  CatalogProductOfferings/Identifier/value from search
     * @param string $offeringId         CatalogProductOffering/id from search (e.g. "o1")
     * @param string $productRef         ProductBrandOffering/Product/productRef from search (e.g. "p0")
     * @param array  $passengerCriteria  Optional passenger criteria
     */
    public function priceOfferReference($catalogIdentifier, $selections, $passengerCriteria = null) {
        $payload = [
            'OfferQueryBuildFromCatalogProductOfferings' => [
                'BuildFromCatalogProductOfferingsRequest' => [
                    '@type' => 'BuildFromCatalogProductOfferingsRequestAir',
                    'CatalogProductOfferingsIdentifier' => [
                        'Identifier' => ['value' => $catalogIdentifier],
                    ],
                    'CatalogProductOfferingSelection' => $selections,
                ],
            ],
        ];

        if ($passengerCriteria) {
            $payload['OfferQueryBuildFromCatalogProductOfferings']['BuildFromCatalogProductOfferingsRequest']['PassengerCriteria'] = $passengerCriteria;
        }

        return $this->apiRequest(EP_PRICE_REF, 'POST', $payload);
    }

    /**
     * Add Offer using Reference Payload (recommended for GDS + NDC compatibility)
     * POST /air/book/airoffer/reservationworkbench/{wbID}/offers/buildfromcatalogproductofferings
     */
    public function addOfferReference($reservationId, $catalogIdentifier, $selections, $bookingId = null) {
        $endpoint = str_replace('{id}', $reservationId, EP_ADD_OFFER_REF);
        $payload = [
            'OfferQueryBuildFromCatalogProductOfferings' => [
                'BuildFromCatalogProductOfferingsRequest' => [
                    '@type' => 'BuildFromCatalogProductOfferingsRequestAir',
                    'CatalogProductOfferingsIdentifier' => [
                        'Identifier' => ['value' => $catalogIdentifier],
                    ],
                    'CatalogProductOfferingSelection' => $selections,
                ],
            ],
        ];
        return $this->apiRequest($endpoint, 'POST', $payload, $bookingId);
    }

    // ================================================================
    // 3. BOOKING WORKFLOW
    // ================================================================

    /**
     * Step 1: Initiate Reservation Workbench (start session)
     * Auto-retries if a stale workbench exists ("COMMIT OR IGNORE" error)
     */
    public function initiateWorkbench($bookingId = null) {
        $body = ['@type' => 'ReservationID'];

        // Try to discard any stale workbench from session
        $staleId = $_SESSION['last_workbench_id'] ?? null;
        if ($staleId) {
            try { $this->discardWorkbench($staleId); } catch (Exception $e) {}
            unset($_SESSION['last_workbench_id']);
        }

        $result = $this->apiRequest(EP_WORKBENCH, 'POST', $body, $bookingId);

        // Check for stale workbench error (4350: "COMMIT OR IGNORE RESERVATION WORKBENCH")
        $errors = $this->extractResponseErrors($result['data']);
        foreach ($errors as $err) {
            if (($err['SourceCode'] ?? '') === '4350' || stripos($err['Message'] ?? '', 'COMMIT OR IGNORE') !== false) {
                $staleId = $this->extractIdentifier($result['data']);
                if ($staleId) {
                    try { $this->discardWorkbench($staleId); } catch (Exception $e) {}
                }
                sleep(1);
                $result = $this->apiRequest(EP_WORKBENCH, 'POST', $body, $bookingId);
            }
        }

        // Track workbench ID
        $wbId = $this->extractIdentifier($result['data']);
        if ($wbId) $_SESSION['last_workbench_id'] = $wbId;

        return $result;
    }

    /**
     * Discard/cancel a workbench session
     * DELETE /air/book/session/reservationworkbench/{workbenchID}
     */
    public function discardWorkbench($workbenchId) {
        $endpoint = '/air/book/session/reservationworkbench/' . urlencode($workbenchId);
        return $this->apiRequest($endpoint, 'DELETE');
    }

    /**
     * Extract errors from a Travelport response body (HTTP 200 with errors)
     */
    public function extractResponseErrors($data): array {
        $errors = [];
        // Path 1: Result/Error
        $errs = $data['Result']['Error']
             ?? $data['ReservationResponse']['Result']['Error']
             ?? $data['OfferListResponse']['Result']['Error']
             ?? [];
        if (isset($errs['@type'])) $errs = [$errs];
        foreach ($errs as $e) {
            if (isset($e['Message'])) $errors[] = $e;
        }
        return $errors;
    }

    /**
     * Step 2: Add Traveler to workbench
     */
    public function addTraveler($reservationId, $travelerData, $bookingId = null) {
        $endpoint = str_replace('{id}', $reservationId, EP_TRAVELERS);
        return $this->apiRequest($endpoint, 'POST', $travelerData, $bookingId);
    }

    /**
     * Step 3: Add Air Offer to workbench
     */
    public function addOffer($reservationId, $offerPayload, $bookingId = null) {
        $endpoint = str_replace('{id}', $reservationId, EP_ADD_OFFER);
        return $this->apiRequest($endpoint, 'POST', $offerPayload, $bookingId);
    }

    /**
     * Step 4: Add Form of Payment
     */
    public function addFormOfPayment($reservationId, $fopData, $bookingId = null) {
        $endpoint = str_replace('{id}', $reservationId, EP_FOP);
        return $this->apiRequest($endpoint, 'POST', $fopData, $bookingId);
    }

    /**
     * Step 5: Apply Payment
     */
    public function applyPayment($reservationId, $paymentData, $bookingId = null) {
        $endpoint = str_replace('{id}', $reservationId, EP_PAYMENT);
        return $this->apiRequest($endpoint, 'POST', $paymentData, $bookingId);
    }

    /**
     * Step 6: Commit Reservation (with ticket issuance)
     */
    public function commitReservation($reservationId, $bookingId = null) {
        $endpoint = str_replace('{id}', $reservationId, EP_COMMIT);
        $body = ['@type' => 'ReservationQueryCommitReservation'];
        $result = $this->apiRequest($endpoint, 'POST', $body, $bookingId);
        unset($_SESSION['last_workbench_id']); // Workbench closed
        return $result;
    }

    // ================================================================
    // 4. RETRIEVE PNR
    // ================================================================

    /**
     * Retrieve reservation by PNR
     */
    public function retrieveReservation($pnr, $detailView = true, $bookingId = null) {
        $endpoint = str_replace('{pnr}', $pnr, EP_RETRIEVE);
        if ($detailView) {
            $endpoint .= '?detailViewInd=true';
        }
        return $this->apiRequest($endpoint, 'GET', null, $bookingId);
    }

    // ================================================================
    // 5. CANCEL BOOKING
    // ================================================================

    /**
     * Create Post-Commit Workbench for existing reservation (ticketing/modifications)
     * POST /air/book/session/reservationworkbench/buildfromlocator?Locator={PNR}
     * NO request body. Returns full reservation + workbench ID at ReservationResponse/Identifier/value
     */
    public function createPostCommitWorkbench($pnr, $bookingId = null) {
        $endpoint = EP_WORKBENCH_POST . '?Locator=' . urlencode($pnr);
        $result = $this->apiRequest($endpoint, 'POST', null, $bookingId);

        // Check for stale workbench (4350: "COMMIT OR IGNORE")
        $errors = $this->extractResponseErrors($result['data']);
        foreach ($errors as $err) {
            if (($err['SourceCode'] ?? '') === '4350' || stripos($err['Message'] ?? '', 'COMMIT OR IGNORE') !== false) {
                // Try to discard the stale workbench using last known ID from session
                $staleId = $_SESSION['last_workbench_id'] ?? $this->extractIdentifier($result['data']);
                if ($staleId) {
                    try { $this->discardWorkbench($staleId); } catch (Exception $e) {}
                }
                sleep(1);
                return $this->apiRequest($endpoint, 'POST', null, $bookingId);
            }
        }
        // Track workbench ID for future cleanup
        $wbId = $result['data']['ReservationResponse']['Identifier']['value'] ?? $this->extractIdentifier($result['data']);
        if ($wbId) $_SESSION['last_workbench_id'] = $wbId;

        return $result;
    }

    /**
     * Legacy alias
     */
    public function initiateWorkbenchWithRetrieve($pnr, $bookingId = null) {
        return $this->createPostCommitWorkbench($pnr, $bookingId);
    }

    /**
     * Cancel all items in workbench
     */
    public function cancelAll($reservationId, $bookingId = null) {
        $endpoint = str_replace('{id}', $reservationId, EP_CANCEL_ITEMS);
        $body = [
            '@type'        => 'CancelRequest',
            'cancelAllInd' => true,
        ];
        return $this->apiRequest($endpoint, 'POST', $body, $bookingId);
    }

    /**
     * Commit cancellation
     */
    public function commitCancellation($reservationId, $bookingId = null) {
        $endpoint = str_replace('{id}', $reservationId, EP_COMMIT);
        $body = ['@type' => 'ReservationQueryCommitReservation'];
        return $this->apiRequest($endpoint, 'POST', $body, $bookingId);
    }

    // ================================================================
    // 6. TICKET VOID
    // ================================================================

    /**
     * Void a ticket
     */
    public function voidTicket($ticketNumber) {
        $endpoint = str_replace('{ticketNumber}', $ticketNumber, EP_TICKET_VOID);
        $body = ['TicketQueryUpdateTicket' => new \stdClass()];
        return $this->apiRequest($endpoint, 'PUT', $body);
    }

    /**
     * Get ticket list for a reservation
     * GET /air/receipt/reservations/{PNR}/receipts
     * Returns: ReceiptListResponse/ReceiptID[]/Document[]/Number
     */
    public function getTicketList($pnr, $bookingId = null) {
        $endpoint = str_replace('{pnr}', urlencode($pnr), EP_TICKET_LIST);
        return $this->apiRequest($endpoint, 'GET', null, $bookingId);
    }

    /**
     * Get full ticket details (GDS)
     * GET /air/ticket/tickets/{ticketNumber}
     * Returns: TicketListResponse/TicketID[] with segments, prices, agency info
     */
    public function getTicketDisplay($ticketNumber, $bookingId = null) {
        $endpoint = str_replace('{ticketNumber}', urlencode($ticketNumber), EP_TICKET_DISPLAY);
        return $this->apiRequest($endpoint, 'GET', null, $bookingId);
    }

    // ================================================================
    // 6b. TRAVELER MODIFY (Update email, phone, passport, loyalty)
    // ================================================================

    /**
     * Get list of updatable items for traveler(s)
     * POST /air/book/updatableItem/reservationworkbench/{wbID}/travelerupdatableitems/buildfromtraveler
     */
    public function getUpdatableItems($workbenchId, $travelerIds, $bookingId = null) {
        $endpoint = str_replace('{id}', $workbenchId, EP_UPDATABLE_ITEMS);
        $body = [
            'TravelerUpdatableItemsQueryBuildFromTraveler' => [
                'TravelerIdentifier' => is_array($travelerIds) ? $travelerIds : [$travelerIds],
            ],
        ];
        return $this->apiRequest($endpoint, 'POST', $body, $bookingId);
    }

    /**
     * Update traveler information
     * PUT /air/book/traveler/reservationworkbench/{wbID}/travelers/updatefromtravelerupdateditems/{updatableId}
     */
    public function updateTraveler($workbenchId, $updatableId, $updatePayload, $bookingId = null) {
        $endpoint = str_replace(['{id}', '{updatableId}'], [$workbenchId, $updatableId], EP_TRAVELER_UPDATE);
        return $this->apiRequest($endpoint, 'PUT', $updatePayload, $bookingId);
    }

    /**
     * Cancel specific offers/segments from a reservation
     * POST /book/reservationworkbench/{wbID}/reservations/cancelitems
     * Note: base path does NOT include /air/
     */
    public function cancelSelectedOffers($workbenchId, $offerSelections, $bookingId = null) {
        $endpoint = str_replace('{id}', $workbenchId, EP_CANCEL_ITEMS);
        $body = [
            '@type' => 'CancelRequest',
            'cancelOffers' => [
                'objectType' => 'CancelSelectedOffers',
                'offerProductSelection' => $offerSelections,
            ],
        ];
        return $this->apiRequest($endpoint, 'POST', $body, $bookingId);
    }

    // ================================================================
    // 7. FARE RULES (after Search)
    // ================================================================

    /**
     * Get fare rules from search results
     * GET /air/farerule/farerules/fromcatalogproductofferings
     *   ?catalogProductOfferingsIdentifier={responseIdentifier}
     *   &catalogProductOfferingID={offerID}
     *   &productIDs={productID}
     *   &fareRuleType={ShortText|LongText|Structured}
     */
    public function getFareRules($catalogIdentifier, $offeringId, $productId, $ruleType = 'ShortText') {
        $queryParams = http_build_query([
            'catalogProductOfferingsIdentifier' => $catalogIdentifier,
            'catalogProductOfferingID'          => $offeringId,
            'productIDs'                        => $productId,
            'fareRuleType'                      => $ruleType,
        ]);
        $endpoint = EP_FARE_RULES . '?' . $queryParams;
        return $this->apiRequest($endpoint, 'GET');
    }

    /**
     * Get fare rules for existing reservation (after booking)
     * GET /air/farerule/farerules/fromreservation
     *   ?reservationIdentifier={LocatorCode}
     *   &fareRuleType={ShortText|LongText|Structured}
     *   &fareRuleCategories={optional: Penalties|MinimumStay|MaximumStay|etc.}
     */
    public function getFareRulesFromReservation($pnr, $ruleType = 'ShortText', $categories = null) {
        $params = [
            'reservationIdentifier' => $pnr,
            'fareRuleType'          => $ruleType,
        ];
        if ($categories) {
            $params['fareRuleCategories'] = $categories;
        }
        $endpoint = EP_FARE_RULES_RES . '?' . http_build_query($params);
        return $this->apiRequest($endpoint, 'GET');
    }

    // ================================================================
    // 8. SEAT MAP (after Search / during Workbench)
    // ================================================================

    /**
     * Get seat map after search (reference payload)
     * POST /air/search/seat/catalogofferingsancillaries/seatavailabilities
     * 
     * Uses SeatAvailabilityOfferingsBuildFromCatalogProductOfferings
     * with identifiers from the search response. NO query params, NO SpecificFlightCriteria.
     */
    public function getSeatMapFromSearch($catalogIdentifier, $offeringId, $productId = null) {
        $selection = [
            '@type' => 'CatalogProductOfferingSelection',
            'CatalogProductOfferingIdentifier' => ['id' => $offeringId],
        ];
        if ($productId) {
            $selection['ProductIdentifier'] = [['id' => $productId]];
        }

        $body = [
            'CatalogOfferingsQuerySeatAvailability' => [
                'SeatAvailabilityOfferings' => [
                    '@type' => 'SeatAvailabilityOfferingsBuildFromCatalogProductOfferings',
                    'BuildFromCatalogProductOfferingsRequest' => [
                        '@type' => 'BuildFromCatalogProductOfferingsRequest',
                        'CatalogProductOfferingsIdentifier' => [
                            'id'         => 'catalogProductOfferings_1',
                            'Identifier' => [
                                'value'     => $catalogIdentifier,
                                'authority' => 'Travelport',
                            ],
                        ],
                        'CatalogProductOfferingSelection' => [$selection],
                    ],
                ],
            ],
        ];

        return $this->apiRequest(EP_SEAT_MAP, 'POST', $body);
    }

    /**
     * Get seat map during workbench (for existing reservation)
     */
    public function getSeatMapInWorkbench($reservationId, $offerId, $productIds = []) {
        $body = [
            '@type' => 'CatalogOfferingsQuerySeatAvailability',
            'SeatAvailabilityOfferings' => [
                '@type' => 'SeatAvailabilityOfferingsBuildFromReservationWorkbench',
                'BuildFromReservationWorkbench' => [
                    'ReservationIdentifier' => [
                        'Identifier' => ['authority' => 'Travelport', 'value' => $reservationId],
                    ],
                    'OfferIdentifier' => [
                        'Identifier' => ['authority' => 'Travelport', 'value' => $offerId],
                    ],
                ],
            ],
        ];

        if (!empty($productIds)) {
            $prodList = [];
            foreach ($productIds as $pid) {
                $prodList[] = ['Identifier' => ['value' => $pid]];
            }
            $body['SeatAvailabilityOfferings']['BuildFromReservationWorkbench']['ProductIdentifier'] = $prodList;
        }

        return $this->apiRequest(EP_SEAT_MAP_WB, 'POST', $body);
    }

    /**
     * Book seat(s) during workbench session
     * POST /book/airoffer/reservationworkbench/{wbID}/offers/buildancillaryoffersfromcatalogofferings
     * 
     * @param string $workbenchId    Workbench identifier
     * @param string $catalogOfferingsId  From seat map: CatalogOfferingsAncillaryListResponse/Identifier/value
     * @param string $catalogOfferingId   From seat map: CatalogOfferingsID/Identifier/value
     * @param string $travelerId          Traveler identifier value
     * @param string $seatNumber          Seat to book e.g. "15A"
     * @param int|null $bookingId         For logging
     */
    public function bookSeat($workbenchId, $catalogOfferingsId, $catalogOfferingId, $travelerId, $seatNumber, $bookingId = null) {
        $endpoint = str_replace('{id}', $workbenchId, EP_SEAT_BOOK);
        $body = [
            'OfferQueryBuildAncillaryOffersFromCatalogOfferings' => [
                'BuildAncillaryOffersFromCatalogOfferings' => [
                    [
                        '@type' => 'BuildAncillaryOffersFromCatalogOfferingsAirSeat',
                        'CatalogOfferingsIdentifier' => [
                            'Identifier' => ['value' => $catalogOfferingsId],
                        ],
                        'CatalogOfferingIdentifier' => [
                            'Identifier' => ['value' => $catalogOfferingId],
                        ],
                        'TravelerIdentifierRef' => [
                            'value' => $travelerId,
                        ],
                        'SeatAssignment' => $seatNumber,
                    ],
                ],
            ],
        ];
        return $this->apiRequest($endpoint, 'POST', $body, $bookingId);
    }

    // ================================================================
    // 9. POST-COMMIT TICKETING (after Checkout.com payment)
    // ================================================================

    /**
     * Complete ticketing on a held PNR:
     * 1. Initiate workbench with PNR
     * 2. Add FOP
     * 3. Apply Payment
     * 4. Commit with ticketing
     */
    public function completeTicketing($pnr, $cardData, $amount, $currency, $bookingId = null) {
        // ================================================================
        // Travelport Ticketing Workflow (matching Postman collection):
        // 1. POST buildfromlocator?Locator={PNR}       → workbench ID
        // 2. POST /payment/.../formofpayment            → FOP
        // 3. POST /paymentoffer/.../payments             → Payment
        // 4. POST /book/reservation/reservations/{wbID}  → Commit (issues ticket)
        // ================================================================

        // Step 1: Create Post-Commit Workbench
        $wbResult = $this->createPostCommitWorkbench($pnr, $bookingId);
        $wbData = $wbResult['data'];

        // Workbench ID at ReservationResponse/Identifier/value
        $workbenchId = $wbData['ReservationResponse']['Identifier']['value'] ?? null;
        if (!$workbenchId) {
            $workbenchId = $wbData['Identifier']['value'] ?? null;
        }
        if (!$workbenchId) {
            throw new Exception("Could not obtain workbench ID. Response: " . substr(json_encode($wbData), 0, 500));
        }

        // Extract offer identifiers
        $offerIdentifiers = $this->extractOfferIdentifiers($wbData);
        if (empty($offerIdentifiers)) {
            throw new Exception("No offers found in workbench. PNR may have expired.");
        }

        // Get price from workbench if not provided
        if (!$amount || $amount <= 0) {
            $reservation = $wbData['ReservationResponse']['Reservation'] ?? [];
            $offers = $reservation['Offer'] ?? [];
            if (isset($offers['@type'])) $offers = [$offers];
            if (!empty($offers[0]['Price']['TotalPrice'])) {
                $amount = $offers[0]['Price']['TotalPrice'];
                $currency = $offers[0]['Price']['CurrencyCode']['value'] ?? $currency;
            }
        }

        // Step 2: Add FOP (matching Postman "Add FOP Post Commit Reservation Credit Card")
        $fopId = 'formOfPayment_1';
        $fopPayload = [
            '@type'            => 'FormOfPaymentPaymentCard',
            'id'               => $fopId,
            'FormOfPaymentRef' => $fopId,
            'PaymentCard'      => [
                '@type'          => 'PaymentCardDetail',
                'id'             => 'paymentCard_1',
                'expireDate'     => $cardData['expiry'],
                'CardType'       => 'Credit',
                'CardCode'       => $cardData['card_code'] ?? 'VI',
                'CardHolderName' => $cardData['holder_name'],
                'approvalCode'   => '123456',
                'CardNumber'     => [
                    '@type'     => 'CardNumber',
                    'PlainText' => $cardData['card_number'],
                ],
                'SeriesCode'     => [
                    'PlainText' => $cardData['cvv'] ?? '123',
                ],
            ],
        ];

        $fopEndpoint = str_replace('{id}', $workbenchId, EP_FOP);
        $this->apiRequest($fopEndpoint, 'POST', $fopPayload, $bookingId);

        // Step 3: Add Payment (matching Postman "Add Payment")
        $paymentPayload = [
            'Payment' => [
                'id'         => 'payment_1',
                'Identifier' => [
                    'authority' => 'Travelport',
                    'value'     => 'A0656EFF-FAF4-456F-B061-0161008D6A5E',
                ],
                'Amount' => [
                    'code'           => $currency,
                    'minorUnit'      => 2,
                    'currencySource' => 'Charged',
                    'approximateInd' => true,
                    'value'          => (string)((float)$amount),
                ],
                'FormOfPaymentIdentifier' => [
                    'id'               => $fopId,
                    'FormOfPaymentRef' => $fopId,
                    'Identifier'       => [
                        'authority' => 'Travelport',
                        'value'     => 'A0656EFF-FAF4-456F-B061-0161008D6FOP',
                    ],
                ],
                'OfferIdentifier' => $offerIdentifiers,
            ],
        ];

        $paymentEndpoint = str_replace('{id}', $workbenchId, EP_PAYMENT);
        $this->apiRequest($paymentEndpoint, 'POST', $paymentPayload, $bookingId);

        // Step 4: Commit → issues ticket
        // Postman: {"@type": "ReservationQueryCommitReservation"}
        $commitPayload = [
            '@type' => 'ReservationQueryCommitReservation',
        ];

        $commitEndpoint = str_replace('{id}', $workbenchId, EP_COMMIT);
        return $this->apiRequest($commitEndpoint, 'POST', $commitPayload, $bookingId);
    }

    /**
     * Extract workbench/reservation identifier from API response
     * For post-commit workbench: ReservationResponse/Identifier/value
     */
    public function extractIdentifier($data) {
        // Post-commit workbench: top-level Identifier (outside Reservation)
        if (isset($data['ReservationResponse']['Identifier']['value'])) {
            return $data['ReservationResponse']['Identifier']['value'];
        }
        // Inside Reservation
        if (isset($data['ReservationResponse']['Reservation']['Identifier']['value'])) {
            return $data['ReservationResponse']['Reservation']['Identifier']['value'];
        }
        if (isset($data['Identifier']['value'])) {
            return $data['Identifier']['value'];
        }
        return null;
    }

    /**
     * Extract offer identifiers from workbench/reservation response
     * Returns format needed for Add Payment: [{id, offerRef, Identifier: {authority, value}}]
     */
    public function extractOfferIdentifiers($data) {
        $identifiers = [];
        $reservation = $data['ReservationResponse']['Reservation'] ?? $data['Reservation'] ?? $data;
        $offers = $reservation['Offer'] ?? [];
        if (isset($offers['@type'])) $offers = [$offers];

        foreach ($offers as $off) {
            if (isset($off['Identifier']['value'])) {
                $offerId = $off['id'] ?? 'offer_1';
                $identifiers[] = [
                    'id'       => $offerId,
                    'offerRef' => $offerId,
                    'Identifier' => [
                        'authority' => $off['Identifier']['authority'] ?? 'Travelport',
                        'value'     => $off['Identifier']['value'],
                    ],
                ];
            }
        }
        return $identifiers;
    }

    // ================================================================
    // HELPER: Build traveler object for API
    // ================================================================

    /**
     * Build traveler payload from passenger form data
     */
    public function buildTravelerPayload($passenger, $travelerId = 'trav_1') {
        $traveler = [
            '@type'             => 'Traveler',
            'gender'            => $passenger['gender'],
            'birthDate'         => $passenger['date_of_birth'],
            'id'                => $travelerId,
            'passengerTypeCode' => $passenger['passenger_type'] ?? 'ADT',
            'PersonName'        => [
                '@type'   => 'PersonNameDetail',
                'Given'   => $passenger['first_name'],
                'Surname' => $passenger['last_name'],
            ],
            'Telephone' => [
                [
                    '@type'             => 'Telephone',
                    'countryAccessCode' => $passenger['phone_code'] ?? '1',
                    'phoneNumber'       => $passenger['phone_number'],
                    'role'              => 'Home',
                ],
            ],
            'Email' => [
                ['value' => $passenger['email']],
            ],
        ];

        // Add passport if provided
        if (!empty($passenger['passport_number'])) {
            $traveler['TravelDocument'] = [
                [
                    '@type'        => 'TravelDocumentDetail',
                    'docNumber'    => $passenger['passport_number'],
                    'docType'      => 'Passport',
                    'expireDate'   => $passenger['passport_expiry'] ?? '',
                    'issueCountry' => $passenger['passport_country'] ?? 'US',
                    'birthDate'    => $passenger['date_of_birth'],
                    'Gender'       => $passenger['gender'],
                    'PersonName'   => [
                        '@type'   => 'PersonName',
                        'Given'   => $passenger['first_name'],
                        'Surname' => $passenger['last_name'],
                    ],
                ],
            ];
        }

        return $traveler;
    }

    /**
     * Build Form of Payment (Credit Card) payload
     */
    public function buildCardFOP($cardData, $fopId = 'formOfPayment_1') {
        return [
            '@type'            => 'FormOfPaymentPaymentCard',
            'id'               => $fopId,
            'FormOfPaymentRef' => $fopId,
            'PaymentCard'      => [
                '@type'          => 'PaymentCard',
                'id'             => 'paymentCard_1',
                'expireDate'     => $cardData['expiry'], // MMYY format
                'CardType'       => 'Credit',
                'CardCode'       => $cardData['card_code'] ?? 'VI', // VI, CA, AX, etc.
                'CardHolderName' => $cardData['holder_name'],
                'CardNumber'     => [
                    'PlainText' => $cardData['card_number'],
                ],
            ],
        ];
    }

    /**
     * Build Payment payload
     */
    public function buildPaymentPayload($amount, $currency, $fopIdentifierValue, $offerIdentifier, $paymentId = 'payment_1', $fopId = 'formOfPayment_1') {
        return [
            '@type'      => 'Payment',
            'id'         => $paymentId,
            'Identifier' => [
                'authority' => 'Travelport',
                'value'     => 'A0656EFF-FAF4-456F-B061-0161008D6A5E',
            ],
            'Amount' => [
                'code'           => $currency,
                'minorUnit'      => 2,
                'currencySource' => 'Charged',
                'value'          => (float)$amount,
            ],
            'FormOfPaymentIdentifier' => [
                'id'               => $fopId,
                'FormOfPaymentRef' => $fopId,
                'Identifier'       => [
                    'authority' => 'Travelport',
                    'value'     => $fopIdentifierValue,
                ],
            ],
            'OfferIdentifier' => $offerIdentifier,
        ];
    }
}
