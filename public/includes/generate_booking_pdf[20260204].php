<?php

require_once __DIR__ . '/../../vendor/autoload.php';

function generateBookingPDF(mysqli $mysqli, int $booking_id)
{
    global $currency_symbol;

    /* ================= FETCH BOOKING ================= */
    $q = $mysqli->prepare("SELECT * FROM bookings WHERE id=? LIMIT 1");
    $q->bind_param("i", $booking_id);
    $q->execute();
    $booking = $q->get_result()->fetch_assoc();
    $q->close();
    if (!$booking) return false;

    $scenario_id = (int)$booking['type_id'];

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

    $orderData   = !empty($details['order_json']) ? json_decode($details['order_json'], true) : [];
    $travellers  = !empty($details['travellers']) ? json_decode($details['travellers'], true) : [];

    /* ================= FLIGHT SEGMENTS ================= */
    $segments = [];
    if ($scenario_id === 1 && !empty($orderData['flightOffers'][0]['itineraries'])) {
        foreach ($orderData['flightOffers'][0]['itineraries'] as $itin) {
            foreach ($itin['segments'] as $seg) {
                $segments[] = $seg;
            }
        }
    }

    /* ================= PDF SETUP ================= */
    $dir = __DIR__ . '/../../uploads/bookings/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $file = $dir . 'booking_' . $booking_id . '_' . date("YmdHis") . '.pdf';

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(true, 12);
    $pdf->SetFont('dejavusans', '', 10);
    $pdf->AddPage();

    /* ================= STYLES ================= */
    $html = '
    <style>
      body { font-size:10pt; color:#111; }
      h1 { font-size:17pt; margin:0; }
      h2 { font-size:12pt; margin:14px 0 6px; }
      table { width:100%; border-collapse:collapse; }
      td, th { padding:4px 6px; vertical-align:top; }

      .label { color:#666; width:22%; }
      .value { font-weight:600; }

      .meta td { padding:3px 6px; }

      .head-row th {
        font-weight:700;
        border-bottom:2px solid #000;
        text-align:left;
      }

      .row td {
        border-bottom:1px solid #eee;
      }

      .total-box {
        margin-top:14px;
        border-top:2px solid #000;
        font-size:12pt;
        font-weight:700;
      }

      .right { text-align:right; }
      .muted { color:#777; font-size:9pt; }

      .small { font-size:8.5pt; }
      // .traveller-row td { padding:3px 4px; }
    </style>
    ';

    /* ================= HEADER ================= */
    $html .= '
    <table>
      <tr>
        <td>
          <h1>JRJ Travel</h1>
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

    /* ================= CUSTOMER & BOOKING ================= */
    $html .= '
    <h2>Customer & Booking Details</h2>
    <table class="meta">
      <tr>
        <td class="label">Customer</td><td class="value">'.$booking['contact_name'].'</td>
        <td class="label">Status</td><td class="value">'.$booking['status'].'</td>
      </tr>
      <tr>
        <td class="label">Email</td><td>'.$booking['contact_email'].'</td>
        <td class="label">Phone</td><td>'.$booking['contact_phone'].'</td>
      </tr>
      <tr>
        <td class="label">Scenario</td><td class="value">'.($scenario_id==1?'Flight':'Tour Package').'</td>
        <td class="label">Date</td><td class="value">'.date('d M Y', strtotime($booking['date'])).'</td>
      </tr>
      <tr>
        <td class="label">Subject</td>
        <td colspan="3">'.$booking['subject'].'</td>
      </tr>
    </table>
    ';

    /* ================= FLIGHT ================= */
    if ($scenario_id === 1) {

        $html .= '
        <h2>Flight Summary</h2>
        <table class="meta">
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
        ';

        if ($segments) {
            $html .= '
            <h2>Flight Segments</h2>
            <table>
              <tr class="head-row">
                <th>From</th><th>To</th><th>Departure</th><th>Arrival</th><th>Flight</th>
              </tr>';
            foreach ($segments as $s) {
                $html .= '
                <tr class="row">
                  <td>'.$s['departure']['iataCode'].'</td>
                  <td>'.$s['arrival']['iataCode'].'</td>
                  <td>'.date('d M Y H:i', strtotime($s['departure']['at'])).'</td>
                  <td>'.date('d M Y H:i', strtotime($s['arrival']['at'])).'</td>
                  <td>'.$s['carrierCode'].' '.$s['number'].'</td>
                </tr>';
            }
            $html .= '</table>';
        }

        $amount = $details['currency'].' '.number_format($details['total_amount'],2);
    }

    /* ================= TOUR ================= */
    if ($scenario_id === 2) {

        $html .= '
        <h2>Tour Package Details</h2>
        <table class="meta">
          <tr>
            <td class="label">Package</td><td class="value">'.$details['tour_name'].'</td>
            <td class="label">Travellers</td><td class="value">'.$details['travellers_count'].'</td>
          </tr>
          <tr>
            <td class="label">Duration</td><td class="value">'.$details['tour_duration'].'</td>
            <td class="label">Package ID</td><td class="value">'.$details['package_id'].'</td>
          </tr>
        </table>
        ';

        $amount = $currency_symbol . number_format($details['tour_price'], 2);
    }

    /* ================= TRAVELLERS ================= */
    if ($travellers) {
        $html .= '
        <h2>Travellers Details</h2>
        <table>
          <tr class="head-row">
            <th>#</th>
            <th>Name</th>
            <th>Email</th>
            <th>DOB</th>
            <th>Passport</th>
          </tr>';
        $i = 1;
        foreach ($travellers as $t) {
            $html .= '
            <tr class="row small traveller-row">
              <td>'.$i++.'</td>
              <td>'.htmlspecialchars($t['name']).'</td>
              <td>'.htmlspecialchars($t['email'] ?? '-').'</td>
              <td>'.(!empty($t['dob']) ? date('d M Y', strtotime($t['dob'])) : '-').'</td>
              <td>'.(!empty($t['passport_number']) ? strtoupper($t['passport_number']) : '-').'</td>
            </tr>';
        }
        $html .= '</table>';
    }

    /* ================= TOTAL ================= */
    $html .= '
    <table class="total-box">
      <tr>
        <td>Total Amount</td>
        <td class="right">'.$amount.'</td>
      </tr>
    </table>
    ';

    /* ================= FOOTER ================= */
    $html .= '
    <br>
    <!--div class="muted">
      This is a system generated booking confirmation.<br>
      Generated on '.date('d M Y H:i').'
    </div --!>
    ';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($file, 'F');

    return $file;
}
?>