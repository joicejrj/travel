<?php
/**
 * JRJ TravelPort — Book Seat AJAX Handler
 * POST /api/book_seat.php
 * 
 * Complete workflow in one call:
 * 1. Create post-commit workbench (buildfromlocator)
 * 2. Get seat map from workbench (to get identifiers)
 * 3. Book the selected seat
 * 4. Commit the workbench
 */
// Suppress PHP warnings from appearing in JSON output
// ini_set('display_errors', 0);
// error_reporting(E_ALL);

require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../includes/TravelportAPI.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$pnr          = strtoupper(sanitize($input['pnr'] ?? ''));
$seatNumber   = strtoupper(sanitize($input['seat'] ?? ''));
$bookingId    = (int)($input['booking_id'] ?? 0);
$flightIndex  = (int)($input['flight_index'] ?? 0); // which flight (0-based)
$flightCarrier = sanitize($input['flight_carrier'] ?? '');
$flightNumber  = sanitize($input['flight_number'] ?? '');
$flightFrom    = sanitize($input['flight_from'] ?? '');
$flightTo      = sanitize($input['flight_to'] ?? '');
$flightDate    = sanitize($input['flight_date'] ?? '');

if (!$pnr || !$seatNumber) {
    jsonResponse(['error' => 'Missing PNR or seat number.'], 400);
}

try {
    $api = new TravelportAPI();

    // Step 1: Create post-commit workbench
    $wbResult = $api->createPostCommitWorkbench($pnr, $bookingId ?: null);
    $wbData = $wbResult['data'];

    $workbenchId = $wbData['ReservationResponse']['Identifier']['value']
                ?? $wbData['Identifier']['value'] ?? null;
    if (!$workbenchId) {
        throw new Exception('Could not create workbench for PNR ' . $pnr);
    }

    // Extract offer and traveler identifiers from workbench response
    $reservation = $wbData['ReservationResponse']['Reservation'] ?? [];

    // Offer ID + Identifier
    $offers = $reservation['Offer'] ?? [];
    if (isset($offers['@type'])) $offers = [$offers];
    $offerId = $offers[0]['Identifier']['value'] ?? null;
    $offerShortId = $offers[0]['id'] ?? 'offer_1';
    if (!$offerId) throw new Exception('No offer found in reservation.');

    // Traveler Identifier
    $travelers = $reservation['Traveler'] ?? [];
    if (isset($travelers['@type'])) $travelers = [$travelers];
    $travelerId = $travelers[0]['Identifier']['value'] ?? null;
    $travelerName = ($travelers[0]['PersonName']['Given'] ?? '') . ' ' . ($travelers[0]['PersonName']['Surname'] ?? '');
    if (!$travelerId) throw new Exception('No traveler found in reservation.');

    // Step 2: Get seat map from workbench (to get CatalogOfferingsIdentifier + CatalogOfferingIdentifier)
    $seatMapResult = $api->getSeatMapInWorkbench($workbenchId, $offerId);
    $seatMapData = $seatMapResult['data'];

    $seatMapResponse = $seatMapData['CatalogOfferingsAncillaryListResponse'] ?? $seatMapData;

    // CatalogOfferingsIdentifier = top-level Identifier/value
    $catalogOfferingsId = $seatMapResponse['Identifier']['value'] ?? null;
    if (!$catalogOfferingsId) throw new Exception('Could not get seat map identifiers.');

    // CatalogOfferingIdentifier = CatalogOfferingsID[0]/Identifier/value
    $catalogOfferingsIDs = $seatMapResponse['CatalogOfferingsID'] ?? [];
    if (isset($catalogOfferingsIDs['@type'])) $catalogOfferingsIDs = [$catalogOfferingsIDs];

    // Find which CatalogOfferingIdentifier contains the requested seat
    // Each CatalogOfferingsID corresponds to one flight
    $catalogOfferingId = null;
    $targetCofId = $catalogOfferingsIDs[$flightIndex] ?? $catalogOfferingsIDs[0] ?? null;

    if ($targetCofId) {
        $cofIdentifier = $targetCofId['Identifier']['value'] ?? '';
        $catalogOfferings = $targetCofId['CatalogOffering'] ?? [];
        if (isset($catalogOfferings['@type'])) $catalogOfferings = [$catalogOfferings];

        foreach ($catalogOfferings as $co) {
            $products = $co['ProductOptions'] ?? [];
            if (isset($products['@type'])) $products = [$products];
            foreach ($products as $po) {
                $prodList = $po['Product'] ?? [];
                if (isset($prodList['@type'])) $prodList = [$prodList];
                foreach ($prodList as $prod) {
                    $seatAvails = $prod['SeatAvailability'] ?? [];
                    if (isset($seatAvails['seatAvailabilityStatus'])) $seatAvails = [$seatAvails];
                    foreach ($seatAvails as $sa) {
                        if ($sa['seatAvailabilityStatus'] === 'Available' && in_array($seatNumber, $sa['value'] ?? [])) {
                            $catalogOfferingId = $cofIdentifier;
                            break 4;
                        }
                    }
                }
            }
        }
    }

    if (!$catalogOfferingId) {
        // Seat might not be available anymore - discard workbench and report
        throw new Exception("Seat {$seatNumber} is not available. It may have been taken. Please try another seat.");
    }

    // Step 3: Book the seat
    $bookResult = $api->bookSeat(
        $workbenchId,
        $catalogOfferingsId,
        $catalogOfferingId,
        $travelerId,
        $seatNumber,
        $bookingId ?: null
    );

    $bookData = $bookResult['data'];

    // Check for errors in book response
    $bookErrors = $bookData['Result']['Error'] ?? $bookData['OfferListResponse']['Result']['Error'] ?? [];
    if (!empty($bookErrors)) {
        if (isset($bookErrors['@type'])) $bookErrors = [$bookErrors];
        $errMsg = $bookErrors[0]['Message'] ?? json_encode($bookErrors);
        throw new Exception("Seat book error: {$errMsg}");
    }

    // Extract seat price from book response
    $seatPrice = 0;
    $offerIds = $bookData['OfferListResponse']['OfferID'] ?? $bookData['OfferID'] ?? [];
    if (isset($offerIds['@type'])) $offerIds = [$offerIds];
    if (!empty($offerIds[0]['Price']['TotalPrice'])) {
        $seatPrice = $offerIds[0]['Price']['TotalPrice'];
    }

    // Step 4: Commit the workbench
    $commitResult = $api->commitReservation($workbenchId, $bookingId ?: null);

    // Check commit for errors
    $commitData = $commitResult['data'];
    $commitErrors = $commitData['ReservationResponse']['Result']['Error'] ?? [];
    if (isset($commitErrors['@type'])) $commitErrors = [$commitErrors];
    $commitErrMsg = '';
    foreach ($commitErrors as $ce) {
        $commitErrMsg .= ($ce['Message'] ?? '') . ' ';
    }

    // Save to DB
    if ($bookingId) {
        try {
            $db = getDB();
            $flightSeg = $flightCarrier . $flightNumber;
            $flightDateDb = $flightDate ?: null;
            $stmt = $db->prepare("INSERT INTO seat_assignments (booking_id, flight_segment, flight_from, flight_to, flight_date, seat_number, seat_price, currency, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'GBP', 'confirmed') ON DUPLICATE KEY UPDATE seat_number=VALUES(seat_number), seat_price=VALUES(seat_price), status='confirmed'");
            $stmt->bind_param("isssssd", $bookingId, $flightSeg, $flightFrom, $flightTo, $flightDateDb, $seatNumber, $seatPrice);
            $stmt->execute(); $stmt->close();
        } catch (Exception $dbErr) {
            error_log('Seat save to DB failed: ' . $dbErr->getMessage());
        }
    }

    jsonResponse([
        'success'  => true,
        'seat'     => $seatNumber,
        'traveler' => trim($travelerName),
        'price'    => $seatPrice,
        'pnr'      => $pnr,
        'flight'   => $flightCarrier . $flightNumber,
        'warning'  => trim($commitErrMsg) ?: null,
        'message'  => "Seat {$seatNumber} assigned to " . trim($travelerName) . " on {$flightCarrier}{$flightNumber} ({$flightFrom}→{$flightTo}).",
    ]);

} catch (Exception $e) {
    jsonResponse([
        'error' => $e->getMessage(),
        'debug' => APP_DEBUG ? $e->getTraceAsString() : null,
    ]);
}
