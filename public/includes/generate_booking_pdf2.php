<?php

/* ---------------------------------
   TCPDF Setup
---------------------------------- */
require_once __DIR__ . '/../../vendor/autoload.php';

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
       2. Scenario-specific data
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
       3. Prepare PDF
    ---------------------------------- */
    $dir = __DIR__ . '/../../uploads/bookings/';
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $file = $dir . 'booking_' . $booking_id . '_' . date("YmdHis") . '.pdf';

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
    $pdf->SetMargins(12, 12, 12);
    $pdf->SetAutoPageBreak(true, 15);
    $pdf->AddPage();

    /* ---------------------------------
       4. Styling
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
    </style>
    ';

    /* ---------------------------------
       5. Header
    ---------------------------------- */
    $html .= '
    <div class="header">
      <table width="100%">
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
       6. Customer Details
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
       7. Booking Summary
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
       8. Scenario Details
    ---------------------------------- */
    if ($scenario_id === 1) {
        // Flight
        $html .= '
        <div class="section">
          <h3>Flight Details</h3>
          <table class="box">
            <tr><td class="label">PNR</td><td class="value">'.$details['pnr'].'</td></tr>
            <tr><td class="label">Route</td><td class="value">'.$details['origin'].' → '.$details['destination'].'</td></tr>
            <tr><td class="label">Departure</td><td class="value">'.$details['departure_date'].'</td></tr>
            <tr><td class="label">Return</td><td class="value">'.($details['return_date'] ?: '-').'</td></tr>
            <tr><td class="label">Trip Type</td><td class="value">'.ucfirst(str_replace('_',' ',$details['trip_type'])).'</td></tr>
            <tr><td class="label">Passengers</td><td class="value">'.$details['people_no'].'</td></tr>
            <tr><td class="label">Class</td><td class="value">'.$details['class'].'</td></tr>
          </table>
        </div>
        ';

        $amount = $details['currency'].' '.number_format($details['total_amount'],2);

    } else {
        // Tour
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

        $amount = number_format($details['tour_price'],2);
    }

    /* ---------------------------------
       9. Amount Summary
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
       10. Footer
    ---------------------------------- */
    $html .= '
    <br><br>
    <div class="muted" style="font-size:9pt;">
      This is a system generated booking summary.<br>
      Generated on '.date('d M Y H:i').'
    </div>
    ';

    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output($file, 'F');

    return $file;
}
?>