<?php
$reports = safeRun($pdo, "SELECT sr.*, s.matric_number, u.full_name FROM student_reports sr
    JOIN students s ON s.id=sr.student_id JOIN users u ON u.id=s.user_id
    WHERE sr.target_role='supervisor' AND s.supervisor_id=? AND sr.semester_id=? AND s.semester_id=?
    ORDER BY sr.status='submitted' DESC, sr.submitted_at DESC", [$lecturerId,$currentSemesterId,$currentSemesterId])->fetchAll();
?>
<div class="card-fy">
  <div class="section-title">Reports Submitted by Your Supervisees</div>
  <?php foreach ($reports as $r): ?>
    <div class="py-3 border-bottom">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div>
          <div class="fw-semibold small"><?= e($r['full_name']) ?> <span class="text-muted">(<?= e($r['matric_number']) ?>)</span></div>
          <a href="<?= e(secureFileUrl((int)$r['id'],'student_report')) ?>" target="_blank" class="small" style="color:var(--unisel-orange);"><i class="fa-solid fa-file-lines me-1"></i><?= e($r['file_name']) ?></a>
          <div class="small text-muted"><?= timeAgo($r['submitted_at']) ?></div>
        </div>
        <span class="badge-fy badge-<?= $r['status']==='reviewed'?'success':'warning' ?>"><?= ucfirst($r['status']) ?></span>
      </div>
      <form method="POST" class="row g-2 mt-2 align-items-end">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="review_report">
        <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
        <input type="hidden" name="back_to" value="supervision-reports">
        <div class="col-md-3">
          <label class="form-label small fw-semibold">Marks</label>
          <input type="number" step="0.5" min="0" max="100" name="marks" class="form-control-fy w-100" value="<?= e($r['marks'] ?? '') ?>">
        </div>
        <div class="col-md-7">
          <label class="form-label small fw-semibold">Remarks</label>
          <input type="text" name="remarks" class="form-control-fy w-100" value="<?= e($r['remarks'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <button class="btn-fy-orange w-100">Save</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (empty($reports)): ?><p class="text-muted small mb-0 py-3">No reports submitted yet.</p><?php endif; ?>
</div>
