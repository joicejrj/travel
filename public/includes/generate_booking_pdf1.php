<?php

/* ---------------------------------
   TCPDF Setup
---------------------------------- */
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Generate Booking PDF (Flight / Tour)
 * @param mysqli $mysqli
 * @param int $booking_id
 * @return string|false  Full PDF path or false on failure
 */
function generateBookingPDF(mysqli $mysqli, int $booking_id)
{
    /* ---------------------------------
       1. Fetch booking
    ---------------------------------- */
    $q = $mysqli->prepare("SELECT * FROM bookings WHERE id=? LIMIT 1");
    $q->bind_param("i", $booking_id);
    $q->execute();
    $booking = $q->get_result()->fetch_assoc();
    $q->close();

    if (!$booking) {
        return false;
    }

    $scenario_id = (int)$booking['type_id'];

    /* ---------------------------------
       2. Scenario-specific data
    ---------------------------------- */
    if ($scenario_id === 1) {
        // Flight
        $q = $mysqli->prepare("SELECT * FROM bookings_flights WHERE booking_id=? LIMIT 1");
    } else {
        // Tour
        $q = $mysqli->prepare("SELECT * FROM bookings_tours WHERE booking_id=? LIMIT 1");
    }

    $q->bind_param("i", $booking_id);
    $q->execute();
    $details = $q->get_result()->fetch_assoc();
    $q->close();

    if (!$details) {
        return false;
    }

    /* ---------------------------------
       3. Prepare PDF
    ---------------------------------- */
    $dir = __DIR__ . '/../../uploads/bookings/';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $file = $dir . 'booking_' . $booking_id .'_'.date("Ymdhis"). '.pdf';

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetCreator('Booking System');
    $pdf->SetAuthor('JRJ Travel');
    $pdf->SetTitle('Booking Confirmation');
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();

    /* ---------------------------------
       4. Header (SOA-style)
    ---------------------------------- */
    $html = '
    <style>
      body { font-size: 10.5pt; }
      h2 { font-size: 14pt; margin-bottom: 5px; }
      table { border-collapse: collapse; width: 100%; }
      td { padding: 6px; }
      .label { color: #555; width: 35%; }
      .value { font-weight: bold; }
      .box { border: 1px solid #ddd; }
      .section { margin-top: 15px; }
    </style>

    <h2>Booking Confirmation</h2>
    <table class="box">
      <tr>
        <td class="label">Booking ID</td>
        <td class="value">#' . $booking_id . '</td>
      </tr>
      <tr>
        <td class="label">Customer</td>
        <td class="value">' . htmlspecialchars($booking['contact_name']) . '</td>
      </tr>
      <tr>
        <td class="label">Email</td>
        <td class="value">' . htmlspecialchars($booking['contact_email']) . '</td>
      </tr>
      <tr>
        <td class="label">Phone</td>
        <td class="value">' . htmlspecialchars($booking['contact_phone']) . '</td>
      </tr>
      <tr>
        <td class="label">Status</td>
        <td class="value">' . htmlspecialchars($booking['status']) . '</td>
      </tr>
    </table>
    ';

    /* ---------------------------------
       5. Flight / Tour section
    ---------------------------------- */
    if ($scenario_id === 1) {
        // FLIGHT
        $html .= '
        <div class="section">
          <h3>Flight Details</h3>
          <table class="box">
            <tr><td class="label">PNR</td><td class="value">' . $details['pnr'] . '</td></tr>
            <tr><td class="label">Route</td><td class="value">' . $details['origin'] . ' → ' . $details['destination'] . '</td></tr>
            <tr><td class="label">Departure</td><td class="value">' . $details['departure_date'] . '</td></tr>
            <tr><td class="label">Return</td><td class="value">' . ($details['return_date'] ?: '-') . '</td></tr>
            <tr><td class="label">Trip Type</td><td class="value">' . ucfirst(str_replace('_',' ',$details['trip_type'])) . '</td></tr>
            <tr><td class="label">Passengers</td><td class="value">' . $details['people_no'] . '</td></tr>
            <tr><td class="label">Class</td><td class="value">' . $details['class'] . '</td></tr>
            <tr><td class="label">Amount</td><td class="value">' . $details['currency'] . ' ' . number_format($details['total_amount'], 2) . '</td></tr>
          </table>
        </div>
        ';
    } else {
        // TOUR
        $html .= '
        <div class="section">
          <h3>Tour Package</h3>
          <table class="box">
            <tr><td class="label">Package</td><td class="value">' . htmlspecialchars($details['tour_name']) . '</td></tr>
            <tr><td class="label">Duration</td><td class="value">' . htmlspecialchars($details['tour_duration']) . '</td></tr>
            <tr><td class="label">Travellers</td><td class="value">' . $details['travellers_count'] . '</td></tr>
            <tr><td class="label">Price</td><td class="value">' . number_format($details['tour_price'], 2) . '</td></tr>
          </table>
        </div>
        ';
    }

    $html .= '<br><small>Generated on ' . date('d M Y H:i') . '</small>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($file, 'F');

    return $file;
}
?>