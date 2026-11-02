<?php
$filterRole=$rubricRoleFilter??($_GET['role_type']??null);
$allowedRoles=['supervisor','report_panel','presentation_panel','coordinator'];
if($filterRole&&!in_array($filterRole,$allowedRoles,true))$filterRole=null;
$myRubricRoles=array_values(array_filter($roles,fn($r)=>in_array($r['role_type'],$allowedRoles,true)&&(!$filterRole||$r['role_type']===$filterRole)));
?>
<div class="row g-3">
<?php foreach($myRubricRoles as $r):
$eval=safeRun($pdo,'SELECT * FROM rubrics WHERE programme_type=? AND evaluator_role=? AND is_locked=1 ORDER BY version DESC LIMIT 1',[$r['programme_type'],$r['role_type']])->fetch();
$evalCriteria=$eval?safeRun($pdo,'SELECT * FROM rubric_criteria WHERE rubric_id=? ORDER BY sort_order,id',[$eval['id']])->fetchAll():[];
$detailed=latestPublishedDetailedRubric($pdo,$r['programme_type'],$r['role_type']);
$detailedCriteria=$detailed?safeRun($pdo,'SELECT * FROM detailed_rubric_criteria WHERE detailed_rubric_id=? ORDER BY sort_order,id',[$detailed['id']])->fetchAll():[];
?>
<div class="col-12"><div class="card-fy"><div class="d-flex justify-content-between align-items-start gap-2 flex-wrap"><div><div class="section-title mb-1"><?= roleLabel($r['role_type']) ?> Rubric Criteria</div><div class="small text-muted"><?= e(programmeTypeLabel($r['programme_type'])) ?></div></div></div>
<div class="row g-3 mt-1"><div class="col-lg-6"><div class="border rounded-3 p-3 h-100"><div class="d-flex justify-content-between gap-2"><strong>Evaluation Rubric</strong></div><?php if($eval): ?><div class="small text-muted mb-2"><?= e($eval['name']) ?></div><table class="table-fy"><thead><tr><th>Criterion</th><th>Weighted Marks</th></tr></thead><tbody><?php foreach($evalCriteria as $c): ?><tr><td><?= e($c['criteria_name']) ?></td><td><?= e((string)$c['weight']) ?></td></tr><?php endforeach; ?></tbody></table><?php else: ?><p class="small text-muted mt-3 mb-0">No published evaluation rubric.</p><?php endif; ?></div></div>
<div class="col-lg-6"><div class="border rounded-3 p-3 h-100"><div class="d-flex justify-content-between gap-2"><strong>Detailed Rubric</strong></div><?php if($detailed): ?><div class="small text-muted mb-2"><?= e($detailed['title']) ?> · Total marks <?= e((string)detailedRubricTotal($pdo,(int)$detailed['id'])) ?></div><table class="table-fy"><thead><tr><th>Criterion</th><th>Maximum</th></tr></thead><tbody><?php foreach($detailedCriteria as $c): ?><tr><td><strong class="small"><?= e($c['criterion_name']) ?></strong><div class="small text-muted"><?= e($c['description']??'') ?></div></td><td><?= e((string)(float)$c['max_marks']) ?>/10</td></tr><?php endforeach; ?></tbody></table><?php else: ?><p class="small text-muted mt-3 mb-0">No published detailed rubric.</p><?php endif; ?></div></div></div>
</div></div>
<?php endforeach; ?>
<?php if(!$myRubricRoles): ?><div class="col-12"><div class="card-fy text-center text-muted py-5">No rubric is available for this role.</div></div><?php endif; ?>
</div>
