<?php
require_once __DIR__ . '/../../config/db.php';

$amount        = (float)($_POST['amount'] ?? 0);
$travel_date   = $_POST['travel_date'] ?? null;
$forceDiscount = (int)($_POST['force_discount'] ?? 0);

if ($amount <= 0 || !$travel_date) {
    exit;
}

/*
  discounts table columns:
  id, discount_name, description,
  discount_type (fixed|percentage),
  discount_value, min_amount,
  valid_from, valid_to, status
*/

$sql = "
    SELECT
        id,
        discount_name,
        description,
        discount_type,
        discount_value
    FROM discounts
    WHERE status = 'active'
      AND min_amount <= ?
      AND ? BETWEEN valid_from AND valid_to
";

$params = [$amount, $travel_date];
$types  = 'ds';

/* If package has discount_id, restrict to it */
// if ($forceDiscount > 0) {
//     $sql .= " AND id = ?";
//     $params[] = $forceDiscount;
//     $types   .= 'i';
// }

$sql .= " ORDER BY id";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    exit;
}

$autoApplied = false;

while ($d = $res->fetch_assoc()) {

    /* Calculate discount amount */
    if ($d['discount_type'] === 'percentage') {
        $discountAmount = round($amount * ($d['discount_value'] / 100), 2);
    } else {
        $discountAmount = round($d['discount_value'], 2);
    }

    if ($discountAmount <= 0) continue;
    ?>

    <div class="border rounded p-2 mb-2 discount-card">
      <div class="d-flex justify-content-between align-items-center">

        <div>
          <div class="fw-semibold"><?= htmlspecialchars($d['discount_name']) ?></div>
          <div class="small text-muted">
            <?= htmlspecialchars($d['description']) ?>
          </div>
        </div>

        <div class="text-end">
          <div class="fw-bold text-success">
            − £<?= number_format($discountAmount, 2) ?>
          </div>

          <?php if (!$autoApplied): ?>
            <button type="button"
                    class="btn btn-sm btn-outline-success apply-discount-btn mt-1"
                    onclick="applyDiscount(<?= (int)$d['id'] ?>, <?= $discountAmount ?>)">
              Apply
            </button>
          <?php endif; ?>
        </div>

      </div>
    </div>

    <?php

    /* Auto-apply ONLY package-linked discount */
    if ($forceDiscount > 0 && !$autoApplied) {
        echo "<script>applyDiscount({$d['id']}, {$discountAmount});</script>";
        $autoApplied = true;
    }
}
?>