<?php
// public/email_log.php
require_once __DIR__ . '/includes/header.php';
require_login();

// Current date or filtered date
$fdate = $_GET['date'] ?? date('Y-m-d');

// Define statuses
$statuses = [
  'Active' => 'Active',
  'Inactive' => 'Inactive',
  'Work in Progress' => 'Work in Progress',
  'Prospect' => 'Prospect',
  'Suspect' => 'Suspect',
  'Dead' => 'Dead'
];

// 1️⃣ Get total agents
$total_agents = (int)$pdo->query("SELECT COUNT(*) FROM people")->fetchColumn();

// 2️⃣ Get settings values for each status
$placeholders = implode(',', array_fill(0, count($statuses), '?'));
$stmt = $pdo->prepare("SELECT name, value FROM settings WHERE name IN ($placeholders)");
$stmt->execute(array_keys($statuses));
$settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// 3️⃣ Get daily_followup grouped by status
$sql = "SELECT status_followup, COUNT(*) AS total, SUM(count_of) as due_today, 
               SUM(reminder_done) AS reminder_done, 
               SUM(note_done) AS note_done
        FROM daily_followup
        WHERE date_followup = :date_followup
        GROUP BY status_followup";
$stmt = $pdo->prepare($sql);
$stmt->execute([':date_followup' => $fdate]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$byStatus = [];
foreach ($rows as $r) $byStatus[$r['status_followup']] = $r;

// 4️⃣ Build Coverage Data
$coverageData = [];
$totalperuser = 0;
foreach ($statuses as $s) {
  $setting_val = (int)($settings[$s] ?? 0);
  $totalperuser += $setting_val;
  $expected = $total_agents * $setting_val;
  $attempted = (int)($byStatus[$s]['total'] ?? 0);
  $due = $expected-$attempted; //(int)($byStatus[$s]['due_today'] ?? 0); //$setting_val;
  $pct = $expected > 0 ? round(($attempted / $expected) * 100) : 0;

  $coverageData[] = [
    'status' => $s,
    'total' => $expected,
    'due' => $due,
    'attempted' => $attempted,
    'pct' => $pct
  ];
}

// 5️⃣ Staff Leaderboard
$sql = "SELECT p.name,
               (SELECT count(*) FROM customers where agent_id=p.id) AS customers_owned,
               SUM(f.reminder_done) AS reminders,
               SUM(f.note_done) AS notes
        FROM people p
        LEFT JOIN daily_followup f 
          ON p.id = f.agent_id AND f.date_followup = :date_followup
        GROUP BY p.id
        ORDER BY reminders DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([':date_followup' => $fdate]);
$staff = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

    <!-- Filters -->
    <div class="bg-white border rounded-3 p-4 mb-4 shadow-sm" style="display: none;">
      <form class="row g-3 justify-content-end">
        <!-- <div class="col-md-6">
          <input type="text" class="form-control" placeholder="Search contact, company, email, phone…">
        </div> -->
        <div class="col-md-3">
            <input type="hidden" name="page" value="dashboard">
            <input type="date" name="date" class="form-control" value="<?=$fdate?>">
        </div>
        <div class="col-md-3">
          <button class="btn btn-primary w-100">Apply Filters</button>
        </div>
      </form>
    </div>

    <!-- Coverage by Status -->
    <div class="bg-white border rounded-3 shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center border-bottom px-4 py-3">
      <h6 class="text-uppercase text-secondary mb-0">
        Coverage by Status (Today<?php //echo date('l, d M Y', strtotime($fdate)) ?>)
      </h6>
    </div>
    <div class="table-responsive p-4">
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Status</th>
            <th>Total</th>
            <th>Due Today</th>
            <th>Attempted Today</th>
            <th>Coverage %</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($coverageData as $r): 
            $color = $r['pct'] >= 70 ? 'bg-success' : ($r['pct'] >= 40 ? 'bg-warning' : 'bg-danger');
          ?>
          <tr>
            <td><?= htmlspecialchars($r['status']) ?></td>
            <td><?= $r['total'] ?></td>
            <td><?= $r['due'] ?></td>
            <td><?= $r['attempted'] ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height:6px;">
                  <div class="progress-bar <?= $color ?>" style="width: <?= $r['pct'] ?>%;"></div>
                </div>
                <small class="text-muted"><?= $r['pct'] ?>%</small>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    </div>

    <!-- Staff Leaderboard -->
    <div class="bg-white border rounded-3 shadow-sm mb-4">
    <div class="d-flex justify-content-between align-items-center border-bottom px-4 py-3">
      <h6 class="text-uppercase text-secondary mb-0">Staff Leaderboard (Today<?php //echo date('l, d M Y', strtotime($fdate)) ?>)</h6>
    </div>
    <div class="table-responsive p-4">
      <table class="table table-sm align-middle mb-0">
        <thead class="table-light">
          <tr>
            <th>Staff</th>
            <th>customers Owned</th>
            <th>Attempted Today</th>
            <th>Notes Today</th>
            <th>Coverage %</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($staff as $u):
            $coverage = $totalperuser > 0 ? round(($u['reminders'] / $totalperuser) * 100) : 0;
            $color = $coverage >= 70 ? 'bg-success' : ($coverage >= 40 ? 'bg-info' : 'bg-warning');
          ?>
          <tr>
            <td><?= htmlspecialchars($u['name']) ?></td>
            <td><?= (int)$u['customers_owned'] ?></td>
            <td><?= (int)$u['reminders'] ?></td>
            <td><?= (int)$u['notes'] ?></td>
            <td>
              <div class="d-flex align-items-center gap-2">
                <div class="progress flex-grow-1" style="height:6px;">
                  <div class="progress-bar <?= $color ?>" style="width: <?= $coverage ?>%;"></div>
                </div>
                <small class="text-muted"><?= $coverage ?>%</small>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    </div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>