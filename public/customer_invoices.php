<?php
// customer_invoice.php — Monthly Customer Invoice

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

/* ---------------------------------
   Inputs
---------------------------------- */
$customer_id = $_GET['customer_id'] ?? '';
$month       = $_GET['month'] ?? date('Y-m');

$is_ready = ($customer_id && $month);

// if (!$customer_id || !$month) {
//     echo '<div class="alert alert-warning">Customer and month required.</div>';
//     exit;
// }

$month_start = $month . '-01';
$month_end   = date('Y-m-t', strtotime($month_start));



if (isset($_POST['mark_paid'])) {

    // Fetch invoice
    $invoice = $db->get('customer_invoices', [
        'customer_id' => $customer_id,
        'month'       => $month
    ]);

    if (!$invoice) {
        $site->msg("Invoice not found","danger");
        header("Location: ./?page=customer_invoices");
        exit;
    }

    // Always work with CURRENT balance
    $current_balance = (float)$invoice->balance_amount;

    $payment_amount = (float)$_POST['payment_amount'];
    $payment_type   = $_POST['payment_type'];
    $payment_date   = $_POST['payment_date']!=''?date("Y-m-d",strtotime($_POST['payment_date'])):$date("Y-m-d");

    // FULL PAYMENT → clear balance
    if ($payment_type === 'full') {
        $payment_amount = $current_balance;
        $new_balance    = 0;
        $paid_status    = 1;
        $invoice_partial = 0;
    }
    // PARTIAL PAYMENT
    else {
        // Prevent overpayment
        if ($payment_amount > $current_balance) {
            $payment_amount = $current_balance;
        }

        $new_balance     = $current_balance - $payment_amount;
        $paid_status     = ($new_balance <= 0) ? 1 : 0;
        $invoice_partial = $new_balance;
    }

    /* -----------------------------
       1️⃣ Insert into payments table
    ------------------------------ */
    $db->insert('customers_payments', [
        'customer_id'      => $customer_id,
        'type'             => 'Income',
        'category'         => 'Invoice',
        'invoice_date'     => $payment_date,
        'payment_status'   => $paid_status ? 'Paid' : 'Partial Paid',
        'invoice_amount'   => $payment_amount,
        'invoice_partial'  => $paid_status ?0:$payment_amount,
        'notes'            =>
            $_POST['notes'] .
            " Invoice No: ".$_POST['invoice_no'] .
            ($_POST['payment_ref']!='' ? " Ref: ".$_POST['payment_ref'] : ''),
        'created_at'       => date('Y-m-d H:i:s'),
        'created_by'       => $_SESSION['person_id']
    ]);

    /* -----------------------------
       2️⃣ Insert into SOA
    ------------------------------ */
    $db->insert('customers_soa', [
        'customer_id' => $customer_id,
        'date' => $payment_date,
        'invoiceno'   => $_POST['invoice_no'],
        'type'        => 'Payment',
        'amount'      => $payment_amount,
        'ref_no'      => $_POST['payment_ref'],
        'created_at'  => date('Y-m-d H:i:s')
    ]);

    /* -----------------------------
       3️⃣ Update Invoice
    ------------------------------ */
    $db->update('customer_invoices',
        ['id' => $invoice->id],
        [
            'paid'           => $paid_status,
            'balance_amount' => $new_balance
        ]
    );

    $site->msg(
        $paid_status
            ? "Invoice fully paid"
            : "Partial payment recorded. Balance AED ".number_format($new_balance,2),
        "success"
    );

    header("Location: ./?page=customer_invoices&customer_id=$customer_id&month=$month");
    exit;
}

/* ---------------------------------
   Customer
---------------------------------- */
$customer_name = '';
$customer_trn = '';
if ($is_ready) {
    $cust = $mysqli->query(
        "SELECT company,trn FROM customers WHERE id=".(int)$customer_id
    )->fetch_assoc();
    $customer_name = $cust['company'] ?? '';
    $customer_trn = $cust['trn'] ?? '';
}

/* ---------------------------------
   Fetch Timesheets (BUSINESS RULE)
---------------------------------- */

$timesheets = [];

if ($is_ready) {

    // Prefer ALL-SITES timesheet
    $allSiteTs = $db->get('customer_timesheets', [
        'customer_id' => $customer_id,
        'site_id' => null,
        'month' => $month
    ]);

    if ($allSiteTs) {
        $timesheets[] = $allSiteTs;
    } else {
        $res = $mysqli->query("
            SELECT *
            FROM customer_timesheets
            WHERE customer_id = ".(int)$customer_id."
              AND site_id IS NOT NULL
              AND month = '".$mysqli->real_escape_string($month)."'
        ");
        while ($r = $res->fetch_object()) {
            $timesheets[] = $r;
        }
    }
}

if (empty($timesheets)) {
    // require_once __DIR__ . '/includes/header.php';
    // echo '<div class="alert alert-info">Timesheets not generated for this month.</div>';
    // require_once __DIR__ . '/includes/footer.php';
    // exit;
}

$geti = $db->get('customer_invoices',array(),'max(id) as nino');
$inno = $geti&&$geti->nino!=''?(($geti->nino+1+100)."/".date("Y")):"100/".date("Y");

/* ---------------------------------
   Calculate Invoice Amounts
---------------------------------- */
$subtotal = 0;
$vat      = 0;
$total    = 0;

foreach ($timesheets as $ts) {
    $subtotal += $ts->subtotal;
    $vat      += $ts->vat_amount;
    $total    += $ts->total_amount;
}

/* ---------------------------------
   Existing Invoice?
---------------------------------- */
$invoice = $db->get('customer_invoices', [
    'customer_id' => $customer_id,
    'month' => $month
]);

/* ---------------------------------
   Regenerate Invoice
---------------------------------- */
if (isset($_GET['regenerate']) && $invoice) {

    $db->delete('customer_invoice_timesheets', [
        'invoice_id' => $invoice->id
    ]);

    $db->delete('customers_soa', [
        'customer_id' => $customer_id,
        'invoiceno'   => $invoice->invoice_no,
        'type'        => 'Invoice'
    ]);

    $db->delete('customer_invoices', [
        'id' => $invoice->id
    ]);

    header("Location: ./?page=customer_invoices&customer_id=$customer_id&month=$month");
    exit;
}

/* ---------------------------------
   Generate Invoice
---------------------------------- */
if (!$invoice && isset($_POST['generate_invoice'])) {

    $start_date  = $month . '-01';
    $end_date    = date('Y-m-t', strtotime($start_date));

    $invoice_id = $db->insert('customer_invoices', [
        'customer_id' => $customer_id,
        'month' => $month,
        'invoice_no' => $_POST['invoice_no'],
        'reference_no' => $_POST['reference_no'],
        'invoice_date' => $end_date,
        'subtotal' => $subtotal,
        'vat_amount' => $vat,
        'total_amount' => $total,
        'balance_amount' => $total
    ]);

    $db->insert('customers_soa', [
        'customer_id' => $customer_id,
        'date' => $end_date,
        'invoiceno'   => $_POST['invoice_no'],
        'type'        => 'Invoice',
        'amount'      => $total,
        'ref_no'      => $_POST['reference_no'],
        'created_at'  => date('Y-m-d H:i:s')
    ]);


    foreach ($timesheets as $ts) {
        $db->insert('customer_invoice_timesheets', [
            'invoice_id' => $invoice_id,
            'timesheet_id' => $ts->id
        ]);
    }

    header("Location: ./?page=customer_invoices&customer_id=$customer_id&month=$month");
    exit;
}

/* ---------------------------------
   Reload Invoice
---------------------------------- */
if ($invoice) {
    $invoice = $db->get('customer_invoices', [
        'customer_id' => $customer_id,
        'month' => $month
    ]);
}

/* ---------------------------------
   Helpers
---------------------------------- */
function number_to_words($number) {
    $number = round($number, 2);
    $no = floor($number);
    $point = round(($number - $no) * 100);

    $words = [
        0 => '',1=>'One',2=>'Two',3=>'Three',4=>'Four',5=>'Five',
        6=>'Six',7=>'Seven',8=>'Eight',9=>'Nine',10=>'Ten',
        11=>'Eleven',12=>'Twelve',13=>'Thirteen',14=>'Fourteen',
        15=>'Fifteen',16=>'Sixteen',17=>'Seventeen',18=>'Eighteen',19=>'Nineteen',
        20=>'Twenty',30=>'Thirty',40=>'Forty',50=>'Fifty',60=>'Sixty',
        70=>'Seventy',80=>'Eighty',90=>'Ninety'
    ];

    $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
    $str = [];
    $i = 0;

    while ($no > 0) {
        $divider = ($i == 1) ? 10 : 100;
        $number_part = $no % $divider;
        $no = floor($no / $divider);
        $i += ($divider == 10) ? 1 : 2;

        if ($number_part) {
            if ($number_part < 21) {
                $str[] = $words[$number_part] . ' ' . $digits[count($str)];
            } else {
                $str[] = $words[floor($number_part/10)*10] . ' ' .
                         $words[$number_part%10] . ' ' .
                         $digits[count($str)];
            }
        }
    }

    $result = implode('', array_reverse($str));
    if ($point > 0) {
        $result .= ' and ' . $words[floor($point/10)*10] . ' ' . $words[$point%10] . ' Fils';
    }
    return trim($result);
}

require_once __DIR__ . '/includes/header.php';
?>

<style>
@media print {

  @page {
    size: A4;
    margin: 12mm;
  }

  /* Hide everything */
  body * {
    visibility: hidden;
  }

  /* Show invoice only */
  .print-invoice,
  .print-invoice * {
    visibility: visible;
  }

  .print-invoice {
    position: absolute;
    margin: 12mm;
    top: 0;
    left: 0;
    width: 90%;
    font-family: Arial, Helvetica, sans-serif;
    font-size: 11px;
    color: #000;
  }

  /* ---------- HEADER ---------- */
  .inv-header {
    text-align: center;
    margin-bottom: 10px;
  }

  .inv-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: bold;
  }

  .inv-header small {
    font-size: 10px;
  }

  /* ---------- META TABLE ---------- */
  .meta-table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 8px;
  }

  .meta-table td {
    padding: 4px;
    font-size: 10px;
    vertical-align: top;
  }

  /* ---------- INVOICE TABLE ---------- */
  .inv-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 8px;
    font-size: 10px;
  }

  .inv-table th,
  .inv-table td {
    border: 1px solid #000;
    padding: 5px;
  }

  .inv-table th {
    background: #f2f2f2;
    text-align: center;
    font-weight: bold;
  }

  /* ---------- TEXT HELPERS ---------- */
  .text-right { text-align: right; }
  .text-center { text-align: center; }

  /* ---------- FOOTER ---------- */
  .inv-footer {
    margin-top: 8px;
    font-size: 9px;
  }

  .signature {
    margin-top: 35px;
    font-size: 10px;
  }

}
.print-invoice {
  visibility: hidden;
}
</style>

<!-- ================= INVOICE UI ================= -->
<div class="container-fluid mt-3 mb-5">

<h4 class="fw-bold mb-3">Customer Invoice</h4>

<form method="get" class="row g-2 mb-4">
    <div class="col-md-4">
        <input type="hidden" name="page" value="customer_invoices">
        <select name="customer_id" class="form-control form-control-sm" required>
            <option value="">Select Customer</option>
            <?php
            $res = $mysqli->query("SELECT id, company FROM customers ORDER BY company");
            while ($c = $res->fetch_assoc()):
            ?>
                <option value="<?=$c['id']?>" <?=$customer_id==$c['id']?'selected':''?>>
                    <?=$c['company']?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="col-md-3">
        <input type="month"
               name="month"
               class="form-control form-control-sm"
               value="<?=$month?>">
    </div>

    <div class="col-md-3">
        <button class="btn btn-primary btn-sm">
            Load Invoice
        </button>
    </div>
</form>

<?php
    $site->show_msg();
?>

<?php if ($is_ready && empty($timesheets)): ?>
    <div class="alert alert-info">
        No timesheets generated for this customer in the selected month.
    </div>
<?php endif; ?>

<?php if ($is_ready): ?>
<?php if ($is_ready && !$invoice && !empty($timesheets)): ?>

<form method="post" class="row g-2 mb-3">
    <div class="col-md-3">
        <input type="hidden" name="page" value="customer_invoices">
        <input type="text" name="invoice_no"
               class="form-control form-control-sm"
               placeholder="Invoice No" value="<?=$inno??''?>" required>
    </div>
    <div class="col-md-3">
        <input type="text" name="reference_no"
               class="form-control form-control-sm" value="ANGS/<?=$inno?>"
               placeholder="Reference No">
    </div>
    <div class="col-md-3">
        <button name="generate_invoice" class="btn btn-primary btn-sm">
            Generate Invoice
        </button>
    </div>
</form>

<?php endif; ?>


<?php if ($invoice): ?>

<div class="border p-3 mb-3 bg-light">
    <div class="row">
        <div class="col-md-8 fw-bold">
            <?=$customer_name?>
        </div>
        <div class="col-md-4 text-end">
            <div><strong>Invoice No:</strong> <?=$invoice->invoice_no?></div>
            <div><strong>Date:</strong> <?=date('d-M-Y', strtotime($invoice->invoice_date))?></div>
            <div><strong>Month:</strong> <?=date('M-Y', strtotime($month_start))?></div>
        </div>
    </div>
</div>

<table class="table table-bordered table-sm">
<tr>
    <td>Professional Service Charges – <?=date('M Y', strtotime($month_start))?></td>
    <td class="text-end"><?=number_format($invoice->subtotal,2)?></td>
</tr>
<tr>
    <td>VAT 5%</td>
    <td class="text-end"><?=number_format($invoice->vat_amount,2)?></td>
</tr>
<tr class="fw-bold table-secondary">
    <td>Total Amount</td>
    <td class="text-end"><?=number_format($invoice->total_amount,2)?></td>
</tr>
</table>

<div class="mt-2 fw-bold">
    (AED: <?=strtoupper(number_to_words($invoice->total_amount))?> ONLY)
</div>

<div class="text-end mt-3">
    <a href="?page=customer_invoices&customer_id=<?=$customer_id?>&month=<?=$month?>&regenerate=1"
       class="btn btn-warning btn-sm">
        Regenerate Invoice
    </a>

    <?php if ($invoice->balance_amount > 0): ?>
    <button class="btn btn-success btn-sm"
            data-bs-toggle="modal"
            data-bs-target="#markPaidModal">
      Mark as Paid
    </button>
    <?php else: ?>
    <!-- <span class="badge bg-success">Paid</span> -->
    <?php endif; ?>


    <button onclick="window.print()"
            class="btn btn-outline-secondary btn-sm">
        Print / Save PDF
    </button>
</div>

<?php endif; ?>

<div class="modal fade" id="markPaidModal" tabindex="-1">
  <div class="modal-dialog">
    <form method="post">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Mark Payment</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">

          <input type="hidden" name="invoice_id" value="<?=$invoice->id?>">
          <input type="hidden" name="invoice_no" value="<?=$invoice->invoice_no?>">

            <div class="mb-2">
                <label class="form-label">Payment Date</label>
                <input type="date"
                     name="payment_date"
                     class="form-control form-control-sm"
                     value="<?=date('Y-m-d')?>"
                     required>
            </div>

            <div class="mb-3">
              <label class="form-label d-block">Payment Type</label>

              <div class="btn-group w-100" role="group">
                <input type="radio" class="btn-check" name="payment_type" id="pay_full"
                       value="full" checked>
                <label class="btn btn-outline-success btn-sm" for="pay_full">
                  Full Payment
                </label>

                <input type="radio" class="btn-check" name="payment_type" id="pay_partial"
                       value="partial">
                <label class="btn btn-outline-warning btn-sm" for="pay_partial">
                  Partial Payment
                </label>
              </div>
            </div>

          <div class="mb-2">
            <label class="form-label">Payment Amount</label>
            <input type="number"
               step="0.01"
               name="payment_amount"
               class="form-control form-control-sm"
               value="<?=$invoice->balance_amount?>"
               data-balance="<?=$invoice->balance_amount?>" readonly
               required>
          </div>

          <div class="mb-2">
            <label class="form-label">Payment Reference</label>
            <input type="text"
                   name="payment_ref"
                   class="form-control form-control-sm"
                   placeholder="Reference Number">
          </div>

          <div class="mb-2">
            <label class="form-label">Notes</label>
            <input type="text"
                   name="notes"
                   class="form-control form-control-sm"
                   placeholder="Payment Notes" value="Timesheet Invoice Payment Received">
          </div>

        </div>

        <div class="modal-footer">
          <button type="submit"
                  name="mark_paid"
                  class="btn btn-success btn-sm">
            Save Payment
          </button>
        </div>

      </div>
    </form>
  </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {

  const fullBtn    = document.getElementById('pay_full');
  const partialBtn = document.getElementById('pay_partial');
  const amtInput   = document.querySelector('[name="payment_amount"]');

  if (!amtInput) return;

  function getBalance() {
    return parseFloat(amtInput.dataset.balance || 0);
  }

  fullBtn.addEventListener('change', () => {
    amtInput.value = getBalance().toFixed(2); // ✅ ALWAYS balance
    amtInput.readOnly = true;
  });

  partialBtn.addEventListener('change', () => {
    amtInput.readOnly = false;
    amtInput.focus();
  });

});
</script>

<?php if ($invoice): ?>
<div class="print-invoice">

  <!-- HEADER -->
  <div class="inv-header">
    <h2>AL NASR GENERAL SERVICES EST.</h2>
    <small>Recruitment & Outsourcing Supply</small><br>
    <strong>TAX INVOICE</strong>
  </div>

  <!-- TO + META -->
  <table class="meta-table">
    <tr>
      <td width="55%">
        <strong>To:</strong><br>
        <?=$customer_name?><br><br>

        <strong>TRN: </strong><?=$customer_trn?><br><br>

        <strong>Scope:</strong> SUPPLY OF MANPOWER
      </td>
      <td width="45%">
        <table width="100%">
          <tr><td><strong>Invoice No</strong></td><td><?=$invoice->invoice_no?></td></tr>
          <tr><td><strong>Ref</strong></td><td><?=$invoice->reference_no?></td></tr>
          <tr><td><strong>Date</strong></td><td><?=date('d-M-Y', strtotime($invoice->invoice_date))?></td></tr>
          <tr><td><strong>TRN</strong></td><td>100294137300003</td></tr>
        </table>
      </td>
    </tr>
  </table>

  <!-- ITEMS -->
  <table class="inv-table">
    <thead>
      <tr>
        <th width="5%">Sl</th>
        <th>Description</th>
        <th width="15%">Taxable Amount</th>
        <th width="8%">VAT %</th>
        <th width="15%">VAT Amount</th>
        <th width="18%">Total Amount Incl. VAT</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td class="text-center">1</td>
        <td>
          Professional Service Charges<br>
          <?=date('M - Y', strtotime($month_start))?>
        </td>
        <td class="text-right"><?=number_format($invoice->subtotal,2)?></td>
        <td class="text-center">5%</td>
        <td class="text-right"><?=number_format($invoice->vat_amount,2)?></td>
        <td class="text-right"><?=number_format($invoice->total_amount,2)?></td>
      </tr>
    </tbody>
  </table>

    <!-- TOTAL SUMMARY -->
    <table class="inv-table" style="margin-top:8px;">
      <tr>
        <td colspan="4" class="text-right"><strong>Taxable Amount</strong></td>
        <td colspan="2" class="text-right">
          <?=number_format($invoice->subtotal,2)?>
        </td>
      </tr>
      <tr>
        <td colspan="4" class="text-right"><strong>VAT Amount (5%)</strong></td>
        <td colspan="2" class="text-right">
          <?=number_format($invoice->vat_amount,2)?>
        </td>
      </tr>
      <tr>
        <td colspan="4" class="text-right"><strong>Total Amount (Incl. VAT)</strong></td>
        <td colspan="2" class="text-right">
          <?=number_format($invoice->total_amount,2)?>
        </td>
      </tr>
    </table>

    <!-- AMOUNT IN WORDS -->
    <div class="inv-footer">
      <strong>Total Amount Chargeable (in words)</strong><br>
      UAE Dirhams <?=number_to_words($invoice->total_amount)?> Only
    </div>

  <!-- BANK -->
  <div class="inv-footer">
    <strong>Bank Details</strong><br>
    Account Name: AL NASR GENERAL SERVICES EST.<br>
    Bank: ADCB – Abu Dhabi Main<br>
    Account No: 1140428820001<br>
    IBAN: AE660030011404128820001
  </div>

  <!-- SIGN -->
  <div class="signature">
    <strong>AL NASR GENERAL SERVICES EST.</strong><br><br>
    ___________________________<br>
    Authorized Signatory
  </div>

</div>
<?php endif; ?>

</div>

<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>