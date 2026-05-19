<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/_auth.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/functions.php';

if (!isset($_GET['id'])) {
    header('location: ./?page=customers_list');
    exit;
}

$customer_id = (int)$_GET['id'];
$as_on_date  = $_GET['date'] ?? date('Y-m-d');

/* ---------------------------------
   Fetch Customer
---------------------------------- */
$customer = $db->get('customers', ['id' => $customer_id], 'company,trn');
if (!$customer) {
    die('Customer not found');
}

/* ---------------------------------
   Fetch SOA Entries
---------------------------------- */
$soa = $db->get(
    'customers_soa',
    [
        '#all' => 1,
        'customer_id' => $customer_id,
        '#cus' => "created_at <= '".$site->esc($as_on_date)." 23:59:59'",
        '#srt' => 'date ASC, id ASC'
    ]
);

/* ---------------------------------
   TCPDF Setup
---------------------------------- */
require_once __DIR__ . '/../vendor/autoload.php';

class MYPDF extends TCPDF {
    public function Header() {}
    public function Footer() {}
}

$pdf = new MYPDF();
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle("Customer SOA - ".$customer->company);
$pdf->SetMargins(20, 12, 20);
$pdf->SetAutoPageBreak(true, 18);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

/* ---------------------------------
   Build HTML
---------------------------------- */
ob_start();
?>

<style type="text/css">
body{
	font-family: sans-serif;
}
.container {
	/*width: fit-content;*/
	text-align: center;
	/*width: 250px;*/
}
.item-table td,.phone, table {
	/*border-bottom: solid;
		border-color: grey;
		border-width: 1px;*/
		/*border-left: 0px;*/
		/*border-right: 0px;*/
/*  				border-bottom: 1px solid grey;*/
/*  				border-style: solid;*/
}
.item-table th {
	font-size: 1.2em;
	font-weight: bold;
}
.order {
	line-height: 20px;
	/*font-weight: bold;*/
	font-size: 1.3em;
	text-align: left;
}
</style>

<div class="container">
<!-- HEADER -->
<table width="100%" cellpadding="0" cellspacing="0" class="order-table">
    <!-- TITLE + DATE -->
    <tr>
        <th style="
            font-weight:bold;
            font-size:1.3em;
            text-align:left;
        ">
            Statement of Account
        </th>
        <th style="
            font-weight:bold;
            font-size:1.3em;
            text-align:right;
        ">
            <!-- Date: <?=date("d-m-Y", strtotime($as_on_date))?> -->
        </th>
    </tr>

    <!-- SPACING -->
    <tr>
        <th colspan="2" style="height:12px;"></th>
    </tr>

    <!-- CUSTOMER NAME + CLIENT CODE -->
    <tr>
        <th style="
            font-weight:normal;
            font-size:1.3em;
            text-align:left;
        ">
            <?=$customer->company?>
        </th>
        <th style="
            font-weight:normal;
            font-size:1.3em;
            text-align:right;
        ">
            <!-- TRN: <?=$customer->trn??'-'?> -->
        </th>
    </tr>

    <!-- SPACING -->
    <tr>
        <th colspan="2" style="height:10px;"></th>
    </tr>

    <hr style="line-height: 3px;border-style: solid;"/>

    <!-- SPACING AFTER LINE -->
    <tr>
        <th colspan="2" style="height:14px;"></th>
    </tr>
</table>


<table width="100%;" cellpadding="0" cellspacing="0" class="item-table">

    <!-- TABLE HEADER -->
    <tr style="border-bottom: 1px solid;border-style: solid; line-height: 30px;">
		<th style="text-align: left; width: 20%;">Date</th>
		<th style="text-align: left; width: 20%;">Type</th>
		<th style="text-align: left; width: 20%;">Invoice</th>
		<th style="text-align: right; width: 20%;">Amount</th>
		<th style="text-align: right; width: 20%;">Balance</th>
	</tr>

<?php
$balance = 0;
$settime = '';

if ($soa && !empty($soa->data)):
foreach ($soa->data as $row):

    if ($row->type === 'Invoice') {
        $balance += $row->amount;
    } else {
        $balance -= $row->amount;
    }

    $settime = date("d/m/Y h:i A", strtotime($row->created_at));
?>
    <tr style="border-bottom:1px solid;border-style:solid;line-height:30px;">
        <td style="text-align:left;width:20%;">
            <?=date('d/m/Y', strtotime($row->date??$date))?>
        </td>
        <td style="text-align:left;width:20%;">
            <?=$row->type?>
        </td>
        <td style="text-align:left;width:20%;">
            #<?=$row->invoiceno?>
        </td>
        <td style="text-align:right;width:20%;">
            <?=$row->type === 'Payment' ? '-' : ''?>
            <?=number_format($row->amount,2,'.','')?>
        </td>
        <td style="text-align:right;width:20%;">
            <?=number_format($balance,2,'.','')?>
        </td>
    </tr>
<?php endforeach; else: ?>
    <tr>
        <td colspan="5" style="text-align:center;line-height:30px;">
            No transactions found
        </td>
    </tr>
<?php endif; ?>

    <!-- SPACING -->
    <tr><th colspan="5" style="height:10px;"></th></tr>

    <hr style="line-height: 3px;border-style: solid;"/>

    <!-- TOTAL -->
    <tr style="border-bottom:1px solid;border-style:solid;line-height:30px;">
        <th colspan="5" style="text-align:center;font-weight:bold;">
            Total Outstanding Balance = <?=$currency_symbol?><?=number_format($balance,2,'.','')?><br>
            <span style="font-size:0.7em;font-weight:normal;">
                This statement is generated on <?=$settime?>
            </span>
        </th>
    </tr>

</table><br>

</div>

<?php
$html = ob_get_clean();

/* ---------------------------------
   Render PDF
---------------------------------- */
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Customer_SOA.pdf', 'I');
