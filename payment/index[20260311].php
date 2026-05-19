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

</head>

<body>

<div class="payment-wrapper">

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

<div class="amount-box">
<?= number_format($amount/100,2) ?>
<span class="currency"><?= htmlspecialchars($currency) ?></span>
</div>

<div class="text-muted mb-3">
Amount to be charged
</div>

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

<script>

let sessionId = "";
let baseAmount = <?= $amount ?>;

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

async function mountFlow(paymentSession,reference){

$("#loader").hide();

const checkout = await CheckoutWebComponents({

publicKey:"<?= CKO_PUBLIC_KEY ?>",
environment:"sandbox",
paymentSession:paymentSession,

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

handleSubmit:async function(_self,submitData){

    const res = await fetch("api/submit-session.php",{

    method:"POST",
    headers:{'Content-Type':'application/json'},

    body:JSON.stringify({

    session_id:sessionId,
    session_data:submitData,
    amount:baseAmount

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

    return data;

}

});

flow.mount(document.getElementById("flow-container"));

}

<?php endif; ?>

</script>

</body>
</html>