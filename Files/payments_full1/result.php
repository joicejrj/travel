<?php
require_once __DIR__ . '/includes/checkout.php';

$status           = $_GET['status']                  ?? 'unknown';
$reference        = $_GET['ref']                     ?? '';
$paymentId        = $_GET['cko-payment-id']          ?? ($_GET['payment_id'] ?? '');
$ckoSessionId     = $_GET['cko-payment-session-id']  ?? '';  // ps_xxx — always in 3DS redirect

$sessionRow  = null;
$paymentData = null;

// ── Step 1: try to find the DB row ───────────────────────────────────────────
if ($reference) {
    $sessionRow = getSessionByReference($reference);
}
if (!$sessionRow && $ckoSessionId) {
    $sessionRow = getSessionBySessionId($ckoSessionId);
}
if (!$sessionRow && $paymentId) {
    $sessionRow = getSessionByPaymentId($paymentId);
}

// ── Step 2: update the row with payment ID + final status ────────────────────
if ($paymentId) {
    if ($sessionRow) {
        // We have both the row and the payment ID — do a precise update by reference
        $paymentData = updateSessionStatus($sessionRow['reference'], $paymentId);
        $sessionRow  = getSessionByReference($sessionRow['reference']);
    } else {
        // Row not found yet — update by payment ID (will match once payment_id is set)
        $paymentData = updateSessionStatusById($paymentId);
        $sessionRow  = getSessionByPaymentId($paymentId);
    }
}

// Resolve display values
$displayStatus = $sessionRow['status'] ?? $status;

$statusConfig = [
    'authorized' => ['icon'=>'bi-check-circle-fill', 'color'=>'success', 'label'=>'Payment Authorized',   'bg'=>'#ECFDF5','border'=>'#A7F3D0'],
    'captured'   => ['icon'=>'bi-check-circle-fill', 'color'=>'success', 'label'=>'Payment Captured',     'bg'=>'#ECFDF5','border'=>'#A7F3D0'],
    'success'    => ['icon'=>'bi-check-circle-fill', 'color'=>'success', 'label'=>'Payment Successful',   'bg'=>'#ECFDF5','border'=>'#A7F3D0'],
    'declined'   => ['icon'=>'bi-x-circle-fill',     'color'=>'danger',  'label'=>'Payment Declined',     'bg'=>'#FEF2F2','border'=>'#FECACA'],
    'failure'    => ['icon'=>'bi-x-circle-fill',     'color'=>'danger',  'label'=>'Payment Failed',       'bg'=>'#FEF2F2','border'=>'#FECACA'],
    'cancelled'  => ['icon'=>'bi-dash-circle-fill',  'color'=>'warning', 'label'=>'Payment Cancelled',    'bg'=>'#FFFBEB','border'=>'#FDE68A'],
    'cancel'     => ['icon'=>'bi-dash-circle-fill',  'color'=>'warning', 'label'=>'Payment Cancelled',    'bg'=>'#FFFBEB','border'=>'#FDE68A'],
    'expired'    => ['icon'=>'bi-clock-fill',         'color'=>'secondary','label'=>'Session Expired',    'bg'=>'#F9FAFB','border'=>'#E5E7EB'],
    'pending'    => ['icon'=>'bi-hourglass-split',   'color'=>'info',    'label'=>'Payment Pending',      'bg'=>'#EFF6FF','border'=>'#BFDBFE'],
    'unknown'    => ['icon'=>'bi-question-circle-fill','color'=>'secondary','label'=>'Status Unknown',    'bg'=>'#F9FAFB','border'=>'#E5E7EB'],
];
$cfg = $statusConfig[$displayStatus] ?? $statusConfig['unknown'];

// Format amount
function fmtMoney(int $minor, string $currency, int $decimals = 2): string {
    $syms = ['USD'=>'$','EUR'=>'€','GBP'=>'£','AED'=>'د.إ','SAR'=>'﷼','EGP'=>'E£','PLN'=>'zł'];
    $sym  = $syms[$currency] ?? $currency . ' ';
    $d    = in_array($currency, ['KWD','BHD','OMR']) ? 3 : (in_array($currency, ['JPY','KRW']) ? 0 : 2);
    return $sym . number_format($minor / pow(10, $d), $d);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Result</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand:      #0F52BA;
            --brand-light:#EEF3FB;
            --text-main:  #1A1A2E;
            --text-muted: #6B7280;
            --border:     #E5E7EB;
            --bg:         #F8F9FC;
            --white:      #FFFFFF;
            --radius:     10px;
            --shadow:     0 2px 16px rgba(15,82,186,.08);
        }
        body {
            font-family:'DM Sans', sans-serif;
            background: var(--bg);
            color: var(--text-main);
            min-height: 100vh;
        }
        .topbar {
            background: var(--white); border-bottom:1px solid var(--border);
            padding: 14px 24px; display:flex; align-items:center; gap:10px;
        }
        .topbar-logo { width:32px;height:32px;background:var(--brand);border-radius:8px;display:grid;place-items:center; }
        .topbar-logo i { color:#fff;font-size:16px; }
        .topbar-name { font-weight:600;font-size:15px; }
        .topbar-badge { margin-left:auto;font-size:11px;background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;border-radius:20px;padding:3px 10px;font-weight:500; }

        .result-wrap { max-width:600px;margin:48px auto;padding:0 20px; }

        /* Status banner */
        .status-banner {
            border-radius: var(--radius);
            border: 1px solid <?= $cfg['border'] ?>;
            background: <?= $cfg['bg'] ?>;
            padding: 28px 24px;
            text-align: center;
            margin-bottom: 24px;
        }
        .status-icon { font-size: 44px; line-height:1; }
        .status-label { font-size:20px;font-weight:600;margin-top:12px;margin-bottom:4px; }
        .status-ref { font-family:'DM Mono',monospace;font-size:12px;color:var(--text-muted); }

        /* Detail card */
        .detail-card {
            background: var(--white);
            border:1px solid var(--border);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow:hidden;
            margin-bottom: 20px;
        }
        .detail-header {
            padding:16px 20px;
            border-bottom:1px solid var(--border);
            font-size:13px;font-weight:600;
            text-transform:uppercase;letter-spacing:.5px;
            color:var(--text-muted);
            display:flex;align-items:center;gap:8px;
        }
        .detail-row {
            display:flex; justify-content:space-between; align-items:flex-start;
            padding:12px 20px; border-bottom:1px solid var(--border);
            font-size:14px;
        }
        .detail-row:last-child { border-bottom:none; }
        .detail-label { color:var(--text-muted);font-size:13px;flex-shrink:0;margin-right:12px; }
        .detail-value { font-weight:500;text-align:right;word-break:break-all;font-family:'DM Mono',monospace;font-size:13px; }
        .detail-value.plain { font-family:'DM Sans',sans-serif;font-size:14px; }

        /* JSON block */
        .json-block {
            background:#1E293B;
            border-radius:8px;
            padding:16px;
            font-family:'DM Mono',monospace;
            font-size:11.5px;
            line-height:1.7;
            color:#94A3B8;
            overflow-x:auto;
            max-height:320px;
            white-space:pre;
        }
        .json-block .key   { color:#7DD3FC; }
        .json-block .str   { color:#A3E635; }
        .json-block .num   { color:#FB923C; }
        .json-block .bool  { color:#F472B6; }
        .json-block .null_ { color:#94A3B8; }

        .btn-back {
            display:inline-flex;align-items:center;gap:8px;
            background:var(--brand);color:#fff;border:none;
            border-radius:8px;padding:11px 20px;font-size:14px;
            font-weight:600;font-family:'DM Sans',sans-serif;
            text-decoration:none;transition:background .15s;
        }
        .btn-back:hover { background:#0A3D8A;color:#fff; }

        .badge-status {
            display:inline-block;padding:4px 12px;border-radius:20px;
            font-size:12px;font-weight:600;letter-spacing:.3px;text-transform:uppercase;
        }
        @media (max-width:480px) { .result-wrap { margin:24px auto; } }
    </style>
</head>
<body>

<div class="topbar">
    <div class="topbar-logo"><i class="bi bi-credit-card-2-front"></i></div>
    <span class="topbar-name"><?= APP_NAME ?></span>
    <span class="topbar-badge"><i class="bi bi-cone-striped me-1"></i>SANDBOX MODE</span>
</div>

<div class="result-wrap">

    <!-- Status Banner -->
    <div class="status-banner">
        <div class="status-icon text-<?= $cfg['color'] ?>">
            <i class="bi <?= $cfg['icon'] ?>"></i>
        </div>
        <div class="status-label"><?= $cfg['label'] ?></div>
        <?php if ($reference): ?>
        <div class="status-ref">Ref: <?= htmlspecialchars($reference) ?></div>
        <?php endif; ?>
    </div>

    <!-- Payment Details -->
    <?php if ($sessionRow): ?>
    <div class="detail-card">
        <div class="detail-header"><i class="bi bi-receipt"></i> Payment Details</div>

        <div class="detail-row">
            <span class="detail-label">Status</span>
            <span class="detail-value plain">
                <span class="badge-status bg-<?= $cfg['color'] ?> text-<?= $cfg['color'] === 'warning' ? 'dark' : 'white' ?>">
                    <?= strtoupper($sessionRow['status']) ?>
                </span>
            </span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Amount</span>
            <span class="detail-value">
                <?= fmtMoney((int)$sessionRow['amount'], $sessionRow['currency']) ?>
                <span style="color:var(--text-muted);font-size:11px"> <?= $sessionRow['currency'] ?></span>
            </span>
        </div>

        <?php if ($sessionRow['payment_method']): ?>
        <div class="detail-row">
            <span class="detail-label">Method</span>
            <span class="detail-value plain"><?= htmlspecialchars(ucfirst($sessionRow['payment_method'])) ?></span>
        </div>
        <?php endif; ?>

        <div class="detail-row">
            <span class="detail-label">Customer</span>
            <span class="detail-value plain"><?= htmlspecialchars($sessionRow['customer_name']) ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Email</span>
            <span class="detail-value plain"><?= htmlspecialchars($sessionRow['customer_email']) ?></span>
        </div>

        <?php if ($sessionRow['description']): ?>
        <div class="detail-row">
            <span class="detail-label">Description</span>
            <span class="detail-value plain"><?= htmlspecialchars($sessionRow['description']) ?></span>
        </div>
        <?php endif; ?>

        <?php if ($sessionRow['payment_id']): ?>
        <div class="detail-row">
            <span class="detail-label">Payment ID</span>
            <span class="detail-value"><?= htmlspecialchars($sessionRow['payment_id']) ?></span>
        </div>
        <?php endif; ?>

        <div class="detail-row">
            <span class="detail-label">Session ID</span>
            <span class="detail-value"><?= htmlspecialchars($sessionRow['session_id']) ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Reference</span>
            <span class="detail-value"><?= htmlspecialchars($sessionRow['reference']) ?></span>
        </div>

        <div class="detail-row">
            <span class="detail-label">Created</span>
            <span class="detail-value plain"><?= $sessionRow['created_at'] ?></span>
        </div>
    </div>
    <?php endif; ?>


    <!-- Submit Debug Log (only shown when DEBUG_MODE is on and debug_log exists) -->
    <?php if (defined('DEBUG_MODE') && DEBUG_MODE && $sessionRow && !empty($sessionRow['debug_log'])): ?>
    <?php $debugData = json_decode($sessionRow['debug_log'], true); ?>
    <div class="detail-card mb-4">
        <div class="detail-header" style="cursor:pointer;background:#1E293B;color:#7DD3FC"
             data-bs-toggle="collapse" data-bs-target="#debugCollapse">
            <i class="bi bi-bug-fill"></i>
            Submit Debug Log
            <i class="bi bi-chevron-down ms-auto"></i>
        </div>
        <div class="collapse show" id="debugCollapse">
            <div style="padding:16px;background:#0F172A">

                <!-- Amount comparison -->
                <div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:16px">
                    <div style="background:#1E293B;border:1px solid #334155;border-radius:8px;padding:12px 18px;flex:1;min-width:140px">
                        <div style="font-size:10px;color:#64748B;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Original Amount</div>
                        <div style="font-size:20px;font-weight:600;color:#7DD3FC;font-family:'DM Mono',monospace">
                            <?php echo number_format(($sessionRow['amount'] ?? 0) / 100, 2); ?>
                        </div>
                        <div style="font-size:11px;color:#475569">minor: <?php echo $sessionRow['amount'] ?? 0; ?></div>
                    </div>
                    <div style="background:#1E293B;border:1px solid #334155;border-radius:8px;padding:12px 18px;flex:1;min-width:140px">
                        <div style="font-size:10px;color:#64748B;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Amount Sent to API</div>
                        <div style="font-size:20px;font-weight:600;color:<?php echo ($sessionRow['charged_amount'] ?? 0) > ($sessionRow['amount'] ?? 0) ? '#A3E635' : '#7DD3FC'; ?>;font-family:'DM Mono',monospace">
                            <?php echo number_format(($sessionRow['charged_amount'] ?? 0) / 100, 2); ?>
                        </div>
                        <div style="font-size:11px;color:#475569">minor: <?php echo $sessionRow['charged_amount'] ?? '—'; ?></div>
                    </div>
                    <div style="background:#1E293B;border:1px solid #334155;border-radius:8px;padding:12px 18px;flex:1;min-width:140px">
                        <div style="font-size:10px;color:#64748B;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Fee Applied</div>
                        <?php
                            $orig     = $sessionRow['amount'] ?? 0;
                            $charged  = $sessionRow['charged_amount'] ?? 0;
                            $feeDiff  = $charged - $orig;
                            $feeColor = $feeDiff > 0 ? '#A3E635' : '#94A3B8';
                        ?>
                        <div style="font-size:20px;font-weight:600;color:<?php echo $feeColor ?>;font-family:'DM Mono',monospace">
                            <?php echo $feeDiff > 0 ? '+' . number_format($feeDiff / 100, 2) : '—'; ?>
                        </div>
                        <div style="font-size:11px;color:#475569">
                            <?php echo $feeDiff > 0 ? round(($feeDiff / $orig) * 100) . '% surcharge' : 'no surcharge'; ?>
                        </div>
                    </div>
                </div>

                <!-- session_data preview -->
                <div style="margin-bottom:12px">
                    <div style="font-size:11px;color:#64748B;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">
                        session_data sent (type: <?php echo htmlspecialchars($debugData['session_data_type'] ?? '?') ?>)
                    </div>
                    <pre style="background:#1E293B;border:1px solid #334155;border-radius:6px;padding:12px;font-size:11px;color:#94A3B8;overflow-x:auto;white-space:pre-wrap;word-break:break-all;margin:0"><?php
                        echo htmlspecialchars($debugData['session_data_preview'] ?? '—');
                    ?></pre>
                </div>

                <!-- Checkout API response -->
                <div>
                    <div style="font-size:11px;color:#64748B;text-transform:uppercase;letter-spacing:.6px;margin-bottom:6px">
                        Checkout.com /submit response (HTTP <?php echo htmlspecialchars((string)($debugData['http_status'] ?? '?')) ?>)
                    </div>
                    <pre style="background:#1E293B;border:1px solid #334155;border-radius:6px;padding:12px;font-size:11px;color:#94A3B8;overflow-x:auto;white-space:pre-wrap;margin:0"><?php
                        echo htmlspecialchars(json_encode($debugData['cko_response'] ?? $debugData['error'] ?? '—', JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                    ?></pre>
                </div>

            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Actions -->
    <div class="d-flex gap-3 flex-wrap">
        <a href="index.php" class="btn-back"><i class="bi bi-arrow-left"></i> New Payment</a>
        <?php if ($sessionRow && $sessionRow['payment_id']): ?>
        <a href="payments.php" class="btn-back" style="background:#374151">
            <i class="bi bi-table"></i> View All Payments
        </a>
        <?php endif; ?>
    </div>

</div><!-- /result-wrap -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
