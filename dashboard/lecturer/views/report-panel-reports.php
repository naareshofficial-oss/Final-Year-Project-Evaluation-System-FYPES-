<?php
$reports=safeRun($pdo,"SELECT sr.*,u.full_name,s.matric_number,s.project_title FROM student_reports sr JOIN students s ON s.id=sr.student_id JOIN users u ON u.id=s.user_id WHERE sr.target_role='report_panel' AND s.report_panel_id=? AND sr.semester_id=? AND s.semester_id=? ORDER BY sr.submitted_at DESC",[$lecturerId,$currentSemesterId,$currentSemesterId])->fetchAll();
?>
<div class="card-fy"><div class="section-title">Reports Submitted to You</div>
<?php foreach($reports as $r): ?><div class="border-bottom py-3">
  <div class="d-flex justify-content-between gap-3"><div><strong><?= e($r['full_name']) ?></strong><div class="small text-muted"><?= e($r['matric_number']) ?> · <?= e($r['project_title']) ?></div></div><a class="btn-fy-outline btn-sm" href="<?= e(secureFileUrl((int)$r['id'],'student_report')) ?>" target="_blank">Open report</a></div>
  <form method="post" class="row g-2 align-items-end mt-1"><?= csrfField() ?><input type="hidden" name="action" value="review_report"><input type="hidden" name="report_id" value="<?= $r['id'] ?>"><input type="hidden" name="back_to" value="report-panel-reports">
    <div class="col-md-3"><label class="form-label small fw-semibold">Marks /100</label><input type="number" min="0" max="100" step="0.5" name="marks" class="form-control-fy w-100" value="<?= e($r['marks'] ?? '') ?>"></div>
    <div class="col-md-7"><label class="form-label small fw-semibold">Remarks</label><input name="remarks" class="form-control-fy w-100" value="<?= e($r['remarks'] ?? '') ?>"></div>
    <div class="col-md-2"><button class="btn-fy-orange w-100">Save</button></div>
  </form>
</div><?php endforeach; ?><?php if(!$reports): ?><p class="text-muted mb-0">No reports submitted to your panel.</p><?php endif; ?></div>
