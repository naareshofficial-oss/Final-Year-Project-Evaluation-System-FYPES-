<?php
$logs = safeRun($pdo, "SELECT al.*, u.full_name, u.role FROM audit_logs al LEFT JOIN users u ON u.id=al.user_id ORDER BY al.created_at DESC LIMIT 1000")->fetchAll();
?>
<div class="card-fy">
  <div class="section-title">Audit Logs (latest 1000)</div>
  <div style="max-height:600px;overflow-y:auto;">
  <table class="table-fy">
    <thead><tr><th>User</th><th>Action</th><th>Details</th><th>IP</th><th>Time</th></tr></thead>
    <tbody>
    <?php foreach ($logs as $l): ?>
      <tr>
        <td class="small"><?= e($l['full_name'] ?? 'System') ?></td>
        <td><span class="badge-fy badge-secondary"><?= e($l['action']) ?></span></td>
        <td class="small text-muted"><?= e($l['details']) ?></td>
        <td class="small text-muted"><?= e($l['ip_address']) ?></td>
        <td class="small text-muted"><?= timeAgo($l['created_at']) ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
</div>
