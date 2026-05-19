<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/config/config.php';

$db = getDB();

// Filters
$filterStatus = $_GET['status'] ?? '';
$filterSearch = trim($_GET['q'] ?? '');
$page  = max(1, (int)($_GET['page'] ?? 1));
$limit = 20;
$offset= ($page - 1) * $limit;

$where  = [];
$params = [];
if ($filterStatus) {
    $where[]          = 'status = :status';
    $params[':status']= $filterStatus;
}
if ($filterSearch) {
    $where[]           = '(reference LIKE :q OR customer_name LIKE :q OR customer_email LIKE :q OR payment_id LIKE :q)';
    $params[':q']      = '%' . $filterSearch . '%';
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$total = (int)$db->prepare("SELECT COUNT(*) FROM payment_sessions $whereSQL")->execute($params) ? 0 : 0;
$countStmt = $db->prepare("SELECT COUNT(*) FROM payment_sessions $whereSQL");
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();

$stmt = $db->prepare("SELECT * FROM payment_sessions $whereSQL ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute($params);
$payments = $stmt->fetchAll();
$pages = (int)ceil($total / $limit);

// Stats
$stats = $db->query("
    SELECT status, COUNT(*) as cnt, SUM(amount) as total
    FROM payment_sessions GROUP BY status
")->fetchAll();
$statMap = array_column($stats, null, 'status');

function fmtMoney(int $minor, string $cur): string {
    $d   = in_array($cur,['KWD','BHD','OMR']) ? 3 : (in_array($cur,['JPY','KRW']) ? 0 : 2);
    $sym = ['USD'=>'$','EUR'=>'€','GBP'=>'£','AED'=>'د.إ','SAR'=>'﷼','EGP'=>'E£','PLN'=>'zł'][$cur] ?? $cur.' ';
    return $sym . number_format($minor / pow(10,$d), $d);
}

$statusColors = [
    'authorized'=>'success','captured'=>'success','pending'=>'info',
    'declined'=>'danger','cancelled'=>'warning','expired'=>'secondary',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> — Payments Log</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --brand:#0F52BA; --brand-light:#EEF3FB; --text-main:#1A1A2E;
            --text-muted:#6B7280; --border:#E5E7EB; --bg:#F8F9FC;
            --white:#FFFFFF; --shadow:0 2px 16px rgba(15,82,186,.08);
        }
        body { font-family:'DM Sans',sans-serif; background:var(--bg); color:var(--text-main); }
        .topbar { background:var(--white);border-bottom:1px solid var(--border);padding:14px 24px;display:flex;align-items:center;gap:10px; }
        .topbar-logo { width:32px;height:32px;background:var(--brand);border-radius:8px;display:grid;place-items:center; }
        .topbar-logo i { color:#fff;font-size:16px; }
        .topbar-name { font-weight:600;font-size:15px; }
        .topbar-badge { margin-left:auto;font-size:11px;background:#FEF3C7;color:#92400E;border:1px solid #FDE68A;border-radius:20px;padding:3px 10px; }
        .topbar-link { margin-left:16px;font-size:13px;color:var(--brand);text-decoration:none;font-weight:500; }

        .content { max-width:1200px;margin:32px auto;padding:0 20px; }

        .stat-card {
            background:var(--white);border:1px solid var(--border);border-radius:10px;
            padding:16px 20px;box-shadow:var(--shadow);
        }
        .stat-num { font-size:24px;font-weight:600;font-family:'DM Mono',monospace;color:var(--brand); }
        .stat-label { font-size:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-top:2px; }

        .table-card { background:var(--white);border:1px solid var(--border);border-radius:10px;box-shadow:var(--shadow);overflow:hidden; }
        .table-header { padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap; }
        .table-header h5 { margin:0;font-size:15px;font-weight:600;display:flex;align-items:center;gap:8px; }
        .table-header h5 i { color:var(--brand); }

        table { margin:0; }
        thead th { font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);border-bottom:1px solid var(--border)!important;padding:10px 16px;background:#FAFAFA; }
        tbody td { font-size:13px;padding:12px 16px;vertical-align:middle;border-color:var(--border); }
        tbody tr:hover { background:#FAFBFF; }
        .mono { font-family:'DM Mono',monospace;font-size:12px; }

        .form-control-sm, .form-select-sm {
            font-size:13px;border-radius:7px;border:1px solid var(--border);padding:7px 12px;
            font-family:'DM Sans',sans-serif;
        }
        .form-control-sm:focus,.form-select-sm:focus { border-color:var(--brand);box-shadow:0 0 0 3px rgba(15,82,186,.1);outline:none; }

        .btn-new { display:inline-flex;align-items:center;gap:6px;background:var(--brand);color:#fff;border:none;border-radius:7px;padding:8px 16px;font-size:13px;font-weight:600;font-family:'DM Sans',sans-serif;text-decoration:none; }
        .btn-new:hover { background:#0A3D8A;color:#fff; }
        .pagination .page-link { font-size:13px;color:var(--brand);border-color:var(--border); }
        .pagination .active .page-link { background:var(--brand);border-color:var(--brand); }
    </style>
</head>
<body>
<div class="topbar">
    <div class="topbar-logo"><i class="bi bi-credit-card-2-front"></i></div>
    <span class="topbar-name"><?= APP_NAME ?></span>
    <a href="index.php" class="topbar-link"><i class="bi bi-plus-circle me-1"></i>New Payment</a>
    <span class="topbar-badge"><i class="bi bi-cone-striped me-1"></i>SANDBOX MODE</span>
</div>

<div class="content">

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-num"><?= $total ?></div>
                <div class="stat-label">Total Sessions</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <?php $s = ($statMap['authorized']['cnt'] ?? 0) + ($statMap['captured']['cnt'] ?? 0); ?>
                <div class="stat-num text-success"><?= $s ?></div>
                <div class="stat-label">Successful</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-num text-danger"><?= $statMap['declined']['cnt'] ?? 0 ?></div>
                <div class="stat-label">Declined</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stat-card">
                <div class="stat-num text-warning"><?= $statMap['pending']['cnt'] ?? 0 ?></div>
                <div class="stat-label">Pending</div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="table-card">
        <div class="table-header">
            <h5><i class="bi bi-table"></i> Payment Sessions</h5>
            <form method="GET" class="d-flex gap-2 flex-wrap align-items-center">
                <input class="form-control-sm" name="q" placeholder="Search…" value="<?= htmlspecialchars($filterSearch) ?>" style="width:200px">
                <select class="form-select-sm" name="status" style="width:140px">
                    <option value="">All statuses</option>
                    <?php foreach(['pending','authorized','captured','declined','cancelled','expired'] as $s): ?>
                    <option value="<?= $s ?>" <?= $filterStatus===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn-new"><i class="bi bi-funnel"></i> Filter</button>
            </form>
            <a href="index.php" class="btn-new"><i class="bi bi-plus"></i> New</a>
        </div>

        <div class="table-responsive">
            <table class="table table-borderless">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Reference</th>
                        <th>Customer</th>
                        <th>Amount / Fee</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Payment ID</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($payments): ?>
                <?php foreach($payments as $row): ?>
                <tr>
                    <td class="mono text-muted"><?= $row['id'] ?></td>
                    <td class="mono"><?= htmlspecialchars($row['reference']) ?></td>
                    <td>
                        <div style="font-weight:500"><?= htmlspecialchars($row['customer_name']) ?></div>
                        <div style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($row['customer_email']) ?></div>
                    </td>
                    <td class="mono">
                        <?php
                            $orig     = (int)$row['amount'];
                            $charged  = isset($row['charged_amount']) && $row['charged_amount'] !== null
                                        ? (int)$row['charged_amount'] : null;
                            $hasFee   = $charged !== null && $charged > $orig;
                            $fee      = $hasFee ? $charged - $orig : 0;
                        ?>
                        <?php if ($hasFee): ?>
                            <div><?= fmtMoney($charged, $row['currency']) ?>
                                <span style="font-size:11px;color:var(--text-muted)"> <?= $row['currency'] ?></span>
                            </div>
                            <div style="font-size:11px;color:#6B7280;margin-top:2px">
                                Base: <?= fmtMoney($orig, $row['currency']) ?>
                                <span style="color:#D97706;margin-left:4px">+<?= fmtMoney($fee, $row['currency']) ?> fee</span>
                            </div>
                        <?php else: ?>
                            <?= fmtMoney($orig, $row['currency']) ?>
                            <span style="font-size:11px;color:var(--text-muted)"> <?= $row['currency'] ?></span>
                        <?php endif; ?>
                    </td>
                    <td><?= $row['payment_method'] ? htmlspecialchars(ucfirst($row['payment_method'])) : '<span class="text-muted">—</span>' ?></td>
                    <td>
                        <span class="badge bg-<?= $statusColors[$row['status']] ?? 'secondary' ?>" style="font-size:11px;font-weight:600">
                            <?= strtoupper($row['status']) ?>
                        </span>
                    </td>
                    <td class="mono" style="font-size:11px">
                        <?= $row['payment_id'] ? htmlspecialchars(substr($row['payment_id'],0,20)).'…' : '<span class="text-muted">—</span>' ?>
                    </td>
                    <td style="font-size:12px;color:var(--text-muted)"><?= $row['created_at'] ?></td>
                </tr>
                <?php endforeach; ?>
                <?php else: ?>
                <tr><td colspan="8" class="text-center py-5 text-muted">No payment sessions found.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($pages > 1): ?>
        <div class="d-flex justify-content-center py-3 border-top">
            <nav><ul class="pagination mb-0">
                <?php for($i=1;$i<=$pages;$i++): ?>
                <li class="page-item <?= $i===$page?'active':'' ?>">
                    <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($filterStatus) ?>&q=<?= urlencode($filterSearch) ?>"><?= $i ?></a>
                </li>
                <?php endfor; ?>
            </ul></nav>
        </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
