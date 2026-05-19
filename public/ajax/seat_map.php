<?php

// ini_set("display_errors", 0); error_reporting(E_ALL);
/**
 * JRJ TravelPort — Seat Map AJAX Handler
 * GET /api/seat_map.php?offering_id=o1&product_id=p0
 * 
 * Uses reference payload from search:
 *   POST /air/search/seat/catalogofferingsancillaries/seatavailabilities
 *   Body: SeatAvailabilityOfferingsBuildFromCatalogProductOfferings
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/TravelportAPI.php';

header('Content-Type: application/json');

$catalogIdentifier = $_SESSION['catalog_identifier'] ?? '';
$offeringId = sanitize($_GET['offering_id'] ?? '');
$productId  = sanitize($_GET['product_id'] ?? '');

if (!$catalogIdentifier) {
    jsonResponse(['error' => 'Search session expired. Please search again.'], 400);
}
if (!$offeringId) {
    jsonResponse(['error' => 'Missing offering ID.'], 400);
}

try {
    $api = new TravelportAPI();
    $result = $api->getSeatMapFromSearch($catalogIdentifier, $offeringId, $productId ?: null);
    $data = $result['data'] ?? [];

    // ================================================================
    // Parse CatalogOfferingsAncillaryListResponse
    // ================================================================
    $response = $data['CatalogOfferingsAncillaryListResponse'] ?? $data;
    $catalogOfferingsIDs = $response['CatalogOfferingsID'] ?? [];
    if (isset($catalogOfferingsIDs['@type'])) $catalogOfferingsIDs = [$catalogOfferingsIDs];

    $seatMaps = [];

    foreach ($catalogOfferingsIDs as $cofId) {
        // Flight info
        $flights = $cofId['Flight'] ?? [];
        if (isset($flights['@type'])) $flights = [$flights];

        $flightInfo = null;
        if (!empty($flights[0])) {
            $f = $flights[0];
            $flightInfo = [
                'carrier'   => $f['carrier'] ?? '',
                'number'    => $f['number'] ?? '',
                'from'      => $f['Departure']['location'] ?? '',
                'to'        => $f['Arrival']['location'] ?? '',
                'date'      => $f['Departure']['date'] ?? '',
                'equipment' => $f['equipment'] ?? '',
            ];
        }

        // Parse CatalogOfferings (each = a price tier: free, preferred, extra legroom etc.)
        $catalogOfferings = $cofId['CatalogOffering'] ?? [];
        if (isset($catalogOfferings['@type'])) $catalogOfferings = [$catalogOfferings];

        $seats = ['available' => [], 'occupied' => [], 'premium' => []];
        $seatPrices = [];

        foreach ($catalogOfferings as $co) {
            $price = (float)($co['Price']['TotalPrice'] ?? 0);
            $isFree = ($price == 0);

            $productOptions = $co['ProductOptions'] ?? [];
            if (isset($productOptions['@type'])) $productOptions = [$productOptions];

            foreach ($productOptions as $po) {
                $products = $po['Product'] ?? [];
                if (isset($products['@type'])) $products = [$products];

                foreach ($products as $prod) {
                    $brandName = $prod['Brand']['name'] ?? '';
                    $seatAvailList = $prod['SeatAvailability'] ?? [];
                    if (isset($seatAvailList['seatAvailabilityStatus'])) $seatAvailList = [$seatAvailList];

                    foreach ($seatAvailList as $sa) {
                        $status   = $sa['seatAvailabilityStatus'] ?? '';
                        $seatNums = $sa['value'] ?? [];
                        if (!is_array($seatNums)) $seatNums = [$seatNums];

                        foreach ($seatNums as $sn) {
                            if ($status === 'Available') {
                                if ($isFree) {
                                    $seats['available'][] = $sn;
                                } else {
                                    $seats['premium'][] = $sn;
                                    $seatPrices[$sn] = ['price' => $price, 'brand' => $brandName];
                                }
                            } else {
                                // Reserved, Blocked, NoSeat, Unavailable
                                $seats['occupied'][] = $sn;
                            }
                        }
                    }
                }
            }
        }

        // Dedupe and clean
        $seats['available'] = array_values(array_unique($seats['available']));
        $seats['premium']   = array_values(array_unique($seats['premium']));
        $bookable = array_merge($seats['available'], $seats['premium']);
        $seats['occupied'] = array_values(array_unique(array_diff($seats['occupied'], $bookable)));

        $seatMaps[] = [
            'flight'         => $flightInfo,
            'seats'          => $seats,
            'seatPrices'     => $seatPrices,
            'totalAvailable' => count($seats['available']),
            'totalPremium'   => count($seats['premium']),
            'totalOccupied'  => count($seats['occupied']),
        ];
    }

    // ================================================================
    // Parse ReferenceListSeatingChart → Cabin layout
    // ================================================================
    $cabinLayouts = [];
    $refLists = $response['ReferenceList'] ?? [];
    if (isset($refLists['@type'])) $refLists = [$refLists];

    foreach ($refLists as $rl) {
        if (($rl['@type'] ?? '') !== 'ReferenceListSeatingChart') continue;
        $charts = $rl['SeatingChart'] ?? [];
        if (isset($charts['@type'])) $charts = [$charts];

        foreach ($charts as $chart) {
            $cabins = $chart['Cabin'] ?? [];
            if (isset($cabins['@type'])) $cabins = [$cabins];

            foreach ($cabins as $cabin) {
                $layout = $cabin['Layout'] ?? [];
                $rows = $cabin['Row'] ?? [];
                if (isset($rows['@type'])) $rows = [$rows];

                // Parse Layout: columns + positions
                $columns = [];
                $startRow = $endRow = null;
                foreach ($layout as $l) {
                    if (isset($l['startRow'])) {
                        $startRow = $l['startRow'];
                        $endRow   = $l['endRow'] ?? $startRow;
                    } elseif (isset($l['value']) && isset($l['position'])) {
                        $pos = is_array($l['position']) ? $l['position'][0] : $l['position'];
                        $columns[] = ['letter' => $l['value'], 'position' => $pos];
                    }
                }

                // Parse Row → Space (seat characteristics)
                $rowDetails = [];
                foreach ($rows as $row) {
                    $spaces = $row['Space'] ?? [];
                    if (isset($spaces['@type'])) $spaces = [$spaces];
                    $seatDetails = [];
                    foreach ($spaces as $sp) {
                        $seatDetails[] = [
                            'location'        => $sp['location'] ?? '',
                            'characteristics' => $sp['Characteristic'] ?? [],
                        ];
                    }
                    $rowDetails[] = ['label' => $row['label'] ?? '', 'seats' => $seatDetails];
                }

                $cabinLayouts[] = [
                    'name'     => $cabin['name'] ?? 'ECONOMY',
                    'columns'  => $columns,
                    'startRow' => $startRow,
                    'endRow'   => $endRow,
                    'rows'     => $rowDetails,
                ];
            }
        }
    }

    jsonResponse([
        'success'      => true,
        'seatMaps'     => $seatMaps,
        'cabinLayouts' => $cabinLayouts,
        'debug'        => $local ? ['rawResponse' => $data] : null,
    ]);

} catch (Exception $e) {
    $msg = $e->getMessage();
    if (stripos($msg, '<!doctype') !== false || stripos($msg, '<html') !== false || stripos($msg, 'Server error') !== false) {
        jsonResponse([
            'error' => 'Seat map is not available for this flight. The carrier may not support seat maps through GDS, or the search session may have expired.',
            'debug' => $local ? $msg : null,
        ]);
    } else {
        jsonResponse([
            'error' => 'Could not load seat map: ' . $msg,
            'debug' => $local ? $msg : null,
        ]);
    }
}
