<?php

require_once __DIR__ . '/../../vendor/autoload.php';

function generateBookingPDF(mysqli $mysqli, int $booking_id)
{
    /* ===============================
       FETCH BOOKING
    =============================== */
    $q = $mysqli->prepare("SELECT * FROM bookings WHERE id=? LIMIT 1");
    $q->bind_param("i", $booking_id);
    $q->execute();
    $booking = $q->get_result()->fetch_assoc();
    $q->close();
    if (!$booking) return false;

    $scenario_id = (int)$booking['type_id'];

    /* ===============================
       FETCH SCENARIO DATA
    =============================== */
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

    /* ===============================
       PREPARE PDF
    =============================== */
    $dir = __DIR__ . '/../../uploads/bookings/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $file = $dir . 'booking_' . $booking_id .'_'.date("Ymdhis"). '.pdf';

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(10, 10, 10);
    $pdf->SetAutoPageBreak(true, 10);
    $pdf->AddPage();

    /* ===============================
       EXTRACT FLIGHT SEGMENTS
    =============================== */
    $segments = [];
    if ($scenario_id === 1 && !empty($orderData['flightOffers'][0]['itineraries'])) {
        foreach ($orderData['flightOffers'][0]['itineraries'] as $itin) {
            foreach ($itin['segments'] as $seg) {
                $segments[] = $seg;
            }
        }
    }

    /* ===============================
       STYLES
    =============================== */
    $html = '
    <style>
      body { font-size:9.5pt; color:#111; }
      h1 { font-size:15pt; margin:0; }
      h2 {
        font-size:11.5pt;
        margin:14px 0 6px;
        border-bottom:1px solid #bbb;
        padding-bottom:3px;
      }
      table { width:100%; border-collapse:collapse; }
      td { padding:4px 6px; vertical-align:top; }
      .label { color:#555; width:22%; }
      .value { font-weight:600; }
      .meta td { border-bottom:1px solid #ddd; }
      .right { text-align:right; }

      .segments th {
        text-align:left;
        font-weight:600;
        padding:5px;
        border-bottom:1px solid #999;
      }
      .segments td {
        padding:5px;
        border-bottom:1px solid #eee;
      }

      .amount {
        font-size:11pt;
        font-weight:700;
        border-top:2px solid #000;
        padding-top:6px;
      }

      .muted { color:#777; font-size:8.5pt; }
    </style>
    ';

    /* ===============================
       HEADER
    =============================== */
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
    ';

    /* ===============================
       CUSTOMER & BOOKING INFO
    =============================== */
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
        <td class="label">Scenario</td><td class="value">'.($scenario_id==1?'Flight':'Tour').'</td>
        <td class="label">Date</td><td class="value">'.date('d M Y', strtotime($booking['date'])).'</td>
      </tr>
      <tr>
        <td class="label">Subject</td>
        <td colspan="3">'.$booking['subject'].'</td>
      </tr>
    </table>
    ';

    /* ===============================
       FLIGHT SUMMARY
    =============================== */
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

        /* ===============================
           FLIGHT SEGMENTS
        =============================== */
        if ($segments) {
            $html .= '
            <h2>Flight Segments</h2>
            <table class="segments">
              <tr>
                <th>From</th>
                <th>To</th>
                <th>Departure</th>
                <th>Arrival</th>
                <th>Flight</th>
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

            $html .= '</table>';
        }

        $amount = $details['currency'].' '.number_format($details['total_amount'],2);

    } else {

        /* ===============================
           TOUR SUMMARY
        =============================== */
        $html .= '
        <h2>Tour Package</h2>
        <table class="meta">
          <tr><td class="label">Package</td><td class="value">'.$details['tour_name'].'</td></tr>
          <tr><td class="label">Duration</td><td>'.$details['tour_duration'].'</td></tr>
          <tr><td class="label">Travellers</td><td>'.$details['travellers_count'].'</td></tr>
        </table>
        ';

        $amount = number_format($details['tour_price'],2);
    }

    /* ===============================
       TOTAL AMOUNT
    =============================== */
    $html .= '
    <br>
    <table>
      <tr>
        <td class="amount">Total Amount</td>
        <td class="amount right">'.$amount.'</td>
      </tr>
    </table>
    ';

    /* ===============================
       FOOTER
    =============================== */
    $html .= '
    <br>
    <div class="muted">
      This is a system generated booking confirmation.<br>
      Generated on '.date('d M Y H:i').'
    </div>
    ';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($file, 'F');

    return $file;
}
?>