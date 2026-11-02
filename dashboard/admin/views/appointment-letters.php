<?php
$coordinators = safeRun($pdo, "SELECT lr.lecturer_id,lr.programme_type,u.full_name,u.email,
        al.id letter_id,al.letter_code,al.generated_at
    FROM lecturer_roles lr
    JOIN lecturers l ON l.id=lr.lecturer_id
    JOIN users u ON u.id=l.user_id
    LEFT JOIN appointment_letters al ON al.lecturer_id=lr.lecturer_id
        AND al.programme_type=lr.programme_type AND al.role_type='coordinator' AND al.semester_id=lr.semester_id
    WHERE lr.role_type='coordinator' AND lr.semester_id=? AND u.status='active'
    ORDER BY FIELD(lr.programme_type,'diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2'),u.full_name",[$currentSemesterId])->fetchAll();

$letters = safeRun($pdo, "SELECT al.*,u.full_name,u.email
    FROM appointment_letters al
    JOIN lecturers l ON l.id=al.lecturer_id
    JOIN users u ON u.id=l.user_id
    WHERE al.role_type='coordinator' AND al.semester_id=?
    ORDER BY al.generated_at DESC,al.id DESC",[$currentSemesterId])->fetchAll();
?>
<?php if (($_GET['issued'] ?? '') === '1'): ?>
  <div class="alert alert-success small">Coordinator Appointment Letter issued successfully and is ready to view as a PDF.</div>
<?php endif; ?>

<div class="card-fy mb-3">
  <div class="section-title">Coordinator Appointment Letters</div>
  <p class="small text-muted">Admin can issue or reissue an Appointment Letter only to a lecturer who currently holds a Coordinator role. A newly appointed replacement coordinator will appear here automatically.</p>
  <div class="table-responsive">
    <table class="table-fy">
      <thead><tr><th>Coordinator</th><th>Programme</th><th>Letter Type</th><th>Status</th><th>Action</th></tr></thead>
      <tbody>
      <?php foreach ($coordinators as $row): ?>
        <tr>
          <td><strong><?= e(drName($row['full_name'])) ?></strong><div class="small text-muted"><?= e($row['email']) ?></div></td>
          <td><?= e(programmeTypeLabel($row['programme_type'])) ?></td>
          <td><span class="badge-fy badge-info">Appointment Letter</span></td>
          <td>
            <?php if ($row['letter_id']): ?>
              <span class="badge-fy badge-success">Issued</span>
              <div class="small text-muted"><?= e(date('M j, Y',strtotime($row['generated_at']))) ?></div>
            <?php else: ?><span class="badge-fy badge-warning">Not issued</span><?php endif; ?>
          </td>
          <td>
            <form method="post" class="d-inline" onsubmit="return confirm('Issue or reissue this coordinator appointment letter?')">
              <?= csrfField() ?>
              <input type="hidden" name="action" value="generate_letter">
              <input type="hidden" name="target_lecturer_id" value="<?= (int)$row['lecturer_id'] ?>">
              <input type="hidden" name="programme_type" value="<?= e($row['programme_type']) ?>">
              <input type="hidden" name="letter_role" value="coordinator">
              <input type="hidden" name="letter_type" value="appointment">
              <button class="btn-fy-orange btn-sm"><?= $row['letter_id'] ? 'Reissue' : 'Issue' ?></button>
            </form>
            <?php if ($row['letter_id']): ?><a target="_blank" class="btn-fy-outline btn-sm" href="<?= e(secureFileUrl((int)$row['letter_id'],'appointment_letter')) ?>">View PDF</a><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$coordinators): ?><tr><td colspan="5" class="text-center text-muted py-4">No active coordinator assignments are available.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card-fy">
  <div class="section-title">Issued Coordinator Appointment Letters</div>
  <div class="table-responsive"><table class="table-fy">
    <thead><tr><th>Reference</th><th>Coordinator</th><th>Programme</th><th>Issued</th><th>PDF</th></tr></thead>
    <tbody>
    <?php foreach ($letters as $letter): ?>
      <tr>
        <td><code><?= e($letter['letter_code']) ?></code></td>
        <td><?= e(drName($letter['full_name'])) ?><div class="small text-muted"><?= e($letter['email']) ?></div></td>
        <td><?= e(programmeTypeLabel($letter['programme_type'])) ?></td>
        <td><?= e(date('M j, Y',strtotime($letter['generated_at']))) ?></td>
        <td><a target="_blank" class="btn-fy-outline btn-sm" href="<?= e(secureFileUrl((int)$letter['id'],'appointment_letter')) ?>">View PDF</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$letters): ?><tr><td colspan="5" class="text-center text-muted py-4">No coordinator appointment letters have been issued.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
