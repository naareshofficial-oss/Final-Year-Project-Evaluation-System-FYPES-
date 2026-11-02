<?php
$ph = implode(',', array_fill(0, count($coordProgrammeTypes), '?'));
$pendingOversight = count($coordProgrammeTypes) ? safeRun($pdo, "SELECT sr.*, s.matric_number, s.programme_type, s.project_title,
        u.full_name AS student_name, lu.full_name AS supervisor_name
    FROM supervisor_requests sr
    JOIN students s ON s.id=sr.student_id
    JOIN users u ON u.id=s.user_id
    JOIN lecturers l ON l.id=sr.supervisor_id
    JOIN users lu ON lu.id=l.user_id
    WHERE sr.status='accepted' AND sr.coordinator_status='pending'
      AND sr.semester_id=? AND s.semester_id=? AND s.programme_type IN ($ph)
    ORDER BY sr.responded_at DESC, sr.id DESC", array_merge([$currentSemesterId,$currentSemesterId],$coordProgrammeTypes))->fetchAll() : [];
$confirmed = count($coordProgrammeTypes) ? safeRun($pdo, "SELECT sr.*, s.matric_number, s.programme_type, s.project_title,
        u.full_name AS student_name, lu.full_name AS supervisor_name
    FROM supervisor_requests sr
    JOIN students s ON s.id=sr.student_id
    JOIN users u ON u.id=s.user_id
    JOIN lecturers l ON l.id=sr.supervisor_id
    JOIN users lu ON lu.id=l.user_id
    WHERE sr.coordinator_status='confirmed' AND sr.semester_id=? AND s.semester_id=?
      AND s.programme_type IN ($ph)
    ORDER BY sr.coordinator_decided_at DESC, sr.id DESC LIMIT 100", array_merge([$currentSemesterId,$currentSemesterId],$coordProgrammeTypes))->fetchAll() : [];
?>
<?php if(($_GET['supervision']??'')==='confirmed'): ?><div class="alert alert-success small">Supervisor confirmed successfully.</div><?php endif; ?>
<?php if(($_GET['supervision']??'')==='overridden'): ?><div class="alert alert-success small">Supervisor reassigned and confirmed successfully.</div><?php endif; ?>
<?php if(isset($_GET['supervision_error'])): ?><div class="alert alert-danger small"><?= e(match($_GET['supervision_error']){'capacity'=>'The selected supervisor has reached 10 supervisees.','rubric'=>'A published Supervisor or Coordinator evaluation rubric is missing for this programme.','locked'=>'The completed supervisor evaluation prevents reassignment.','invalid'=>'The request or selected supervisor is no longer valid.',default=>'The supervision action could not be completed.'}) ?></div><?php endif; ?>
<div class="card-fy mb-3">
  <div class="section-title">Awaiting Your Confirmation (<?= count($pendingOversight) ?>)</div>
  <p class="small text-muted mb-3">The lecturer has accepted the request. Confirm to finalize the assignment, or choose Override to assign another eligible supervisor.</p>
  <?php foreach ($pendingOversight as $r):
    $altSupervisors = safeRun($pdo, "SELECT l.id,u.full_name,l.department
        FROM lecturer_roles lr JOIN lecturers l ON l.id=lr.lecturer_id JOIN users u ON u.id=l.user_id
        WHERE lr.role_type='supervisor' AND lr.programme_type=? AND lr.semester_id=? AND l.id<>? AND u.status='active'
        GROUP BY l.id ORDER BY u.full_name", [$r['programme_type'],$currentSemesterId,$r['supervisor_id']])->fetchAll();
    foreach($altSupervisors as &$alt){$alt['load']=supervisorCurrentLoad($pdo,(int)$alt['id'],$currentSemesterId);} unset($alt);
  ?>
    <div class="py-3 border-bottom">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
        <div class="flex-grow-1">
          <div class="fw-semibold"><?= e($r['student_name']) ?> <span class="text-muted small">(<?= e($r['matric_number']) ?>)</span></div>
          <div class="small mt-1"><strong>Project:</strong> <?= e($r['project_title'] ?: 'No project title') ?></div>
          <div class="small text-muted mt-1"><?= e(programmeTypeLabel($r['programme_type'])) ?> · Accepted by <?= e(drName($r['supervisor_name'])) ?></div>
        </div>
        <div class="d-flex gap-2 flex-wrap">
          <form method="post" onsubmit="return confirm('Confirm this supervisor assignment?')">
            <?= csrfField() ?><input type="hidden" name="action" value="confirm_supervision"><input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
            <button type="submit" class="btn-fy-orange btn-sm">Confirm</button>
          </form>
          <button type="button" class="btn-fy-outline btn-sm" data-bs-toggle="modal" data-bs-target="#overrideModal<?= (int)$r['id'] ?>">Override</button>
        </div>
      </div>
    </div>
    <div class="modal fade" id="overrideModal<?= (int)$r['id'] ?>" tabindex="-1">
      <div class="modal-dialog"><div class="modal-content" style="border-radius:18px;">
        <form method="post">
          <?= csrfField() ?><input type="hidden" name="action" value="override_supervision"><input type="hidden" name="request_id" value="<?= (int)$r['id'] ?>">
          <div class="modal-header"><h6 class="modal-title fw-bold">Reassign Supervisor</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
          <div class="modal-body">
            <div class="small mb-3"><strong><?= e($r['student_name']) ?></strong><br><?= e($r['project_title'] ?: 'No project title') ?></div>
            <label class="form-label small fw-semibold">New Supervisor</label>
            <select name="new_supervisor_id" class="form-control-fy w-100 mb-3" required>
              <option value="">Choose an eligible supervisor</option>
              <?php foreach ($altSupervisors as $a): if((int)$a['load']>=10)continue; ?><option value="<?= (int)$a['id'] ?>"><?= e(drName($a['full_name'])) ?> — <?= (int)$a['load'] ?>/10</option><?php endforeach; ?>
            </select>
            <label class="form-label small fw-semibold">Reason</label>
            <textarea class="form-control-fy w-100" name="coordinator_note" rows="3" required placeholder="Why is this being reassigned?"></textarea>
          </div>
          <div class="modal-footer"><button type="submit" class="btn-fy-orange w-100">Reassign &amp; Confirm</button></div>
        </form>
      </div></div>
    </div>
  <?php endforeach; ?>
  <?php if (empty($pendingOversight)): ?><p class="text-muted small mb-0 py-3">Nothing is awaiting confirmation.</p><?php endif; ?>
</div>
<div class="card-fy">
  <div class="section-title">Confirmed Supervision</div>
  <div class="table-responsive"><table class="table-fy"><thead><tr><th>Student</th><th>Current Project Title</th><th>Programme</th><th>Supervisor</th><th>Status</th></tr></thead><tbody>
  <?php foreach ($confirmed as $c): ?><tr><td><strong><?= e($c['student_name']) ?></strong><div class="small text-muted"><?= e($c['matric_number']) ?></div></td><td><?= e($c['project_title'] ?: 'No project title') ?></td><td><?= e(programmeTypeLabel($c['programme_type'])) ?></td><td><?= e(drName($c['supervisor_name'])) ?></td><td><span class="badge-fy badge-success">Confirmed</span></td></tr><?php endforeach; ?>
  <?php if (!$confirmed): ?><tr><td colspan="5" class="text-center text-muted py-4">No confirmed assignments yet.</td></tr><?php endif; ?>
  </tbody></table></div>
</div>
