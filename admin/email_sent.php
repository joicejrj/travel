<?php
require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/../config/db.php';
$q = $mysqli->query("SELECT * FROM email_log WHERE folder='Sent' OR is_sent=1 ORDER BY created_at DESC LIMIT 200");
?>
<div class="container">
  <h1 class="h3 mb-3">Sent Messages</h1>
  <table class="table table-sm">
    <thead><tr><th>ID</th><th>Subject</th><th>To</th><th>From</th><th>Date</th><th>View</th></tr></thead>
    <tbody>
    <?php while($r = $q->fetch_assoc()): ?>
      <tr>
        <td><?=htmlspecialchars($r['id'])?></td>
        <td><?=htmlspecialchars(substr($r['subject'],0,80))?></td>
        <td><?=htmlspecialchars($r['to_emails'])?></td>
        <td><?=htmlspecialchars($r['from_email'])?></td>
        <td><?=htmlspecialchars($r['created_at'])?></td>
        <td><a href="email_view.php?id=<?=urlencode($r['id'])?>">View</a></td>
      </tr>
    <?php endwhile; ?>
    </tbody>
  </table>
</div>
