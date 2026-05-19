<?php
require_once __DIR__."/config/config.php";

$token = $_GET['token'] ?? '';

if(!$token){
    die("Invalid payment link");
}

/* ---------------------------------------------------
   GET BOOKING
--------------------------------------------------- */

$stmt = $mysqli->prepare("
SELECT id,type_id
FROM bookings
WHERE payment_token = ?
LIMIT 1
");

$stmt->bind_param("s",$token);
$stmt->execute();

$res = $stmt->get_result();
$booking = $res->fetch_assoc();

if(!$booking){
    die("Booking not found");
}

$booking_id = (int)$booking['id'];
$type_id    = (int)$booking['type_id'];

$stmt->close();

$stmt = $mysqli->prepare("
SELECT status
FROM bookings
WHERE id = ?
LIMIT 1
");

$stmt->bind_param("i",$booking_id);
$stmt->execute();

$res = $stmt->get_result();
$bookingRow = $res->fetch_assoc();

if($bookingRow && $bookingRow['status'] === 'Payment Success'){
    $alreadyPaid = true;
}else{
    $alreadyPaid = false;
}

/* ---------------------------------------------------
   LOAD PAYMENT DETAILS BASED ON TYPE
--------------------------------------------------- */

if($type_id == 1){

    $stmt = $mysqli->prepare("
    SELECT total_amount AS amount, currency
    FROM bookings_flights
    WHERE booking_id = ?
    LIMIT 1
    ");

}
elseif($type_id == 2){

    $stmt = $mysqli->prepare("
    SELECT final_amount AS amount, currency_symbol AS currency
    FROM bookings_tours
    WHERE booking_id = ?
    LIMIT 1
    ");

}
else{

    die("Invalid booking type");

}

$stmt->bind_param("i",$booking_id);
$stmt->execute();

$res = $stmt->get_result();
$row = $res->fetch_assoc();

if(!$row){
    die("Payment details not found");
}

$amount   = (int) round(((float)$row['amount']) * 100);
$currency = trim($row['currency']);


$flightData = null;
$tourData = null;

if($type_id == 1){

    $stmt = $mysqli->prepare("
    SELECT order_json
    FROM bookings_flights
    WHERE booking_id = ?
    LIMIT 1
    ");

    $stmt->bind_param("i",$booking_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    $flightData = json_decode($row['order_json'],true);

    $offer = $flightData['flightOffers'][0];

    $segments = [];
    foreach($offer['itineraries'] as $itinerary){
        foreach($itinerary['segments'] as $seg){
            $segments[] = $seg;
        }
    }

    $price = $offer['price'];

}

elseif($type_id == 2){

    $stmt = $mysqli->prepare("
    SELECT *
    FROM bookings_tours
    WHERE booking_id = ?
    LIMIT 1
    ");

    $stmt->bind_param("i",$booking_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $tourData = $res->fetch_assoc();
}


// get payment settings
$corporate_card_fee = 10;
$stmt = $mysqli->prepare("SELECT value FROM payment_settings WHERE keyname = 'corporate_card_fee' LIMIT 1");
$stmt->execute();
$res = $stmt->get_result();
if($row = $res->fetch_assoc()){
    $corporate_card_fee = (float)$row['value'];
}

?>

<?php

function renderPayableSection($amountMinor, $currency){

    $amount = number_format($amountMinor / 100, 2);
    $currency = htmlspecialchars($currency);
    global $corporate_card_fee;

echo <<<HTML

<hr class="my-3">

<div class="payable-box">

<div class="payable-label">
Amount to Pay
</div>

<div class="payable-total" id="totalAmount">
{$amount} <span class="currency">{$currency}</span>
</div>

<div class="payable-breakdown" id="amountBreakdown" style="display:none">

<div class="break-row">
<span>Subtotal</span>
<span id="subtotalAmount">
{$amount} {$currency}
</span>
</div>

<div class="break-row text-warning">
<span>Corporate Card Fee ({$corporate_card_fee}%)</span>
<span id="corporateFee"></span>
</div>

</div>

</div>

HTML;

}

function renderPriceSummary($data){
    global $corporate_card_fee;

    $currency = htmlspecialchars($data['currency']);

    $subtotal = number_format($data['subtotal'],2);
    $total    = number_format($data['total'],2);

    $tax      = isset($data['tax']) ? number_format($data['tax'],2) : null;
    $discount = isset($data['discount']) ? number_format($data['discount'],2) : null;
    $charges = isset($data['charges']) ? number_format($data['charges'],2) : null;

    $title = $data['title'];

    echo <<<HTML

    <hr class="my-3">

    <h6 class="fw-semibold mb-3">
    Price Summary
    </h6>

    <div class="price-summary">

    <div class="price-row" id="subtotalRow" style1="display:none">
    <span>{$title}</span>
    <span id="subtotalAmount">{$subtotal} {$currency}</span>
    </div>

    HTML;

    if($charges&&$charges>0){
    echo <<<HTML
    <div class="price-row">
    <span>Charges</span>
    <span>{$charges} {$currency}</span>
    </div>
    HTML;
    }

    if($discount&&$discount>0){
    echo <<<HTML
    <div class="price-row text-success">
    <span>Discount</span>
    <span>-{$discount} {$currency}</span>
    </div>
    HTML;
    }

    if($tax){
    echo <<<HTML
    <div class="price-row">
    <span>Taxes & Fees</span>
    <span>{$tax} {$currency}</span>
    </div>
    HTML;
    }

    echo <<<HTML

    <div class="price-row text-warning" id="corporateFeeRow" style="display:none">
    <span>Corporate Card Fee ({$corporate_card_fee}%)</span>
    <span id="corporateFee"></span>
    </div>

    <hr class="my-2">

    <div class="price-row total-row">
    <span>Total to Pay</span>
    <span id="totalAmount">{$total} {$currency}</span>
    </div>

    </div>

    HTML;

}
?>
<!DOCTYPE html>
<html>
<head>

<meta charset="UTF-8">
<title>Secure Payment</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://checkout-web-components.checkout.com/index.js"></script>

<style>

body{
background:linear-gradient(135deg,#eef2ff,#f8fafc);
font-family:system-ui;
min-height:100vh;
overflow-x:hidden;
}

.payment-wrapper{
max-width:620px;
margin:80px auto;
padding:0 15px;
}

.payment-card{
border-radius:16px;
border:none;
overflow:hidden;
}

.card-header{
padding:30px 20px 10px 20px;
}

.amount-box{
font-size:44px;
font-weight:700;
color:#0d6efd;
line-height:1;
}

.currency{
font-size:18px;
margin-left:4px;
font-weight:500;
color:#6c757d;
}

.secure-note{
font-size:13px;
color:#6c757d;
margin-top:4px;
}

.loader{
padding:30px;
text-align:center;
}

.secure-icons{
font-size:13px;
color:#6c757d;
display:flex;
justify-content:center;
gap:18px;
margin-top:25px;
flex-wrap:wrap;
}

.secure-icons span{
display:flex;
align-items:center;
gap:6px;
}

.paid-banner{
padding:60px 30px;
text-align:center;
}

.paid-icon{
font-size:70px;
color:#198754;
margin-bottom:15px;
}

.error-box{
text-align:center;
padding:40px;
}

@media (max-width:768px){

.payment-wrapper{
margin:40px auto;
}

.amount-box{
font-size:36px;
}

}
</style>
<style>
.amount-section{
background:#f8fafc;
border-radius:12px;
padding:22px;
margin-bottom:20px;
}
.amount-label{
font-size:13px;
color:#6c757d;
letter-spacing:.4px;
margin-bottom:6px;
}
.amount-box{
font-size:44px;
font-weight:700;
color:#0d6efd;
line-height:1;
}
.currency{
font-size:20px;
margin-left:4px;
font-weight:500;
color:#6c757d;
}
.amount-breakdown{
border-top:1px solid #e9ecef;
padding-top:12px;
font-size:14px;
}
.break-row{
display:flex;
justify-content:space-between;
padding:6px 0;
color:#495057;
}
</style>

<style>
.payment-page{
margin-top:70px;
}

.booking-card{
border-radius:16px;
}

.price-row{
display:flex;
justify-content:space-between;
padding:6px 0;
font-size:14px;
}

.segment{
padding:10px;
border:1px solid #eee;
border-radius:8px;
}

.booking-card{
position:sticky;
top:20px;
}
</style>

</head>

<body>

<div class="container payment-page">

<div class="row g-4 mb-4">

<!-- LEFT : PAYMENT -->
<div class="col-lg-6">

    <div class="card shadow payment-card">

    <?php if($alreadyPaid): ?>

    <div class="paid-banner">

    <div class="paid-icon">
    <i class="bi bi-check-circle-fill"></i>
    </div>

    <h3 class="mt-3 text-success">
    This booking is already paid
    </h3>

    <p class="text-muted">
    No further payment is required.
    </p>

    </div>

    <?php else: ?>

    <div class="card-header bg-white text-center border-0">

    <h5 class="fw-semibold mb-1">
    Secure Payment
    </h5>

    <div class="secure-note">
    <i class="bi bi-shield-lock"></i>
    Your payment is protected with SSL encryption
    </div>

    </div>

    <div class="card-body text-center">

    <div id="loader" class="loader">
        <div class="spinner-border text-primary"></div>
        <div class="mt-3 text-muted small">
        Initializing secure payment...
        </div>
    </div>

    <div id="flow-container"></div>

    <div class="secure-icons">
        <span>
        <i class="bi bi-lock-fill"></i>
        SSL Secured
        </span>
        <span>
        <i class="bi bi-credit-card"></i>
        Cards Accepted
        </span>
        <span>
        <i class="bi bi-shield-check"></i>
        PCI Compliant
        </span>
    </div>

    </div>

    <?php endif; ?>

    </div>

</div>

<!-- RIGHT : BOOKING DETAILS -->
<div class="col-lg-6">

    <!-- styles for total section  -->
    <style>
        .payable-box{
        background:#eef4ff;
        border:1px solid #dbe7ff;
        border-radius:12px;
        padding:18px;
        margin-top:10px;
        text-align:center;
        }

        .payable-label{
        font-size:13px;
        color:#6c757d;
        margin-bottom:4px;
        }

        .payable-total{
        font-size:38px;
        font-weight:700;
        color:#0d6efd;
        }

        .payable-breakdown{
        margin-top:10px;
        padding-top:10px;
        border-top:1px solid #dbe7ff;
        }
    </style>

    <div class="card shadow booking-card">

    <div class="card-body" id="bookingDetails">

        <style>
            .price-summary{
            background:#f8fafc;
            border-radius:12px;
            padding:16px;
            border:1px solid #e9ecef;
            }
            .price-row{
            display:flex;
            justify-content:space-between;
            padding:6px 0;
            font-size:14px;
            }
            .total-row{
            font-size:25px;
            font-weight:700;
            color:#0d6efd;
            }

            .route-meta{
            display:flex;
            gap:16px;
            margin-top:6px;
            }

            .route-meta span{
            display:flex;
            align-items:center;
            gap:6px;
            }
        </style>

        <?php if($type_id == 1): 

            $firstSeg = $segments[0];
            $lastSeg  = $segments[count($segments)-1];

            $pnr = $flightData['associatedRecords'][0]['reference'] ?? '';
            $travellerCount = count($offer['travelerPricings']);

            $baggageText = null;
            if(!empty($offer['travelerPricings'][0]['fareDetailsBySegment'][0]['includedCheckedBags'])){

                $bags = $offer['travelerPricings'][0]['fareDetailsBySegment'][0]['includedCheckedBags'];

                // Weight based baggage
                if(!empty($bags['weight'])){
                    $baggageText = $bags['weight']." ".$bags['weightUnit']." / passenger";
                }

                // Piece based baggage
                elseif(!empty($bags['quantity'])){
                    $baggageText = $bags['quantity']." bag".($bags['quantity']>1?'s':'')." / passenger";
                }
            }

            ?>

            <style>
                .route-box{
                background:#f8fafc;
                border-radius:10px;
                padding:12px 14px;
                border:1px solid #e9ecef;
                }

                .route-airports{
                font-size:18px;
                font-weight:600;
                letter-spacing:.5px;
                }

                .route-arrow{
                margin:0 6px;
                color:#6c757d;
                }

                .segment-card{
                border:1px solid #e9ecef;
                border-radius:10px;
                padding:12px;
                background:#fff;
                }

                .segment-airline{
                font-weight:600;
                margin-bottom:8px;
                font-size:14px;
                }

                .segment-route{
                display:flex;
                align-items:center;
                justify-content:space-between;
                gap:8px;
                }

                .segment-arrow{
                color:#6c757d;
                font-size:14px;
                }

                .booking-ref{
                display:flex;
                align-items:center;
                gap:6px;
                }

                .price-box{
                background:#f8fafc;
                border-radius:10px;
                padding:14px;
                border:1px solid #e9ecef;
                }
            </style>

            <h5 class="fw-semibold mb-3">
            Flight Booking <span class="text-primary">#<?= $booking_id ?></span>
            </h5>


            <!-- ROUTE -->
            <div class="route-box mb-3">

            <div class="route-airports">
            <?= htmlspecialchars($firstSeg['departure']['iataCode']) ?>
            <span class="route-arrow">→</span>
            <?= htmlspecialchars($lastSeg['arrival']['iataCode']) ?>
            </div>

            <div class="route-meta small text-muted">

            <span>
            <i class="bi bi-people"></i>
            <?= $travellerCount ?> Passenger<?= $travellerCount>1?'s':'' ?>
            </span>

            <?php if($baggageText): ?>
            <span>
            <i class="bi bi-suitcase-lg"></i>
            <?= htmlspecialchars($baggageText) ?>
            </span>
            <?php endif; ?>

            </div>

            </div>


            <!-- SEGMENTS -->
            <?php foreach($segments as $seg): ?>

            <div class="segment-card mb-3">

            <div class="segment-airline">
            ✈ <?= htmlspecialchars($seg['carrierCode']) ?> <?= htmlspecialchars($seg['number']) ?>
            </div>

            <div class="segment-route small">

            <div>
            <strong><?= htmlspecialchars($seg['departure']['iataCode']) ?></strong>
            <br>
            <span class="text-muted">
            <?= date("d M H:i",strtotime($seg['departure']['at'])) ?>
            </span>
            </div>

            <div class="segment-arrow">
            <i class="bi bi-arrow-right"></i>
            </div>

            <div>
            <strong><?= htmlspecialchars($seg['arrival']['iataCode']) ?></strong>
            <br>
            <span class="text-muted">
            <?= date("d M H:i",strtotime($seg['arrival']['at'])) ?>
            </span>
            </div>

            </div>

            </div>

            <?php endforeach; ?>


            <!-- BOOKING REFERENCE -->
            <div class="booking-ref small text-muted mb-3">
            <i class="bi bi-ticket-perforated"></i>
            Booking Reference: <strong><?= htmlspecialchars($pnr) ?></strong>
            </div>

            <!-- PRICE DETAILS -->
            <?php
                $taxTotal = 0;
                foreach($offer['travelerPricings'] as $tp){
                    foreach($tp['price']['taxes'] as $tax){
                        $taxTotal += (float)$tax['amount'];
                    }
                }
                $subtotal = (float)$price['base'];   // base fare
                $taxes    = $taxTotal;
                $total    = (float)$price['total'];
                renderPriceSummary([
                    "title" => "Base Fare ({$travellerCount} pax)",
                    "subtotal" => $subtotal,
                    "tax" => $taxes,
                    "total" => $total,
                    "currency" => $price['currency']
                ]);
            ?>

            <?php // renderPayableSection($amount, $currency); ?>

        <?php endif; ?>

        <?php if($type_id == 2 && $tourData): ?>

            <style>
                .tour-meta .meta-row{
                display:flex;
                align-items:center;
                gap:8px;
                margin-bottom:6px;
                }

                .tour-passengers{
                display:flex;
                align-items:center;
                gap:6px;
                color:#495057;
                }

                .price-box{
                background:#f8fafc;
                border-radius:10px;
                padding:14px;
                }

                .price-row{
                display:flex;
                justify-content:space-between;
                align-items:center;
                padding:6px 0;
                font-size:14px;
                }

                .price-row span:last-child{
                font-weight:500;
                }

                .price-box{
                background:#f8fafc;
                border-radius:10px;
                padding:16px;
                border:1px solid #e9ecef;
                }
            </style>

            <h5 class="fw-semibold mb-3">
            Tour Package Booking <span class="text-primary">#<?= $booking_id ?></span>
            </h5>

            <!-- PACKAGE NAME -->
            <div class="mb-3 fs-6 fw-semibold">
            <?= htmlspecialchars($tourData['tour_name'] ?? 'Tour Package') ?>
            </div>


            <!-- DATE + DURATION -->
            <div class="tour-meta small text-muted mb-3">

            <?php if(!empty($tourData['travel_date'])): ?>
            <div class="meta-row">
            <i class="bi bi-calendar-event"></i>
            <span><?= date("d M Y", strtotime($tourData['travel_date'])) ?></span>
            </div>
            <?php endif; ?>

            <?php if(!empty($tourData['tour_duration'])): ?>
            <div class="meta-row">
            <i class="bi bi-clock"></i>
            <span><?= htmlspecialchars($tourData['tour_duration']) ?></span>
            </div>
            <?php endif; ?>

            </div>


            <!-- PASSENGERS -->
            <?php if(!empty($tourData['adults']) || !empty($tourData['children'])): ?>

            <div class="tour-passengers small mb-3">

            <i class="bi bi-people"></i>

            <?php if(!empty($tourData['adults'])): ?>
            <span><?= (int)$tourData['adults'] ?> Adults</span>
            <?php endif; ?>

            <?php if(!empty($tourData['children'])): ?>
            <span class="ms-2"><?= (int)$tourData['children'] ?> Children</span>
            <?php endif; ?>

            </div>

            <?php endif; ?>


            <!-- PRICE DETAILS -->
            <?php
                $subtotal = (float)$tourData['tour_price'];
                $discount = (float)$tourData['discount_amount'];
                $total    = (float)$tourData['final_amount'];
                $charges    = (float)$tourData['final_amount']+$discount-$subtotal;

                renderPriceSummary([
                    "title" => "Package Price",
                    "subtotal" => $subtotal,
                    "discount" => $discount,
                    "charges" => $charges,
                    "total" => $total,
                    "currency" => $tourData['currency_symbol']
                ]);
            ?>

            <?php // renderPayableSection($amount, $currency); ?>

        <?php endif; ?>

    </div>

    </div>

</div>

</div>
</div>

<script>

let sessionId = "";
// let baseAmount = <?= $amount ?>;

// let baseAmountMinor = <?= $amount ?>; // base amount in minor units
// let currentAmountMinor = baseAmountMinor;
let cur = "<?= $currency ?>";
let isCorporateCard = false;
let checkout = null;

let subtotalAmount = <?= $subtotal ?>;
let taxAmount = <?= isset($taxes) ? $taxes : 0 ?>;
let chargesAmount = <?= isset($charges) ? $charges : 0 ?>;
let discountAmount = <?= isset($discount) ? $discount : 0 ?>;
let baseAmount = subtotalAmount + taxAmount + chargesAmount - discountAmount;
let baseAmountMinor = Math.round(baseAmount * 100);
let currentAmountMinor = baseAmountMinor;

var card_fee = <?=$corporate_card_fee>0?$corporate_card_fee:10?>;

const currSymbols = {
    GBP: "£",
    USD: "$",
    EUR: "€",
    INR: "₹",
    AED: "د.إ"
};

<?php if(!$alreadyPaid): ?>

$(document).ready(function(){
createSession();
});

function createSession(){

$.post("api/create-session.php",{
token:"<?= $token ?>"
},function(res){

if(!res.success){

$("#loader").html(
'<div class="text-danger"><i class="bi bi-x-circle"></i> '+res.message+'</div>'
);

return;
}

const ps = res.paymentSession;

// If payment already exists (pay_xxx)
if(ps.id && ps.id.startsWith("pay_")){
    window.location =
    "result.php?cko-payment-id="
    +ps.id
    +"&ref="+res.reference;

    return;
}

$("#payment_alert").remove();

// Normal payment session
sessionId = ps.id;
mountFlow(ps,res.reference);

},"json");

}

// ── Show/hide <?=$corporate_card_fee>0?$corporate_card_fee:10?>% corporate fee notice and update order summary ───────────────
function updateFeeDisplay(isCorporate, cur) {
    const symbol = currSymbols[cur] || cur;
    let base = subtotalAmount + taxAmount + chargesAmount - discountAmount;
    let total = base;
    if(isCorporate){
        const fee = base * (card_fee/100);
        total = base + fee;
        $("#corporateFeeRow").show();
        $("#corporateFee").text(
            symbol + fee.toFixed(2) + " " + cur
        );
    } else {
        $("#corporateFeeRow").hide();
    }
    $("#totalAmount").html(
        total.toFixed(2)+' <span class="currency">'+cur+'</span>'
    );
}

async function mountFlow(paymentSession,reference){

$("#loader").hide();

checkout = await CheckoutWebComponents({

publicKey:"<?= CKO_PUBLIC_KEY ?>",
environment:"sandbox",
paymentSession:paymentSession,
componentOptions: {
    stored_card: {
        captureCardCvv: true,   // always capture CVV for stored card payments
    },
},

onPaymentCompleted:function(_self,paymentResponse){

window.location =
"result.php?cko-payment-id="
+paymentResponse.id
+"&ref="+reference;

},

onError:function(_component,error){

// $("#flow-container").html(
// '<div class="error-box">'+
// '<i class="bi bi-x-circle text-danger" style="font-size:40px"></i>'+
// '<p class="mt-2">Payment could not be initialized</p>'+
// '</div>'
// );

}

});

const flow = checkout.create("flow",{


// ── onCardBinChanged — fires when first 8 card digits entered ─────────────
// Does NOT remount — only updates the fee display and isCorporateCard flag.
onCardBinChanged: async function(_self, cardMetadata) {
    isCorporateCard = (cardMetadata.card_category || "").toLowerCase() === "commercial";
    let newAmount = baseAmountMinor;
    if(isCorporateCard){
        newAmount = Math.round(baseAmountMinor+(baseAmountMinor*card_fee/100));
    }
    console.log("card_category: "+cardMetadata.card_category);
    currentAmountMinor = newAmount;
    updateFeeDisplay(isCorporateCard, cur);
    if(checkout){
        await checkout.update({
            amount:newAmount
        });
    }
    return { continue:true };
},

handleSubmit:async function(_self,submitData){

    const res = await fetch("api/submit-session.php",{

    method:"POST",
    headers:{'Content-Type':'application/json'},

    body:JSON.stringify({

    session_id:sessionId,
    session_data:submitData,
    amount:currentAmountMinor

    })

    });

    const data = await res.json();

    /* HANDLE PAYMENT ERRORS */

    if(!data.success){

        // Session attempts exceeded → recreate session
        if(data.debug && data.debug.includes("payment_attempts_exceeded")){

            $("#flow-container").prepend(
            '<div class="alert alert-warning mb-3" id="payment_alert">'+
            '<i class="bi bi-exclamation-triangle"></i> '+
            'Payment session expired. Reloading secure payment...'+
            '</div>'
            );

            setTimeout(()=>{
                createSession();
            },1500);

            return;
        }

        // Return error to Checkout → shows message below card input
        // return {
        //     success: false,
        //     message: data.message || "Payment failed."
        // };

    }

    // let cardToken = null;
    // try {
    //     const decoded = JSON.parse(atob(submitData.session_data));
    //     if(decoded?.source?.instrument_reference){
    //         cardToken = decoded.source.instrument_reference;
    //     }
    //     console.log("decoded:", decoded);
    // } catch(e){
    //     console.log("Decode error", e);
    // }
    // console.log("cardToken:", cardToken);
    // // ✅ use this
    // if(data.success && cardToken){
    //     console.log("card_token:", cardToken);
    //     // call link-card action ajax
    // }

    return data;

}

});

flow.mount(document.getElementById("flow-container"));

}

<?php endif; ?>

</script>

</body>
</html>