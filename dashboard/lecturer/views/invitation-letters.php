<?php
$myLetters=safeRun($pdo,"SELECT al.* FROM appointment_letters al WHERE al.lecturer_id=? ORDER BY al.generated_at DESC",[$lecturerId])->fetchAll();
$issuanceRows=[];
if($isCoordinator&&$coordProgrammeTypes){
 $ph=implode(',',array_fill(0,count($coordProgrammeTypes),'?'));
 $issuanceRows=safeRun($pdo,"SELECT lr.lecturer_id,lr.role_type,lr.programme_type,u.full_name,u.email,
         al.id letter_id,al.letter_code,al.generated_at
  FROM lecturer_roles lr
  JOIN lecturers l ON l.id=lr.lecturer_id
  JOIN users u ON u.id=l.user_id
  LEFT JOIN appointment_letters al ON al.lecturer_id=lr.lecturer_id AND al.role_type=lr.role_type AND al.programme_type=lr.programme_type AND al.semester_id=lr.semester_id
  WHERE lr.programme_type IN ($ph) AND lr.semester_id=?
    AND lr.role_type IN ('supervisor','report_panel','presentation_panel')
    AND u.status='active'
  ORDER BY lr.programme_type,FIELD(lr.role_type,'supervisor','report_panel','presentation_panel'),u.full_name",array_merge($coordProgrammeTypes,[$currentSemesterId]))->fetchAll();
}
?>
<?php if(($_GET['issued']??'')==='1'): ?><div class="alert alert-success small">Letter issued successfully and is ready to view as a PDF.</div><?php endif; ?>

<div class="card-fy mb-3">
  <div class="section-title">My Letters</div>
  <p class="small text-muted">Appointment and invitation letters issued to you appear here.</p>
  <div class="table-responsive"><table class="table-fy">
    <thead><tr><th>Reference</th><th>Duty</th><th>Programme</th><th>Letter Type</th><th>Issued</th><th>PDF</th></tr></thead>
    <tbody>
    <?php foreach($myLetters as $letter): $letterType=letterTypeForRole($letter['role_type']); ?>
      <tr>
        <td><code><?= e($letter['letter_code']) ?></code></td>
        <td><?= e(roleLabel($letter['role_type'])) ?></td>
        <td><?= e(programmeTypeLabel($letter['programme_type'])) ?></td>
        <td><span class="badge-fy badge-info"><?= e(letterTypeLabel($letterType)) ?></span></td>
        <td><?= e(date('M j, Y',strtotime($letter['generated_at']))) ?></td>
        <td><a class="btn-fy-outline btn-sm" target="_blank" href="<?= e(secureFileUrl((int)$letter['id'],'appointment_letter')) ?>">View PDF</a></td>
      </tr>
    <?php endforeach; ?>
    <?php if(!$myLetters): ?><tr><td colspan="6" class="text-center text-muted py-5">No letter has been issued to your account.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>

<?php if($isCoordinator): ?>
<div class="card-fy">
  <div class="section-title">Generate Lecturer Letters</div>
  <p class="small text-muted">
    Supervisors receive an <strong>Appointment Letter</strong>. Report Panel and Presentation Panel members receive an <strong>Invitation Letter</strong>.
    You may issue letters only for programmes you coordinate and only to lecturers who already hold the relevant role.
  </p>
  <div class="table-responsive"><table class="table-fy">
    <thead><tr><th>Lecturer</th><th>Programme</th><th>Duty</th><th>Letter Type</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
    <?php foreach($issuanceRows as $row): $letterType=letterTypeForRole($row['role_type']); ?>
      <tr>
        <td><strong><?= e(drName($row['full_name'])) ?></strong><div class="small text-muted"><?= e($row['email']) ?></div></td>
        <td><?= e(programmeTypeLabel($row['programme_type'])) ?></td>
        <td><?= e(roleLabel($row['role_type'])) ?></td>
        <td><span class="badge-fy badge-info"><?= e(letterTypeLabel($letterType)) ?></span></td>
        <td>
          <?php if($row['letter_id']): ?><span class="badge-fy badge-success">Issued</span><div class="small text-muted"><?= e(date('M j, Y',strtotime($row['generated_at']))) ?></div>
          <?php else: ?><span class="badge-fy badge-warning">Not issued</span><?php endif; ?>
        </td>
        <td>
          <form method="post" class="d-inline" onsubmit="return confirm('Issue or reissue this <?= e(strtolower(letterTypeLabel($letterType))) ?>?')">
            <?= csrfField() ?>
            <input type="hidden" name="action" value="generate_role_invitation">
            <input type="hidden" name="target_lecturer_id" value="<?= (int)$row['lecturer_id'] ?>">
            <input type="hidden" name="programme_type" value="<?= e($row['programme_type']) ?>">
            <input type="hidden" name="letter_role" value="<?= e($row['role_type']) ?>">
            <button class="btn-fy-orange btn-sm"><?= $row['letter_id']?'Reissue':'Generate' ?></button>
          </form>
          <?php if($row['letter_id']): ?><a class="btn-fy-outline btn-sm" target="_blank" href="<?= e(secureFileUrl((int)$row['letter_id'],'appointment_letter')) ?>">View PDF</a><?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if(!$issuanceRows): ?><tr><td colspan="6" class="text-center text-muted py-4">No eligible lecturer roles are available.</td></tr><?php endif; ?>
    </tbody>
  </table></div>
</div>
<?php endif; ?>
