<?php
/**
 * JRJ TravelPort — Ticket Display AJAX Handler
 * GET /api/ticket_display.php?ticket_number=0169904503844&booking_id=14
 * 
 * Calls Travelport Ticket Display API and returns formatted ticket details.
 */
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../_auth.php'; // adjust if your auth path differs
require_once __DIR__ . '/../../config/db.php';
// require_once __DIR__ . '/../../config/functions.php'; // optional
require_once __DIR__ . '/../includes/TravelportAPI.php';

header('Content-Type: application/json');

$ticketNumber = sanitize($_GET['ticket_number'] ?? '');
$bookingId = (int)($_GET['booking_id'] ?? 0);

if (!$ticketNumber) {
    jsonResponse(['error' => 'Missing ticket number.'], 400);
}

try {
    $api = new TravelportAPI();
    $result = $api->getTicketDisplay($ticketNumber, $bookingId ?: null);
    $data = $result['data'] ?? [];

    // Parse TicketListResponse
    $response = $data['TicketListResponse'] ?? $data;
    $tickets = $response['TicketID'] ?? [];
    if (isset($tickets['@type'])) $tickets = [$tickets];

    if (empty($tickets)) {
        // Check for errors
        $errors = $response['Result']['Error'] ?? [];
        if (isset($errors['@type'])) $errors = [$errors];
        $errMsg = !empty($errors) ? ($errors[0]['Message'] ?? 'Unknown error') : 'No ticket data returned.';
        jsonResponse(['error' => $errMsg]);
    }

    $parsed = [];
    foreach ($tickets as $tkt) {
        $ticket = [
            'ticketNumber'   => $tkt['Identifier']['value'] ?? $ticketNumber,
            'passengerName'  => trim(($tkt['PersonName']['Given'] ?? '') . ' ' . ($tkt['PersonName']['Surname'] ?? '')),
            'pnr'            => $tkt['ReservationLocator']['value'] ?? '',
            'pnrSource'      => $tkt['ReservationLocator']['supplierCode'] ?? '',
            'passengerType'  => $tkt['PassengerTypeCode'] ?? 'ADT',
            'validatingCarrier' => $tkt['ValidatingCarrier'] ?? '',
            'pricingType'    => $tkt['PricingType'] ?? '',
            'ticketsIssued'  => $tkt['numberOfTicketsIssued'] ?? 1,
            'restrictions'   => $tkt['Restrictions'] ?? [],
        ];

        // Form of Payment
        $fops = $tkt['FormOfPayment'] ?? [];
        if (isset($fops['value'])) $fops = [$fops];
        $fopDetails = [];
        foreach ($fops as $fop) {
            if (isset($fop['PaymentCard'])) {
                $card = $fop['PaymentCard'];
                $fopDetails[] = [
                    'type' => 'Card',
                    'cardCode' => $card['CardCode'] ?? '',
                    'cardNumber' => $card['CardNumber']['encryptedValue'] ?? $card['CardNumber']['PlainText'] ?? 'XXXX',
                    'expiry' => $card['expireDate'] ?? '',
                    'approvalCode' => $card['approvalCode'] ?? '',
                ];
            } else {
                $fopDetails[] = ['type' => $fop['value'] ?? 'Unknown'];
            }
        }
        $ticket['formOfPayment'] = $fopDetails;

        // Segments
        $segments = [];
        $tktSegments = $tkt['TicketSegment'] ?? [];
        if (isset($tktSegments['@type'])) $tktSegments = [$tktSegments];
        foreach ($tktSegments as $seg) {
            $baggage = $seg['TicketBaggage'] ?? [];
            $baggageText = '';
            if (isset($baggage['soldByPieceInd']) && $baggage['soldByPieceInd']) {
                $baggageText = ($baggage['quantity'] ?? 0) . ' PC';
            } elseif (isset($baggage['soldByWeightInd']) && $baggage['soldByWeightInd']) {
                $m = $baggage['Measurement'][0] ?? [];
                $baggageText = ($m['value'] ?? '') . ' ' . ($m['unit'] ?? '');
            } else {
                $baggageText = ($baggage['quantity'] ?? 0) . ' PC';
            }

            $segments[] = [
                'sequence'       => $seg['sequence'] ?? 0,
                'carrier'        => $seg['Carrier'] ?? '',
                'flightNumber'   => $seg['Number'] ?? '',
                'classOfService' => $seg['ClassOfService'] ?? '',
                'fareBasisCode'  => $seg['FareBasisCode'] ?? '',
                'status'         => $seg['Status'] ?? '',
                'connection'     => $seg['connectionInd'] ?? false,
                'from'           => $seg['Departure']['location'] ?? '',
                'fromDate'       => $seg['Departure']['date'] ?? '',
                'fromTime'       => $seg['Departure']['time'] ?? '',
                'to'             => $seg['Arrival']['location'] ?? '',
                'toDate'         => $seg['Arrival']['date'] ?? '',
                'toTime'         => $seg['Arrival']['time'] ?? '',
                'baggage'        => $baggageText,
            ];
        }
        $ticket['segments'] = $segments;

        // Price
        $price = $tkt['TicketPrice'] ?? [];
        $taxes = [];
        $taxList = $price['Taxes']['Tax'] ?? [];
        if (isset($taxList['taxCode'])) $taxList = [$taxList];
        foreach ($taxList as $tax) {
            $taxes[] = [
                'code' => $tax['taxCode'] ?? '',
                'amount' => $tax['value'] ?? 0,
                'currency' => $tax['currencyCode'] ?? '',
            ];
        }
        $ticket['price'] = [
            'currency'       => $price['CurrencyCode']['value'] ?? '',
            'base'           => $price['Base'] ?? 0,
            'total'          => $price['Total'] ?? 0,
            'taxes'          => $taxes,
            'fareCalculation' => $price['fareCalculation'] ?? '',
            'fareBreakdown'  => $price['fareBreakdown'] ?? '',
        ];
        if (isset($price['FiledAmount'])) {
            $ticket['price']['filedAmount'] = $price['FiledAmount']['value'] ?? '';
            $ticket['price']['filedCurrency'] = $price['FiledAmount']['currencyCode'] ?? '';
        }

        // Agency Info
        $agency = $tkt['AgencyInfo'] ?? [];
        $ticket['agency'] = [
            'name'       => $agency['name'] ?? '',
            'code'       => $agency['code'] ?? '',
            'pcc'        => $agency['ticketingPCC'] ?? '',
            'city'       => $agency['ticketingCity'] ?? '',
            'country'    => $agency['ticketingCountry'] ?? '',
            'ticketDate' => $agency['ticketedDate'] ?? '',
        ];

        // Previous Issue (exchanges)
        if (isset($tkt['PreviousIssue'])) {
            $prev = $tkt['PreviousIssue'];
            if (isset($prev['value'])) $prev = [$prev];
            $ticket['previousIssue'] = $prev;
        }

        // Flight Products (class/fare per segment)
        $flightProducts = $tkt['FlightProduct'] ?? [];
        if (isset($flightProducts['segmentSequence'])) $flightProducts = [$flightProducts];
        $ticket['flightProducts'] = $flightProducts;

        $parsed[] = $ticket;
    }

    jsonResponse(['success' => true, 'tickets' => $parsed]);

} catch (Exception $e) {
    jsonResponse(['error' => $e->getMessage()]);
}
