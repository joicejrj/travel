<?php
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/functions.php';
require_once __DIR__ . '/../../public/_auth.php';
require_once __DIR__ . '/../../lib/amadeus.php';

header('Content-Type: application/json');

$customer_id = !empty($_POST['contact_entity_id']) ? (int)$_POST['contact_entity_id'] : null;

try {
    $origin        = strtoupper(trim($_POST['origin'] ?? ''));
    $destination   = strtoupper(trim($_POST['destination'] ?? ''));
    $departureDate = $_POST['departureDate'] ?? '';
    $returnDate    = $_POST['returnDate'] ?? '';
    $adults        = max(1, (int)($_POST['adults'] ?? 1));
    $travelClass   = $_POST['travelClass'] ?? 'ECONOMY';
    $nonStop       = !empty($_POST['nonStop']);
    $max           = min(50, max(1, (int)($_POST['max'] ?? 10)));

    $provider = $_POST['provider'] ?? 'AMADEUS';
    if ($provider === 'TRAVELPORT') {
        $adults   = (int)($_POST['tp_adults'] ?? 1);
        $children = (int)($_POST['tp_children'] ?? 0);
        $infants  = (int)($_POST['tp_infants'] ?? 0);
    } else {
        $adults   = (int)($_POST['adults'] ?? 1);
        $children = 0;
        $infants  = 0;
    }

    if (!$origin || !$destination || !$departureDate) {
        throw new Exception('Missing required fields'.($origin." ".$destination." ".$departureDate));
    }

    if ($origin === $destination) {
        throw new Exception('Origin and destination cannot be the same');
    }

    if ($returnDate && $returnDate <= $departureDate) {
        throw new Exception('Return date must be after departure date');
    }

    if ($provider === 'TRAVELPORT') {

        /* -----------------------------------------
           TRAVELPORT REQUEST
        ----------------------------------------- */
        require_once __DIR__ . '/../includes/TravelportAPI.php';

        function buildTpPayload($o, $query){
          $segments = $o['segments'];

          $pc = [];

          foreach($segments as $i => $s){

            $pc[] = [
              'SpecificFlightCriteria' => [[
                'flightNumber' => $s['flightNumber'],
                'carrier' => $s['carrier'],
                'departureDate' => $s['departureDate'],
                'departureTime' => $s['departureTime'],
                'arrivalDate' => $s['arrivalDate'],
                'arrivalTime' => $s['arrivalTime'],
                'from' => $s['origin'],
                'to' => $s['destination'],
                'classOfService' => $s['classOfService'],
                'cabin' => $s['cabin'],
                'segmentSequence' => $i+1,
                'boundFlightsInd' => true,
                'ContentSource' => 'GDS'
              ]],
              'sequence' => $i+1
            ];
          }

          return [
            'OfferQueryBuildFromProducts' => [
              'BuildFromProductsRequest' => [
                '@type' => 'BuildFromProductsRequestAir',
                'PassengerCriteria' => [
                  [
                    '@type'=>'PassengerCriteria',
                    'number'=>(int)($query['adults'] ?? 1),
                    'passengerTypeCode'=>'ADT'
                  ]
                ],
                'ProductCriteriaAir' => $pc
              ]
            ]
          ];
        }

        $query = [
            'trip_type'      => $returnDate ? 'roundtrip' : 'oneway',
            'origin'         => $origin,
            'destination'    => $destination,
            'departure_date' => $departureDate,
            'return_date'    => $returnDate,
            'adults'         => $adults,
            'children'       => $children,
            'infants'        => $infants,
            'cabin_class'    => $travelClass
        ];

        $api = new TravelportAPI();
        $result = $api->searchFlights($query);

        $raw = $result['data'] ?? [];

        $parsed = parseSearchResults($raw);

        // Store catalog identifier for fare rules API
        $catalogIdentifier = $raw['CatalogProductOfferingsResponse']['CatalogProductOfferings']['Identifier']['value']
                          ?? $raw['CatalogProductOfferings']['Identifier']['value']
                          ?? '';
        $_SESSION['catalog_identifier'] = $catalogIdentifier;

        /* -----------------------------------------
           NORMALIZE → MATCH AMADEUS FORMAT
        ----------------------------------------- */
        $offers = [];

        foreach ($parsed as $o) {

            $segments = [];

            foreach ($o['segments'] as $s) {

                $depISO = trim(($s['departureDate'] ?? '') . 'T' . ($s['departureTime'] ?? '00:00'));
                $arrISO = trim(($s['arrivalDate'] ?? '') . 'T' . ($s['arrivalTime'] ?? '00:00'));

                $segments[] = [
                    'carrierCode' => $s['carrier'],
                    'number'      => $s['flightNumber'],
                    'departure'   => [
                        'iataCode' => $s['origin'],
                        'at'       => $depISO
                    ],
                    'arrival'     => [
                        'iataCode' => $s['destination'],
                        'at'       => $arrISO
                    ],
                    'duration'    => $s['duration'] ?: 'PT0H'
                ];
            }

            // Get product ref for fare rules
            $frProductId = '';
            $frProducts = $o['rawBrandOffering']['Product'] ?? [];
            if (isset($frProducts['@type'])) $frProducts = [$frProducts];
            foreach ($frProducts as $frp) {
                $frProductId = $frp['productRef'] ?? '';
                if ($frProductId) break;
            }
            $showfare = false;
            if ($frProductId && $o['offeringId']) {
                $showfare = true;
                $frProductId = htmlspecialchars($frProductId);
                $o['offeringId'] = htmlspecialchars($o['offeringId']);
            }
            else {
                $o['offeringId'] = '';
            }

            $offers[] = [
                'itineraries' => [
                    [
                        'duration' => sumDurations($o['segments']),
                        'segments' => $segments
                    ]
                ],
                'price' => [
                    'currency'   => $o['currency'] ?? 'GBP',
                    'grandTotal' => (string)($o['totalPrice'] ?? 0)
                ],
                'numberOfBookableSeats' => 9,
                'offeringId' => $o['offeringId'],
                'showfare' => $showfare,
                'frProductId' => $frProductId,

                /* keep raw for later pricing */
                'tp_raw' => $o,
                'tp_offer_payload' => buildTpPayload($o, $query)
            ];
        }

        $resp = ['data' => $offers];
    } else {
        /* -----------------------------------------
           AMADEUS REQUEST
        ----------------------------------------- */
        $query = [
            'originLocationCode'      => $origin,
            'destinationLocationCode' => $destination,
            'departureDate'           => $departureDate,
            'adults'                  => $adults,
            'currencyCode'            => 'GBP',
            'max'                     => $max
        ];
        if ($returnDate)  $query['returnDate']  = $returnDate;
        if ($travelClass) $query['travelClass'] = $travelClass;
        if ($nonStop)     $query['nonStop']     = 'true';
        $resp = amadeus_request('GET', '/v2/shopping/flight-offers', null, $query);
    }

    if ($customer_id != null) {
        $paxText = ($provider === 'TRAVELPORT') ? "ADT: $adults, CHD: $children, INF: $infants" : "ADT: $adults";
        $site->agent_log("[$provider] Searched flights with origin: $origin, destination: $destination, departureDate: $departureDate"
            . ($returnDate ? ", returnDate: $returnDate" : "")
            . ", pax: $paxText"
            . ", class: $travelClass"
            . ($nonStop ? ", non-stop: Yes" : ""),
            $customer_id,
            "customer"
        );
    }

    /* ---------------------------------------------------
       SAVE SEARCH HISTORY
    --------------------------------------------------- */

    if($customer_id){

        $stmt = $mysqli->prepare("
            INSERT INTO flight_search_history
            (
                customer_id,
                origin,
                destination,
                departure_date,
                return_date,
                adults,
                child_no,
                infant_no,
                travel_class,
                non_stop,
                currency,
                max_results,
                provider,
                search_payload
            )
            VALUES
            (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");

        $payload = json_encode([
            'provider' => $provider,
            'query'    => $query,
            'pax' => [
                'adults'   => $adults,
                'children' => $children,
                'infants'  => $infants
            ]
        ]);

        $stmt->bind_param(
            "issssiiissssss",
            $customer_id,
            $origin,
            $destination,
            $departureDate,
            $returnDate,
            $adults,
            $children,
            $infants,
            $travelClass,
            $nonStop,
            $query['currencyCode'],
            $max,
            $provider,
            $payload
        );

        $stmt->execute();
    }

    echo json_encode([
        'success' => true,
        'offers'  => $resp['data'] ?? []
    ]);

} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ]);
}
?>