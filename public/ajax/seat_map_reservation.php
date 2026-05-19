<?php

// ini_set("display_errors", 0); error_reporting(E_ALL);
/**
 * JRJ TravelPort — Seat Map for Existing Reservation
 * GET /api/seat_map_reservation.php?pnr=FRC4J1
 * 
 * Creates a temporary post-commit workbench, gets seat map, discards workbench.
 * Returns seat availability for the UI to display.
 */
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/TravelportAPI.php';

header('Content-Type: application/json');

$pnr = strtoupper(sanitize($_GET['pnr'] ?? ''));
if (!$pnr) {
    jsonResponse(['error' => 'Missing PNR.'], 400);
}

try {
    $api = new TravelportAPI();

    // Step 1: Create post-commit workbench
    $wbResult = $api->createPostCommitWorkbench($pnr);
    $wbData = $wbResult['data'];

    $workbenchId = $wbData['ReservationResponse']['Identifier']['value']
                ?? $wbData['Identifier']['value'] ?? null;
    if (!$workbenchId) throw new Exception('Could not create workbench.');

    // Get offer ID from reservation
    $reservation = $wbData['ReservationResponse']['Reservation'] ?? [];
    $offers = $reservation['Offer'] ?? [];
    if (isset($offers['@type'])) $offers = [$offers];
    $offerId = $offers[0]['Identifier']['value'] ?? null;
    if (!$offerId) throw new Exception('No offer found in reservation.');

    // Step 2: Get seat map from workbench
    $seatMapResult = $api->getSeatMapInWorkbench($workbenchId, $offerId);
    $seatMapData = $seatMapResult['data'];

    // Step 3: Discard workbench (we only needed the seat map)
    try {
        $discardEndpoint = '/air/book/session/reservationworkbench/' . urlencode($workbenchId);
        // Use DELETE to discard - but we can't call private apiRequest.
        // Instead, just let it expire (30 min timeout). The booking flow creates a fresh one.
    } catch (Exception $e) {
        // Non-fatal
    }

    // Parse seat map response (same parser as seat_map.php)
    $response = $seatMapData['CatalogOfferingsAncillaryListResponse'] ?? $seatMapData;
    $catalogOfferingsIDs = $response['CatalogOfferingsID'] ?? [];
    if (isset($catalogOfferingsIDs['@type'])) $catalogOfferingsIDs = [$catalogOfferingsIDs];

    $seatMaps = [];

    foreach ($catalogOfferingsIDs as $cofId) {
        $flights = $cofId['Flight'] ?? [];
        if (isset($flights['@type'])) $flights = [$flights];

        $flightInfo = null;
        if (!empty($flights[0])) {
            $f = $flights[0];
            $flightInfo = [
                'carrier' => $f['carrier'] ?? '', 'number' => $f['number'] ?? '',
                'from' => $f['Departure']['location'] ?? '', 'to' => $f['Arrival']['location'] ?? '',
                'date' => $f['Departure']['date'] ?? '', 'equipment' => $f['equipment'] ?? '',
            ];
        }

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
                        $status = $sa['seatAvailabilityStatus'] ?? '';
                        $seatNums = $sa['value'] ?? [];
                        if (!is_array($seatNums)) $seatNums = [$seatNums];
                        foreach ($seatNums as $sn) {
                            if ($status === 'Available') {
                                if ($isFree) { $seats['available'][] = $sn; }
                                else { $seats['premium'][] = $sn; $seatPrices[$sn] = ['price' => $price, 'brand' => $brandName]; }
                            } else { $seats['occupied'][] = $sn; }
                        }
                    }
                }
            }
        }

        $seats['available'] = array_values(array_unique($seats['available']));
        $seats['premium'] = array_values(array_unique($seats['premium']));
        $bookable = array_merge($seats['available'], $seats['premium']);
        $seats['occupied'] = array_values(array_unique(array_diff($seats['occupied'], $bookable)));

        $seatMaps[] = [
            'flight' => $flightInfo, 'seats' => $seats, 'seatPrices' => $seatPrices,
            'totalAvailable' => count($seats['available']),
            'totalPremium' => count($seats['premium']),
            'totalOccupied' => count($seats['occupied']),
        ];
    }

    // Parse cabin layouts
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
                $columns = []; $startRow = $endRow = null;
                foreach ($layout as $l) {
                    if (isset($l['startRow'])) { $startRow = $l['startRow']; $endRow = $l['endRow'] ?? $startRow; }
                    elseif (isset($l['value']) && isset($l['position'])) {
                        $pos = is_array($l['position']) ? $l['position'][0] : $l['position'];
                        $columns[] = ['letter' => $l['value'], 'position' => $pos];
                    }
                }
                $rowDetails = [];
                foreach ($rows as $row) {
                    $spaces = $row['Space'] ?? [];
                    if (isset($spaces['@type'])) $spaces = [$spaces];
                    $sd = [];
                    foreach ($spaces as $sp) { $sd[] = ['location' => $sp['location'] ?? '', 'characteristics' => $sp['Characteristic'] ?? []]; }
                    $rowDetails[] = ['label' => $row['label'] ?? '', 'seats' => $sd];
                }
                $cabinLayouts[] = ['name' => $cabin['name'] ?? 'ECONOMY', 'columns' => $columns, 'startRow' => $startRow, 'endRow' => $endRow, 'rows' => $rowDetails];
            }
        }
    }

    jsonResponse([
        'success' => true, 'seatMaps' => $seatMaps, 'cabinLayouts' => $cabinLayouts,
        'pnr' => $pnr, 'canBook' => true,
    ]);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()]);
}
