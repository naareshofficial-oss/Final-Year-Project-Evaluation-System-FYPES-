<?php
$final=safeRun($pdo,"SELECT * FROM final_results WHERE student_id=? AND semester_id<=>? AND status='released' ORDER BY finalized_at DESC LIMIT 1",[$studentId,$studentSemesterId])->fetch();
$evals=safeRun($pdo,"SELECT ev.*,r.name rubric_name,u.full_name evaluator_name,
    (SELECT COALESCE(SUM(weight),0) FROM rubric_criteria WHERE rubric_id=r.id) max_score
    FROM evaluations ev JOIN rubrics r ON r.id=ev.rubric_id JOIN lecturers l ON l.id=ev.evaluator_id JOIN users u ON u.id=l.user_id
    WHERE ev.student_id=? AND ev.semester_id<=>? ORDER BY FIELD(ev.evaluator_role,'supervisor','report_panel','presentation_panel','coordinator')",[$studentId,$studentSemesterId])->fetchAll();
?>
<?php if($final): ?>
<div class="card-fy mb-3">
  <div class="d-flex align-items-center gap-4">
    <div class="user-avatar" style="width:72px;height:72px;font-size:20px;background:#DCFCE7;color:#16A34A;"><?= e(number_format((float)$final['final_score'],2)) ?></div>
    <div><div class="fw-bold">Final Released Result</div><div class="text-muted small">Grade <?= e($final['grade']) ?> · Released <?= e(date('M j, Y',strtotime($final['released_at']))) ?></div></div>
  </div>
</div>
<?php else: ?>
<div class="alert alert-light border small"><i class="fa-solid fa-lock me-1"></i>Your final result and component marks remain private until all four evaluations are completed, finalized, and released by the coordinator.</div>
<?php endif; ?>
<div class="row g-3">
<?php foreach($evals as $ev): ?>
<div class="col-lg-6"><div class="card-fy h-100">
  <div class="d-flex justify-content-between align-items-start mb-2">
    <div><div class="fw-bold small"><?= e($ev['rubric_name']) ?></div><div class="text-muted small">Evaluator: <?= e(drName($ev['evaluator_name'])) ?> · <?= e(roleLabel($ev['evaluator_role'])) ?></div></div>
    <span class="badge-fy badge-<?= e(statusBadgeColor($ev['status'])) ?>"><?= e(ucfirst($ev['status'])) ?></span>
  </div>
  <?php if($final && $ev['status']==='completed'): ?>
    <?php $crits=safeRun($pdo,'SELECT rc.criteria_name,rc.weight,es.score FROM rubric_criteria rc LEFT JOIN evaluation_scores es ON es.criteria_id=rc.id AND es.evaluation_id=? WHERE rc.rubric_id=? ORDER BY rc.sort_order,rc.id',[$ev['id'],$ev['rubric_id']])->fetchAll(); ?>
    <?php $componentPct=(float)$ev['total_score']; ?>
    <div class="h4 fw-bold mb-3"><?= e(rtrim(rtrim(number_format($componentPct,2,'.',''),'0'),'.')) ?><span class="fs-6 text-muted">%</span></div>
    <table class="table-fy"><thead><tr><th>Criterion</th><th>Percentage</th></tr></thead><tbody><?php foreach($crits as $c): $criterionPct=(float)($c['score']??0); ?><tr><td><?= e($c['criteria_name']) ?></td><td><?= e(rtrim(rtrim(number_format($criterionPct,2,'.',''),'0'),'.')) ?>%</td></tr><?php endforeach; ?></tbody></table>
    <?php if($ev['comments']): ?><p class="small text-muted mt-2 mb-0"><i class="fa-solid fa-comment me-1"></i><?= e($ev['comments']) ?></p><?php endif; ?>
  <?php elseif($ev['status']==='completed'): ?>
    <p class="text-muted small mt-3 mb-0"><i class="fa-solid fa-circle-check me-1"></i>This component is completed. Its marks will be shown after final release.</p>
  <?php else: ?>
    <p class="text-muted small mt-3 mb-0">This evaluation is pending.</p>
  <?php endif; ?>
</div></div>
<?php endforeach; ?>
<?php if(empty($evals)): ?><div class="col-12"><div class="card-fy text-center py-5 text-muted">No evaluations have been assigned yet.</div></div><?php endif; ?>
</div>
