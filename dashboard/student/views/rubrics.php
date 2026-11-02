<?php
$roles=['coordinator','supervisor','report_panel','presentation_panel'];
$roleIcons=['coordinator'=>'user-check','supervisor'=>'user-tie','report_panel'=>'file-lines','presentation_panel'=>'person-chalkboard'];
?>
<div class="card-fy mb-3">
  <div class="section-title">Rubrics — <?= e(programmeTypeLabel($pt)) ?></div>
  <p class="small text-muted mb-0">These are the latest published evaluation and detailed rubrics for your active FYP stage. The page is read-only. Your awarded marks remain under Evaluation Results.</p>
</div>

<?php foreach($roles as $role):
$evaluation=safeRun($pdo,"SELECT * FROM rubrics WHERE programme_type=? AND evaluator_role=? AND is_locked=1 ORDER BY published_at DESC,version DESC,id DESC LIMIT 1",[$pt,$role])->fetch();
$evaluationCriteria=$evaluation?safeRun($pdo,'SELECT * FROM rubric_criteria WHERE rubric_id=? ORDER BY sort_order,id',[(int)$evaluation['id']])->fetchAll():[];
$detailed=latestPublishedDetailedRubric($pdo,$pt,$role);
$detailedCriteria=$detailed?safeRun($pdo,'SELECT * FROM detailed_rubric_criteria WHERE detailed_rubric_id=? ORDER BY sort_order,id',[(int)$detailed['id']])->fetchAll():[];
?>
<div class="card-fy mb-3">
  <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-3">
    <div><h6 class="fw-bold mb-1"><i class="fa-solid fa-<?= e($roleIcons[$role]) ?> me-2 text-warning"></i><?= e(roleLabel($role)) ?></h6><div class="small text-muted"><?= e(programmeTypeLabel($pt)) ?></div></div>
  </div>
  <div class="row g-3">
    <div class="col-lg-6">
      <div class="border rounded-3 p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2"><strong>Evaluation Rubric</strong></div>
        <?php if($evaluation): ?>
          <div class="small fw-semibold mb-2"><?= e($evaluation['name']) ?></div>
          <div class="table-responsive"><table class="table-fy"><thead><tr><th>Criterion</th><th class="text-end">Marks</th></tr></thead><tbody>
          <?php $evaluationTotal=0;foreach($evaluationCriteria as $criterion):$evaluationTotal+=(float)$criterion['weight']; ?><tr><td><?= e($criterion['criteria_name']) ?></td><td class="text-end"><?= e(number_format((float)$criterion['weight'],2)) ?></td></tr><?php endforeach; ?>
          <tr><td><strong>Total</strong></td><td class="text-end"><strong><?= e(number_format($evaluationTotal,2)) ?></strong></td></tr></tbody></table></div>
        <?php else: ?><p class="small text-muted mb-0">No evaluation rubric has been published for this role and stage.</p><?php endif; ?>
      </div>
    </div>
    <div class="col-lg-6">
      <div class="border rounded-3 p-3 h-100">
        <div class="d-flex justify-content-between align-items-center mb-2"><strong>Detailed Rubric</strong></div>
        <?php if($detailed): ?>
          <div class="small fw-semibold mb-2"><?= e($detailed['title']) ?></div>
          <div class="table-responsive"><table class="table-fy"><thead><tr><th>Criterion</th><th class="text-end">Max Marks</th></tr></thead><tbody>
          <?php $detailedTotal=0;foreach($detailedCriteria as $criterion):$detailedTotal+=(float)$criterion['max_marks']; ?><tr><td><strong><?= e($criterion['criterion_name']) ?></strong><?php if($criterion['description']): ?><div class="small text-muted"><?= e($criterion['description']) ?></div><?php endif; ?></td><td class="text-end"><?= e(number_format((float)$criterion['max_marks'],2)) ?></td></tr><?php endforeach; ?>
          <tr><td><strong>Total Marks</strong></td><td class="text-end"><strong><?= e(rtrim(rtrim(number_format($detailedTotal,2,'.',''),'0'),'.')) ?></strong></td></tr></tbody></table></div>
        <?php else: ?><p class="small text-muted mb-0">No detailed rubric has been published for this role and stage.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php endforeach; ?>
