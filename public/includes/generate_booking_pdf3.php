<?php

/* ---------------------------------
   TCPDF Setup
---------------------------------- */
require_once __DIR__ . '/../../vendor/autoload.php';

/**
 * Generate Booking PDF (Flight / Tour)
 * @param mysqli $mysqli
 * @param int $booking_id
 * @return string|false
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

    if (!$booking) return false;

    $scenario_id = (int)$booking['type_id'];

    /* ---------------------------------
       2. Fetch scenario data
    ---------------------------------- */
    if ($scenario_id === 1) {
        $q = $mysqli->prepare("SELECT * FROM bookings_flights WHERE booking_id=? LIMIT 1");
    } else {
        $q = $mysqli->prepare("SELECT * FROM bookings_tours WHERE booking_id=? LIMIT 1");
    }

    $q->bind_param("i", $booking_id);
    $q->execute();
    $details = $q->get_result()->fetch_assoc();
    $q->close();

    if (!$details) return false;

    /* ---------------------------------
       3. Decode JSON
    ---------------------------------- */
    $orderData = !empty($details['order_json'])
        ? json_decode($details['order_json'], true)
        : [];

    $travellersData = !empty($details['travellers'])
        ? json_decode($details['travellers'], true)
        : [];

    /* ---------------------------------
       4. PDF Setup
    ---------------------------------- */
    $dir = __DIR__ . '/../../uploads/bookings/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $file = $dir . 'booking_' . $booking_id . '_' . date('YmdHis') . '.pdf';

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();

    /* ---------------------------------
       5. Styles
    ---------------------------------- */
    $html = '
    <style>
      body { font-size: 10.5pt; color:#222; }
      h2 { font-size: 16pt; margin:0; }
      h3 { font-size: 12pt; margin-bottom:6px; }
      table { border-collapse: collapse; width: 100%; }
      td { padding: 6px; vertical-align: top; }
      .label { color: #666; width: 32%; }
      .value { font-weight: bold; }
      .box { border: 1px solid #ddd; }
      .section { margin-top: 14px; }
      .header { border-bottom:2px solid #333; margin-bottom:12px; padding-bottom:6px; }
      .right { text-align:right; }
      .muted { color:#777; }
      .thead { background:#f5f5f5; font-weight:bold; }
    </style>
    ';

    /* ---------------------------------
       6. Header
    ---------------------------------- */
    $html .= '
    <div class="header">
      <table>
        <tr>
          <td>
            <h2>JRJ Travel</h2>
            <div class="muted">Booking Summary</div>
          </td>
          <td class="right">
            <strong>Booking #'.$booking_id.'</strong><br>
            '.date('d M Y').'
          </td>
        </tr>
      </table>
    </div>
    ';

    /* ---------------------------------
       7. Customer Details
    ---------------------------------- */
    $html .= '
    <div class="section">
      <h3>Customer Details</h3>
      <table class="box">
        <tr><td class="label">Name</td><td class="value">'.htmlspecialchars($booking['contact_name']).'</td></tr>
        <tr><td class="label">Email</td><td class="value">'.htmlspecialchars($booking['contact_email']).'</td></tr>
        <tr><td class="label">Phone</td><td class="value">'.htmlspecialchars($booking['contact_phone']).'</td></tr>
        <tr><td class="label">Status</td><td class="value">'.htmlspecialchars($booking['status']).'</td></tr>
      </table>
    </div>
    ';

    /* ---------------------------------
       8. Booking Info
    ---------------------------------- */
    $html .= '
    <div class="section">
      <h3>Booking Information</h3>
      <table class="box">
        <tr><td class="label">Scenario</td><td class="value">'.($scenario_id === 1 ? 'Flight Booking' : 'Tour Package').'</td></tr>
        <tr><td class="label">Date</td><td class="value">'.$booking['date'].' '.$booking['time'].'</td></tr>
        <tr><td class="label">Subject</td><td class="value">'.htmlspecialchars($booking['subject']).'</td></tr>
        <tr><td class="label">Notes</td><td class="value">'.nl2br(htmlspecialchars($booking['notes'])).'</td></tr>
      </table>
    </div>
    ';

    /* ---------------------------------
       9. Scenario Details
    ---------------------------------- */
    if ($scenario_id === 1) {

        /* Flight Summary */
        $html .= '
        <div class="section">
          <h3>Flight Details</h3>
          <table class="box">
            <tr><td class="label">PNR</td><td class="value">'.$details['pnr'].'</td></tr>
            <tr><td class="label">Route</td><td class="value">'.$details['origin'].' → '.$details['destination'].'</td></tr>
            <tr><td class="label">Departure</td><td class="value">'.$details['departure_date'].'</td></tr>
            <tr><td class="label">Return</td><td class="value">'.($details['return_date'] ?: '-').'</td></tr>
            <tr><td class="label">Passengers</td><td class="value">'.$details['people_no'].'</td></tr>
            <tr><td class="label">Class</td><td class="value">'.$details['class'].'</td></tr>
          </table>
        </div>
        ';

        /* Flight Segments */
        $segments = [];
        if (!empty($orderData['flightOffers'][0]['itineraries'])) {
            foreach ($orderData['flightOffers'][0]['itineraries'] as $itin) {
                foreach ($itin['segments'] as $seg) {
                    $segments[] = $seg;
                }
            }
        }

        if ($segments) {
            $html .= '
            <div class="section">
              <h3>Flight Segments</h3>
              <table class="box">
                <tr class="thead">
                  <td>From</td><td>To</td><td>Departure</td><td>Arrival</td><td>Carrier</td><td>Flight</td>
                </tr>';

            foreach ($segments as $seg) {
                $html .= '
                <tr>
                  <td>'.$seg['departure']['iataCode'].'</td>
                  <td>'.$seg['arrival']['iataCode'].'</td>
                  <td>'.date('d M Y H:i', strtotime($seg['departure']['at'])).'</td>
                  <td>'.date('d M Y H:i', strtotime($seg['arrival']['at'])).'</td>
                  <td>'.$seg['carrierCode'].'</td>
                  <td>'.$seg['number'].'</td>
                </tr>';
            }

            $html .= '</table></div>';
        }

        $amount = $details['currency'].' '.number_format($details['total_amount'], 2);

    } else {

        /* Tour Details */
        $html .= '
        <div class="section">
          <h3>Tour Package Details</h3>
          <table class="box">
            <tr><td class="label">Package</td><td class="value">'.htmlspecialchars($details['tour_name']).'</td></tr>
            <tr><td class="label">Duration</td><td class="value">'.htmlspecialchars($details['tour_duration']).'</td></tr>
            <tr><td class="label">Travellers</td><td class="value">'.$details['travellers_count'].'</td></tr>
          </table>
        </div>
        ';

        $amount = number_format($details['tour_price'], 2);
    }

    /* ---------------------------------
       10. Travellers List
    ---------------------------------- */
    if ($travellersData) {
        $html .= '
        <div class="section">
          <h3>Travellers</h3>
          <table class="box">
            <tr class="thead">
              <td>#</td><td>Name</td><td>Email</td><td>Phone</td><td>DOB</td><td>Passport</td>
            </tr>';

        $i = 1;
        foreach ($travellersData as $t) {
            $html .= '
            <tr>
              <td>'.$i++.'</td>
              <td>'.htmlspecialchars($t['name']).'</td>
              <td>'.htmlspecialchars($t['email'] ?? '-').'</td>
              <td>'.htmlspecialchars($t['phone'] ?? '-').'</td>
              <td>'.htmlspecialchars($t['dob'] ?? '-').'</td>
              <td>'.(!empty($t['passport_number']) ? strtoupper($t['passport_number']) : '-').'</td>
            </tr>';
        }

        $html .= '</table></div>';
    }

    /* ---------------------------------
       11. Amount Summary
    ---------------------------------- */
    $html .= '
    <div class="section">
      <h3>Amount Summary</h3>
      <table class="box">
        <tr><td class="label">Total Amount</td><td class="value">'.$amount.'</td></tr>
      </table>
    </div>
    ';

    /* ---------------------------------
       12. Footer
    ---------------------------------- */
    $html .= '
    <br><br>
    <div class="muted" style="font-size:9pt;">
      This is a system generated booking summary.<br>
      Generated on '.date('d M Y H:i').'
    </div>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($file, 'F');

    return $file;
}
?>