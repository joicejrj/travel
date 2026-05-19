<?php
require_once __DIR__ . '/config/config.php';

$currencies = ['USD','EUR','GBP','AED','SAR','KWD','EGP','PLN'];
$countries  = [
    'GB'=>'United Kingdom','US'=>'United States','DE'=>'Germany',
    'FR'=>'France','NL'=>'Netherlands','BE'=>'Belgium','ES'=>'Spain',
    'IT'=>'Italy','AT'=>'Austria','PL'=>'Poland','PT'=>'Portugal',
    'AE'=>'United Arab Emirates','SA'=>'Saudi Arabia','KW'=>'Kuwait',
    'EG'=>'Egypt','AU'=>'Australia','CA'=>'Canada',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Payment</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand:       #0F52BA;
            --brand-light: #EEF3FB;
            --brand-dark:  #0A3D8A;
            --text-main:   #1A1A2E;
            --text-muted:  #6B7280;
            --border:      #E5E7EB;
            --bg:          #F8F9FC;
            --white:       #FFFFFF;
            --success:     #059669;
            --radius:      10px;
            --shadow:      0 2px 16px rgba(15,82,186,.08);
        }
        body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text-main); min-height:100vh; margin:0; }

        .topbar { background:var(--white);border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;gap:10px; }
        .topbar-logo { width:32px;height:32px;background:var(--brand);border-radius:8px;display:grid;place-items:center; }
        .topbar-logo i { color:#fff;font-size:16px; }
        .topbar-name { font-weight:600;font-size:15px; }
        .topbar-badge { margin-left:auto;font-size:11px;background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;border-radius:20px;padding:3px 10px;font-weight:500; }
        .topbar-link { margin-left:16px;font-size:13px;color:var(--brand);text-decoration:none;font-weight:500; }

        .page-wrap { max-width:960px;margin:40px auto;padding:0 20px;display:grid;grid-template-columns:1fr 360px;gap:28px;align-items:start; }
        @media(max-width:767px){ .page-wrap{grid-template-columns:1fr;margin:24px auto;} }

        .card-clean { background:var(--white);border:1px solid var(--border);border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;margin-bottom:20px; }
        .card-header-clean { padding:18px 24px 14px;border-bottom:1px solid var(--border); }
        .card-header-clean h5 { margin:0;font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px; }
        .card-header-clean h5 i { color:var(--brand); }
        .card-body-clean { padding:24px; }

        .form-label { font-size:12px;font-weight:600;color:var(--text-muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px; }
        .form-control,.form-select { border:1px solid var(--border);border-radius:8px;padding:10px 14px;font-size:14px;font-family:'DM Sans',sans-serif;transition:border-color .15s,box-shadow .15s; }
        .form-control:focus,.form-select:focus { border-color:var(--brand);box-shadow:0 0 0 3px rgba(15,82,186,.12);outline:none; }
        .input-group-text { border:1px solid var(--border);background:#F3F4F6;font-size:13px;font-family:'DM Mono',monospace;color:var(--text-muted);border-radius:8px 0 0 8px!important; }
        .input-group .form-control { border-radius:0 8px 8px 0!important; }
        .form-text { font-size:12px;color:var(--text-muted);margin-top:5px; }

        /* Steps */
        .step-indicator { display:flex;align-items:center;gap:0;margin-bottom:28px; }
        .step { display:flex;align-items:center;gap:8px;font-size:13px;font-weight:500; }
        .step-num { width:26px;height:26px;border-radius:50%;display:grid;place-items:center;font-size:12px;font-weight:700;flex-shrink:0; }
        .step.active .step-num { background:var(--brand);color:#fff; }
        .step.done .step-num { background:#DCFCE7;color:#15803D; }
        .step.inactive .step-num { background:#F3F4F6;color:var(--text-muted); }
        .step.active span { color:var(--text-main); }
        .step.inactive span { color:var(--text-muted); }
        .step-line { flex:1;height:1px;background:var(--border);margin:0 10px; }

        /* Flow container */
        #flow-container { min-height:80px; }
        #flow-wrap { display:none; }

        /* Amount display in summary */
        .amount-display { font-family:'DM Mono',monospace;font-size:28px;font-weight:500;color:var(--brand);text-align:center;padding:12px 0 2px;letter-spacing:-1px; }
        .amount-hint { text-align:center;font-size:12px;color:var(--text-muted);margin-bottom:8px; }

        .summary-row { display:flex;justify-content:space-between;align-items:flex-start;padding:10px 0;border-bottom:1px solid var(--border);font-size:14px; }
        .summary-row:last-child { border-bottom:none; }
        .summary-label { color:var(--text-muted);font-size:13px; }
        .summary-value { font-weight:500;text-align:right;max-width:60%;word-break:break-word; }

        .btn-primary-custom { background:var(--brand);color:#fff;border:none;border-radius:8px;padding:12px;font-size:15px;font-weight:600;font-family:'DM Sans',sans-serif;width:100%;display:flex;align-items:center;justify-content:center;gap:8px;cursor:pointer;transition:background .15s; }
        .btn-primary-custom:hover:not(:disabled) { background:var(--brand-dark); }
        .btn-primary-custom:disabled { opacity:.65;cursor:not-allowed; }
        .btn-primary-custom .spinner-border { width:16px;height:16px;border-width:2px; }

        .security-note { text-align:center;font-size:12px;color:var(--text-muted);margin-top:14px;display:flex;align-items:center;justify-content:center;gap:5px; }
        .security-note i { color:var(--success); }

        /* Card metadata chips */
        .meta-chip { display:flex;flex-direction:column;gap:2px;background:var(--brand-light);border:1px solid #C7D9F5;border-radius:8px;padding:8px 14px;min-width:100px; }
        .meta-label { font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;color:var(--text-muted); }
        .meta-value { font-size:13px;font-weight:600;color:var(--brand-dark);text-transform:capitalize;font-family:'DM Mono',monospace; }
        .meta-value.credit  { color:#DC2626; }
        .meta-value.debit   { color:#059669; }
        .meta-value.prepaid { color:#D97706; }

        .alert-clean { border-radius:8px;border:none;padding:12px 16px;font-size:14px;display:flex;align-items:flex-start;gap:10px; }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-logo"><i class="bi bi-credit-card-2-front"></i></div>
    <span class="topbar-name"><?= APP_NAME ?></span>
    <a href="payments.php" class="topbar-link"><i class="bi bi-table me-1"></i>Payment Log</a>
    <span class="topbar-badge"><i class="bi bi-cone-striped me-1"></i>SANDBOX MODE</span>
</div>

<div class="page-wrap">

    <!-- LEFT -->
    <div>

        <!-- Step indicator -->
        <div class="step-indicator">
            <div class="step active" id="step1El">
                <div class="step-num">1</div><span>Order Details</span>
            </div>
            <div class="step-line"></div>
            <div class="step inactive" id="step2El">
                <div class="step-num">2</div><span>Payment</span>
            </div>
        </div>

        <!-- Error -->
        <div id="errorAlert" class="alert-clean alert-danger mb-3" style="display:none">
            <i class="bi bi-exclamation-triangle-fill text-danger"></i>
            <span id="errorMsg"></span>
        </div>

        <!-- STEP 1: Order form -->
        <div id="step1">
            <div class="card-clean">
                <div class="card-header-clean"><h5><i class="bi bi-person-circle"></i> Customer Details</h5></div>
                <div class="card-body-clean">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="customer_name" placeholder="Jane Doe">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control" id="customer_email" placeholder="jane@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Billing Country</label>
                            <select class="form-select" id="billing_country">
                                <?php foreach($countries as $code => $name): ?>
                                <option value="<?= $code ?>" <?= $code==='GB'?'selected':'' ?>><?= $name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-clean">
                <div class="card-header-clean"><h5><i class="bi bi-receipt"></i> Payment Details</h5></div>
                <div class="card-body-clean">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <input type="text" class="form-control" id="description" value="Sandbox test payment">
                        </div>
                        <div class="col-7">
                            <label class="form-label">Amount</label>
                            <div class="input-group">
                                <span class="input-group-text" id="currencySymbol">£</span>
                                <input type="number" class="form-control" id="amountDisplay" value="10.00" min="0.01" step="0.01">
                            </div>
                            <div class="form-text">Enter the display amount (e.g. 10.00)</div>
                        </div>
                        <div class="col-5">
                            <label class="form-label">Currency</label>
                            <select class="form-select" id="currency">
                                <?php foreach($currencies as $c): ?>
                                <option value="<?= $c ?>" <?= $c==='GBP'?'selected':'' ?>><?= $c ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <button class="btn-primary-custom" id="continueBtn" onclick="createSession()">
                <i class="bi bi-lock-fill"></i>
                <span id="continueBtnText">Continue to Payment</span>
                <div class="spinner-border text-white d-none" id="continueSpinner"></div>
            </button>
            <div class="security-note"><i class="bi bi-shield-check-fill"></i> Secured by Checkout.com · Sandbox</div>
        </div>

        <!-- STEP 2: Flow.js component renders here -->
        <div id="step2" style="display:none">
            <div class="card-clean">
                <div class="card-header-clean"><h5><i class="bi bi-wallet2"></i> Complete Payment</h5></div>
                <div class="card-body-clean">
                    <div id="flow-container"></div>
                </div>
            </div>

            <!-- Card metadata panel — shown when onCardBinChanged fires -->
            <div id="cardMetaPanel" class="card-clean" style="display:none">
                <div class="card-header-clean">
                    <h5><i class="bi bi-credit-card-2-front"></i> Detected Card Info</h5>
                </div>
                <div class="card-body-clean py-2">
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div class="meta-chip" id="metaScheme" style="display:none">
                            <span class="meta-label">Scheme</span>
                            <span class="meta-value" id="metaSchemeVal">—</span>
                        </div>
                        <div class="meta-chip" id="metaType" style="display:none">
                            <span class="meta-label">Card Type</span>
                            <span class="meta-value" id="metaTypeVal">—</span>
                        </div>
                        <div class="meta-chip" id="metaCategory" style="display:none">
                            <span class="meta-label">Category</span>
                            <span class="meta-value" id="metaCategoryVal">—</span>
                        </div>
                        <div class="meta-chip" id="metaCountry" style="display:none">
                            <span class="meta-label">Issuer Country</span>
                            <span class="meta-value" id="metaCountryVal">—</span>
                        </div>
                        <div class="meta-chip" id="metaBank" style="display:none">
                            <span class="meta-label">Issuing Bank</span>
                            <span class="meta-value" id="metaBankVal">—</span>
                        </div>
                        <div class="meta-chip" id="metaCurrency" style="display:none">
                            <span class="meta-label">Card Currency</span>
                            <span class="meta-value" id="metaCurrencyVal">—</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Corporate card fee notice -->
            <div id="corporateFeeNotice" style="display:none" class="alert-clean alert-warning mb-0">
                <i class="bi bi-briefcase-fill text-warning"></i>
                <div>
                    <strong>Corporate card detected</strong> — a 10% business card fee has been applied.<br>
                    <span style="font-size:12px;color:var(--text-muted)">
                        Original: <span id="feeOriginal">—</span> &nbsp;+&nbsp;
                        Fee: <span id="feeAmount">—</span> &nbsp;=&nbsp;
                        <strong>Total: <span id="feeTotal">—</span></strong>
                    </span>
                </div>
            </div>

            <button class="btn-primary-custom" style="background:#6B7280;margin-top:12px" onclick="goBack()">
                <i class="bi bi-arrow-left"></i> Back to Order Details
            </button>
        </div>

    </div>

    <!-- RIGHT: Summary (always visible) -->
    <div class="card-clean" style="position:sticky;top:24px">
        <div class="card-header-clean"><h5><i class="bi bi-clipboard-data"></i> Order Summary</h5></div>
        <div class="card-body-clean p-0">
            <div class="px-4 py-3">
                <div class="amount-display" id="summaryAmount">£10.00</div>
                <div class="amount-hint" id="summaryMinor">Minor units: 1000</div>
            </div>
            <div class="px-4 pb-3">
                <div class="summary-row"><span class="summary-label">Customer</span><span class="summary-value" id="sumName">—</span></div>
                <div class="summary-row"><span class="summary-label">Email</span><span class="summary-value" id="sumEmail">—</span></div>
                <div class="summary-row"><span class="summary-label">Description</span><span class="summary-value" id="sumDesc">Sandbox test payment</span></div>
                <div class="summary-row"><span class="summary-label">Country</span><span class="summary-value" id="sumCountry">United Kingdom</span></div>

                <!-- Fee breakdown — shown only for corporate cards -->
                <div id="sumFeeRows" style="display:none">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal</span>
                        <span class="summary-value" id="sumSubtotal" style="font-family:'DM Mono',monospace">—</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label" style="display:flex;align-items:center;gap:5px">
                            <i class="bi bi-briefcase-fill" style="color:#D97706;font-size:11px"></i>
                            Corporate fee (10%)
                        </span>
                        <span class="summary-value" id="sumFee" style="font-family:'DM Mono',monospace;color:#D97706">—</span>
                    </div>
                </div>

                <div class="summary-row" style="border-bottom:none">
                    <span class="summary-label" style="font-weight:600;color:var(--text-main)">Total</span>
                    <span class="summary-value" id="sumTotal" style="font-size:17px;color:var(--brand);font-family:'DM Mono',monospace">£10.00 GBP</span>
                </div>
            </div>
        </div>
        <div style="background:#F8F9FC;border-top:1px solid var(--border);padding:14px 20px">
            <div style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);margin-bottom:6px">API Endpoint</div>
            <code style="font-size:11px;color:var(--brand);word-break:break-all;line-height:1.6">
                POST <?= CKO_API_BASE ?>/payment-sessions
            </code>
        </div>
    </div>

</div>

<!-- Checkout.com Flow.js -->
<script src="https://checkout-web-components.checkout.com/index.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
/* global CheckoutWebComponents */

const PUBLIC_KEY      = '<?= CKO_PUBLIC_KEY ?>';
let   sessionReference = '';
let   sessionId        = '';      // payment session ID (ps_xxx) for /submit
let   baseAmountMinor  = 0;       // original amount in minor units
let   isCorporateCard  = false;
let   checkoutInstance = null;    // CheckoutWebComponents instance

const currSymbols = {USD:'$',EUR:'€',GBP:'£',AED:'د.إ',SAR:'﷼',KWD:'KD',EGP:'E£',PLN:'zł'};
const currDec     = {KWD:3,BHD:3,OMR:3,JPY:0,KRW:0};

function getDecimals(c){ return currDec[c] ?? 2; }
function toMinor(amt, c){ return Math.round(parseFloat(amt) * Math.pow(10, getDecimals(c))); }
function fmtAmt(amt, c){
    const sym = currSymbols[c] || c+' ';
    return sym + parseFloat(amt).toFixed(getDecimals(c));
}

function updateSummary(){
    const name  = document.getElementById('customer_name').value  || '—';
    const email = document.getElementById('customer_email').value || '—';
    const desc  = document.getElementById('description').value    || '—';
    const raw   = parseFloat(document.getElementById('amountDisplay').value) || 0;
    const cur   = document.getElementById('currency').value;
    const cSel  = document.getElementById('billing_country');
    const minor = toMinor(raw, cur);

    document.getElementById('summaryAmount').textContent = fmtAmt(raw, cur);
    document.getElementById('summaryMinor').textContent  = 'Minor units: ' + minor;
    document.getElementById('sumName').textContent       = name;
    document.getElementById('sumEmail').textContent      = email;
    document.getElementById('sumDesc').textContent       = desc;
    document.getElementById('sumCountry').textContent    = cSel.options[cSel.selectedIndex]?.text || '—';
    document.getElementById('sumTotal').textContent      = fmtAmt(raw, cur) + ' ' + cur;
    document.getElementById('sumTotal').style.color      = 'var(--brand)';
    document.getElementById('currencySymbol').textContent= currSymbols[cur] || cur;
}

['customer_name','customer_email','description','amountDisplay','currency','billing_country']
    .forEach(id => document.getElementById(id).addEventListener('input', updateSummary));
updateSummary();

// ── Show/hide 10% corporate fee notice and update order summary ───────────────
function updateFeeDisplay(isCorporate, cur) {
    const notice   = document.getElementById('corporateFeeNotice');
    const sumTotal = document.getElementById('sumTotal');
    const decimals = getDecimals(cur);
    const sym      = currSymbols[cur] || cur + ' ';
    const base     = baseAmountMinor / Math.pow(10, decimals);

    if (isCorporate) {
        const fee   = base * 0.10;
        const total = base + fee;

        // Left panel banner
        document.getElementById('feeOriginal').textContent = sym + base.toFixed(decimals);
        document.getElementById('feeAmount').textContent   = sym + fee.toFixed(decimals);
        document.getElementById('feeTotal').textContent    = sym + total.toFixed(decimals);
        notice.style.display = 'flex';

        // Right summary card
        document.getElementById('sumFeeRows').style.display  = 'block';
        document.getElementById('sumSubtotal').textContent   = sym + base.toFixed(decimals);
        document.getElementById('sumFee').textContent        = '+ ' + sym + fee.toFixed(decimals);
        sumTotal.textContent  = sym + total.toFixed(decimals) + ' ' + cur;
        sumTotal.style.color  = '#DC2626';
    } else {
        notice.style.display = 'none';

        // Right summary card
        document.getElementById('sumFeeRows').style.display  = 'none';
        sumTotal.textContent  = sym + base.toFixed(decimals) + ' ' + cur;
        sumTotal.style.color  = 'var(--brand)';
    }
}

// ── Display card metadata chips ───────────────────────────────────────────────
function showCardMetadata(m) {
    document.getElementById('cardMetaPanel').style.display = 'block';
    const fields = [
        { chipId: 'metaScheme',   valId: 'metaSchemeVal',   value: m.scheme },
        { chipId: 'metaType',     valId: 'metaTypeVal',     value: m.card_type,     cls: m.card_type },
        { chipId: 'metaCategory', valId: 'metaCategoryVal', value: m.card_category },
        { chipId: 'metaCountry',  valId: 'metaCountryVal',  value: m.issuer_country },
        { chipId: 'metaBank',     valId: 'metaBankVal',     value: m.issuer_name },
        { chipId: 'metaCurrency', valId: 'metaCurrencyVal', value: m.card_currency_code },
    ];
    fields.forEach(({ chipId, valId, value, cls }) => {
        if (value) {
            document.getElementById(chipId).style.display = 'flex';
            const el = document.getElementById(valId);
            el.textContent = value;
            el.className   = 'meta-value' + (cls ? ' ' + cls.toLowerCase() : '');
        }
    });
}

async function createSession(){
    const btn     = document.getElementById('continueBtn');
    const spinner = document.getElementById('continueSpinner');
    const btnText = document.getElementById('continueBtnText');
    const errDiv  = document.getElementById('errorAlert');
    const errMsg  = document.getElementById('errorMsg');

    const name  = document.getElementById('customer_name').value.trim();
    const email = document.getElementById('customer_email').value.trim();
    const raw   = parseFloat(document.getElementById('amountDisplay').value);
    const cur   = document.getElementById('currency').value;

    if(!name || !email || !raw){ errMsg.textContent='Please fill in all required fields.'; errDiv.style.display='flex'; return; }
    errDiv.style.display = 'none';

    btn.disabled=true; spinner.classList.remove('d-none'); btnText.textContent='Creating session…';

    const body = new FormData();
    body.append('customer_name',   name);
    body.append('customer_email',  email);
    body.append('amount',          toMinor(raw, cur));
    body.append('currency',        cur);
    body.append('description',     document.getElementById('description').value);
    body.append('billing_country', document.getElementById('billing_country').value);

    try {
        const res  = await fetch('api/create-session.php', {method:'POST', body});
        const data = await res.json();

        if(!data.success) throw new Error(data.message || 'Unknown error');

        // Store session data for handleSubmit and fee calculation
        sessionReference = data.reference;
        sessionId        = data.paymentSession.id;
        baseAmountMinor  = toMinor(raw, cur);

        await mountFlow(data.paymentSession);

        // Switch to step 2
        document.getElementById('step1').style.display    = 'none';
        document.getElementById('step2').style.display    = 'block';
        document.getElementById('step1El').className      = 'step done';
        document.getElementById('step1El').querySelector('.step-num').innerHTML = '<i class="bi bi-check" style="font-size:13px"></i>';
        document.getElementById('step2El').className      = 'step active';

    } catch(err){
        errMsg.textContent = err.message;
        errDiv.style.display = 'flex';
    } finally {
        btn.disabled=false; spinner.classList.add('d-none'); btnText.textContent='Continue to Payment';
    }
}

async function mountFlow(paymentSession) {
    const cur = document.getElementById('currency').value;

    // ── onCardBinChanged — fires when first 8 card digits entered ─────────────
    // Does NOT remount — only updates the fee display and isCorporateCard flag.
    const onCardBinChanged = async (_self, cardMetadata) => {
        console.log('Card metadata:', cardMetadata);
        showCardMetadata(cardMetadata);

        isCorporateCard = (cardMetadata.card_category || '').toLowerCase() === 'commercial';
        updateFeeDisplay(isCorporateCard, cur);

        // Update Apple/Google Pay sheet amount
        if (checkoutInstance) {
            const newAmount = isCorporateCard
                ? Math.round(baseAmountMinor * 1.10)
                : baseAmountMinor;
            await checkoutInstance.update({ amount: newAmount });
        }

        return { continue: true };
    };

    // ── handleSubmit — ALWAYS registered so Flow.js always gets a response ────
    // For regular cards: return null to let Flow.js handle natively (no /submit call).
    // For corporate cards: call /submit with +10% amount.
    const handleSubmit = async (_self, submitData) => {
        console.log('handleSubmit fired — isCorporateCard:', isCorporateCard,
                    '| baseAmount:', baseAmountMinor);

        const finalAmount = isCorporateCard
            ? Math.round(baseAmountMinor * 1.10)
            : baseAmountMinor;
        console.log(isCorporateCard ? 'Corporate card' : 'Regular card',
                    '— sending finalAmount:', finalAmount);

        const res = await fetch('api/submit-session.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({
                session_id:   sessionId,
                session_data: submitData,
                amount:       finalAmount,
            }),
        });

        const result = await res.json();
        console.log('submit-session.php raw result:', JSON.stringify(result, null, 2));
        if (!result.success) {
            console.error('SUBMIT FAILED — raw debug:', result.debug);
            document.getElementById('errorMsg').textContent = result.message || 'Payment could not be completed. Please try a different card.';
            document.getElementById('errorAlert').style.display = 'flex';
            document.getElementById('errorAlert').scrollIntoView({ behavior: 'smooth', block: 'center' });
            throw new Error(result.message || 'Session submit failed');
        }

        console.log('submit success — cko_response:', JSON.stringify(result.cko_response, null, 2));
        return result.cko_response;
    };

    checkoutInstance = await CheckoutWebComponents({
        publicKey:      PUBLIC_KEY,
        environment:    'sandbox',
        paymentSession: paymentSession,
        componentOptions: {
            stored_card: {
                captureCardCvv: true,   // always capture CVV for stored card payments
            },
        },

        onPaymentCompleted: (_self, paymentResponse) => {
            console.log('onPaymentCompleted, ID:', paymentResponse.id);
            window.location.href = 'result.php?status=success'
                + '&cko-payment-id=' + encodeURIComponent(paymentResponse.id)
                + '&ref='            + encodeURIComponent(sessionReference);
        },

        onChange: (component) => {
            console.log('onChange isValid:', component.isValid(), 'type:', component.type);
        },

        onError: (_component, error) => {
            console.error('Flow error:', error);
            document.getElementById('errorMsg').textContent =
                'Payment error: ' + (error.message || JSON.stringify(error));
            document.getElementById('errorAlert').style.display = 'flex';
        },
    });

    // Always mount with both onCardBinChanged and handleSubmit
    const flowComponent = checkoutInstance.create('flow', { onCardBinChanged, handleSubmit });
    flowComponent.mount(document.getElementById('flow-container'));
}

function goBack(){
    document.getElementById('step1').style.display                  = 'block';
    document.getElementById('step2').style.display                  = 'none';
    document.getElementById('step1El').className                    = 'step active';
    document.getElementById('step1El').querySelector('.step-num').textContent = '1';
    document.getElementById('step2El').className                    = 'step inactive';
    document.getElementById('flow-container').innerHTML             = '';
    document.getElementById('cardMetaPanel').style.display          = 'none';
    document.getElementById('corporateFeeNotice').style.display     = 'none';
    isCorporateCard  = false;
    checkoutInstance = null;
    updateSummary();
}
</script>
</body>
</html>
