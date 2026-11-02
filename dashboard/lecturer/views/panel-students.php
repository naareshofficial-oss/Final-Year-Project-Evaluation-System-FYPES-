<?php
$column=$panelRole==='report_panel'?'report_panel_id':'presentation_panel_id';
$students=safeRun($pdo,"SELECT s.*,u.full_name,u.email,u.phone,su.full_name supervisor_name
 FROM students s
 JOIN users u ON u.id=s.user_id
 LEFT JOIN lecturers sl ON sl.id=s.supervisor_id
 LEFT JOIN users su ON su.id=sl.user_id
 WHERE s.$column=? AND s.semester_id=?
 ORDER BY u.full_name",[$lecturerId,$currentSemesterId])->fetchAll();
?>
<div class="row g-3">
<?php foreach($students as $s): ?>
<div class="col-md-6"><div class="card-fy h-100">
  <div class="d-flex justify-content-between gap-3 align-items-start">
    <div>
      <div class="section-title mb-1"><?= e($s['full_name']) ?></div>
      <div class="small text-muted"><?= e($s['matric_number']) ?> · <?= e(programmeTypeLabel($s['programme_type'])) ?></div>
    </div>
    <span class="badge-fy badge-info"><?= e(roleLabel($panelRole)) ?></span>
  </div>
  <hr>
  <div class="small mb-2"><strong>Project title</strong><div class="text-muted mt-1"><?= e($s['project_title']?:'No project title') ?></div></div>
  <div class="small mb-1"><strong>Supervisor:</strong> <?= e($s['supervisor_name']?drName($s['supervisor_name']):'Not assigned') ?></div>
  <div class="small mb-1"><strong>Email:</strong> <a href="mailto:<?= e($s['email']) ?>"><?= e($s['email']) ?></a></div>
  <div class="small mb-1"><strong>Phone:</strong> <?= e($s['phone']?:'Not provided') ?></div>
  <div class="small mb-1"><strong>Panel date:</strong> <?= $s['presentation_date']?e(date('M j, Y',strtotime($s['presentation_date']))):'Not scheduled' ?></div>
  <div class="small"><strong>Status:</strong> <?= e(ucwords(str_replace('_',' ',$s['presentation_status']))) ?></div>
</div></div>
<?php endforeach; ?>
<?php if(!$students): ?><div class="col-12"><div class="card-fy text-center py-5 text-muted">No students assigned to this panel.</div></div><?php endif; ?>
</div>
