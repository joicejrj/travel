<?php

require_once __DIR__ . '/../../vendor/autoload.php';

function generateBookingPDF(mysqli $mysqli, int $booking_id)
{
    /* ---------------- Fetch booking ---------------- */
    $q = $mysqli->prepare("SELECT * FROM bookings WHERE id=? LIMIT 1");
    $q->bind_param("i", $booking_id);
    $q->execute();
    $booking = $q->get_result()->fetch_assoc();
    $q->close();
    if (!$booking) return false;

    $scenario_id = (int)$booking['type_id'];

    /* ---------------- Fetch scenario ---------------- */
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

    $orderData = !empty($details['order_json']) ? json_decode($details['order_json'], true) : [];
    $travellers = !empty($details['travellers']) ? json_decode($details['travellers'], true) : [];

    /* ---------------- PDF ---------------- */
    $dir = __DIR__ . '/../../uploads/bookings/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $file = $dir . 'booking_' . $booking_id . '_'.date("Ymdhis").'.pdf';

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();

    /* ---------------- Styles ---------------- */
    $html = '
    <style>
      body { font-size:9.5pt; color:#222; }
      h2 { font-size:14pt; margin:0; }
      h3 { font-size:11pt; margin:6px 0 4px; }
      table { width:100%; border-collapse:collapse; }
      td { padding:4px; vertical-align:top; }
      .box { border:1px solid #ccc; }
      .label { width:30%; color:#555; }
      .value { font-weight:bold; }
      .thead td { background:#f1f1f1; font-weight:bold; }
      .right { text-align:right; }
      .muted { color:#777; font-size:8.5pt; }
      .section { margin-top:6px; }
    </style>';

    /* ---------------- Header ---------------- */
    $html .= '
    <table>
      <tr>
        <td>
          <h2>JRJ Travel</h2>
          <div class="muted">Booking Confirmation</div>
        </td>
        <td class="right">
          <strong>Booking #'.$booking_id.'</strong><br>
          '.date('d M Y').'
        </td>
      </tr>
    </table>
    <hr>
    ';

    /* ---------------- Customer ---------------- */
    $html .= '
    <table class="box">
      <tr>
        <td class="label">Customer</td><td class="value">'.htmlspecialchars($booking['contact_name']).'</td>
        <td class="label">Status</td><td class="value">'.htmlspecialchars($booking['status']).'</td>
      </tr>
      <tr>
        <td class="label">Email</td><td>'.htmlspecialchars($booking['contact_email']).'</td>
        <td class="label">Phone</td><td>'.htmlspecialchars($booking['contact_phone']).'</td>
      </tr>
    </table>
    ';

    /* ---------------- Booking Info ---------------- */
    $html .= '
    <div class="section">
    <table class="box">
      <tr>
        <td class="label">Scenario</td><td class="value">'.($scenario_id===1?'Flight':'Tour Package').'</td>
        <td class="label">Date</td><td class="value">'.date('d M Y', strtotime($booking['date'])).'</td>
      </tr>
      <tr>
        <td class="label">Subject</td>
        <td colspan="3">'.htmlspecialchars($booking['subject']).'</td>
      </tr>
    </table>
    </div>
    ';

    /* ---------------- Flight ---------------- */
    if ($scenario_id === 1) {

        $html .= '
        <div class="section">
        <h3>Flight Summary</h3>
        <table class="box">
          <tr>
            <td class="label">PNR</td><td class="value">'.$details['pnr'].'</td>
            <td class="label">Passengers</td><td class="value">'.$details['people_no'].'</td>
          </tr>
          <tr>
            <td class="label">Route</td><td class="value">'.$details['origin'].' → '.$details['destination'].'</td>
            <td class="label">Class</td><td class="value">'.$details['class'].'</td>
          </tr>
          <tr>
            <td class="label">Departure</td><td>'.date('d M Y', strtotime($details['departure_date'])).'</td>
            <td class="label">Return</td><td>'.($details['return_date'] ? date('d M Y', strtotime($details['return_date'])) : '-').'</td>
          </tr>
        </table>
        </div>
        ';

        /* Segments */
        $segments=[];
        foreach ($orderData['flightOffers'][0]['itineraries'] ?? [] as $itin) {
            foreach ($itin['segments'] ?? [] as $seg) $segments[]=$seg;
        }

        if ($segments) {
            $html .= '
            <div class="section">
            <h3>Flight Segments</h3>
            <table class="box">
              <tr class="thead">
                <td>From</td><td>To</td><td>Departure</td><td>Arrival</td><td>Flight</td>
              </tr>';
            foreach ($segments as $s) {
                $html .= '
                <tr>
                  <td>'.$s['departure']['iataCode'].'</td>
                  <td>'.$s['arrival']['iataCode'].'</td>
                  <td>'.date('d M Y H:i', strtotime($s['departure']['at'])).'</td>
                  <td>'.date('d M Y H:i', strtotime($s['arrival']['at'])).'</td>
                  <td>'.$s['carrierCode'].' '.$s['number'].'</td>
                </tr>';
            }
            $html .= '</table></div>';
        }

        $amount = $details['currency'].' '.number_format($details['total_amount'],2);

    } else {

        /* Tour */
        $html .= '
        <div class="section">
        <h3>Tour Package</h3>
        <table class="box">
          <tr><td class="label">Package</td><td class="value">'.htmlspecialchars($details['tour_name']).'</td></tr>
          <tr><td class="label">Duration</td><td>'.$details['tour_duration'].'</td></tr>
          <tr><td class="label">Travellers</td><td>'.$details['travellers_count'].'</td></tr>
        </table>
        </div>
        ';
        $amount = number_format($details['tour_price'],2);
    }

    /* ---------------- Travellers ---------------- */
    if ($travellers) {
        $html .= '
        <div class="section">
        <h3>Travellers</h3>
        <table class="box">
          <tr class="thead">
            <td>#</td><td>Name</td><td>Email</td><td>DOB</td><td>Passport</td>
          </tr>';
        $i=1;
        foreach ($travellers as $t) {
            $html .= '
            <tr>
              <td>'.$i++.'</td>
              <td>'.htmlspecialchars($t['name']).'</td>
              <td>'.htmlspecialchars($t['email']??'-').'</td>
              <td>'.(!empty($t['dob'])?date('d M Y',strtotime($t['dob'])):'-').'</td>
              <td>'.(!empty($t['passport_number'])?$t['passport_number']:'-').'</td>
            </tr>';
        }
        $html .= '</table></div>';
    }

    /* ---------------- Amount ---------------- */
    $html .= '
    <div class="section">
    <table class="box">
      <tr>
        <td class="label">Total Amount</td>
        <td class="value">'.$amount.'</td>
      </tr>
    </table>
    </div>
    ';

    /* ---------------- Footer ---------------- */
    $html .= '
    <br>
    <div class="muted">
      This is a system generated booking confirmation.<br>
      Generated on '.date('d M Y H:i').'
    </div>';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($file, 'F');

    return $file;
}
?>