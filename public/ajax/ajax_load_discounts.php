<?php
require_once __DIR__ . '/../../config/db.php';

$amount        = (float)($_POST['amount'] ?? 0);
$travel_date   = $_POST['travel_date'] ?? null;
$forceDiscount = (int)($_POST['force_discount'] ?? 0);

if ($amount <= 0 || !$travel_date) {
    exit;
}

$sql = "
    SELECT
        id,
        discount_name,
        description,
        discount_type,
        discount_value,
        max_discount_amount,
        scope_type,
        min_amount,
        valid_from,
        valid_to,
        expiry_date
    FROM discounts
    WHERE status = 'active'
";

$stmt = $mysqli->prepare($sql);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    exit;
}

$autoApplied = false;

while ($d = $res->fetch_assoc()) {

    /* --------------------------------
       EXPIRY CHECK (TRAVEL DATE BASED)
    --------------------------------- */
    if (!empty($d['expiry_date']) && $travel_date > $d['expiry_date']) {
        continue;
    }

    /* --------------------------------
       SCOPE VALIDATION
    --------------------------------- */
    $eligible = false;

    switch ($d['scope_type']) {

        case 'date_range':
            $eligible =
                $travel_date >= $d['valid_from'] &&
                $travel_date <= $d['valid_to'];
            break;

        case 'amount_based':
            $eligible = $amount >= (float)$d['min_amount'];
            break;

        case 'date_and_amount':
            $eligible =
                $amount >= (float)$d['min_amount'] &&
                $travel_date >= $d['valid_from'] &&
                $travel_date <= $d['valid_to'];
            break;
    }

    if (!$eligible) {
        continue;
    }

    /* --------------------------------
       DISCOUNT CALCULATION
    --------------------------------- */
    if ($d['discount_type'] === 'percentage') {
        $discountAmount = round($amount * ($d['discount_value'] / 100), 2);
    } else {
        $discountAmount = round($d['discount_value'], 2);
    }

    if (!empty($d['max_discount_amount'])) {
        $discountAmount = min($discountAmount, (float)$d['max_discount_amount']);
    }

    if ($discountAmount <= 0) {
        continue;
    }

    $isForced = ($forceDiscount > 0 && (int)$d['id'] === $forceDiscount);
    ?>

    <div class="border rounded p-2 mb-2 discount-card">
      <div class="d-flex justify-content-between align-items-center">

        <div>
          <div class="fw-semibold"><?= htmlspecialchars($d['discount_name']) ?></div>
          <div class="small text-muted"><?= htmlspecialchars($d['description']) ?></div>
        </div>

        <div class="text-end">
          <div class="fw-bold text-success">
            − £<?= number_format($discountAmount, 2) ?>
          </div>

          <button type="button"
                  class="btn btn-sm btn-outline-success apply-discount-btn mt-1"
                  data-id="<?= (int)$d['id'] ?>"
                  data-amount="<?= $discountAmount ?>"
                  <?= $isForced ? 'disabled' : '' ?>
                  onclick="applyDiscount(<?= (int)$d['id'] ?>, <?= $discountAmount ?>)">
            <?= $isForced ? 'Applied' : 'Apply' ?>
          </button>

        </div>

      </div>
    </div>

    <?php

    /* --------------------------------
       AUTO APPLY PACKAGE DISCOUNT
    --------------------------------- */
    if ($isForced && !$autoApplied) {
        // echo "<script>applyDiscount({$d['id']}, {$discountAmount});</script>";
        echo '<div class="auto-apply"
           data-id="'.$d['id'].'"
           data-amount="'.$discountAmount.'"></div>';
        $autoApplied = true;
    }
}
?>