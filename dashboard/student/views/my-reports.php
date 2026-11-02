<?php
$error=$_GET['error']??null;
$reports=safeRun($pdo,'SELECT * FROM student_reports WHERE student_id=? AND semester_id<=>? ORDER BY submitted_at DESC',[$studentId,$studentSemesterId])->fetchAll();
?>
<?php if($error): ?><div class="alert alert-danger small">The report could not be submitted. Check the assignment, file type and file size.</div><?php endif; ?>
<div class="row g-3 mb-3">
<?php foreach ([['supervisor','Supervisor','supervisor_id'],['report_panel','Report Panel','report_panel_id']] as [$role,$label,$column]): ?>
<div class="col-md-6"><div class="card-fy h-100"><div class="section-title">Submit to <?= e($label) ?></div>
<?php if(empty($student[$column])): ?><p class="small text-muted mb-0"><?= e($label) ?> is not assigned yet.</p>
<?php else: ?><form method="POST" enctype="multipart/form-data" class="d-flex gap-2"><input type="hidden" name="action" value="submit_report"><input type="hidden" name="target_role" value="<?= $role ?>"><?= csrfField() ?><input type="file" name="file" class="form-control-fy flex-grow-1" required><button class="btn-fy-orange"><i class="fa-solid fa-upload"></i></button></form><?php endif; ?>
</div></div>
<?php endforeach; ?>
</div>
<div class="card-fy"><div class="section-title">Submitted Reports</div><table class="table-fy"><thead><tr><th>Recipient</th><th>File</th><th>Status</th><th>Marks</th><th>Remarks</th><th></th></tr></thead><tbody>
<?php foreach($reports as $r): ?><tr><td><?= roleLabel($r['target_role']) ?></td><td class="small"><?= e($r['file_name']) ?></td><td><span class="badge-fy badge-<?= statusBadgeColor($r['status']) ?>"><?= ucfirst($r['status']) ?></span></td><td><?= $r['marks']!==null?e((string)$r['marks']):'—' ?></td><td class="small"><?= e($r['remarks']?:'—') ?></td><td><a class="btn-fy-outline btn-sm" target="_blank" href="<?= secureFileUrl((int)$r['id'],'student_report') ?>">View</a></td></tr><?php endforeach; ?>
<?php if(empty($reports)): ?><tr><td colspan="6" class="text-center text-muted py-4">No reports submitted yet.</td></tr><?php endif; ?>
</tbody></table></div>
