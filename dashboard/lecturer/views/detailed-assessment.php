<?php
/**
 * Staff-only detailed assessment form.
 * Required variables: $selected (evaluation row), $lecturerId, $currentSemesterId, $backTo.
 */
$detailEvaluationId=(int)($selected['id']??0);
$detailContext=$detailEvaluationId?safeRun($pdo,"SELECT ev.id,ev.student_id,ev.evaluator_role,ev.evaluator_id,ev.semester_id,s.programme_type,s.presentation_status FROM evaluations ev JOIN students s ON s.id=ev.student_id WHERE ev.id=? AND ev.evaluator_id=? AND ev.semester_id=? AND s.semester_id=ev.semester_id",[$detailEvaluationId,$lecturerId,$currentSemesterId])->fetch():null;
$detailAssessment=$detailContext?safeRun($pdo,'SELECT da.*,dr.title rubric_title,dr.version rubric_version,dr.status rubric_status FROM detailed_assessments da JOIN detailed_rubrics dr ON dr.id=da.detailed_rubric_id WHERE da.evaluation_id=?',[$detailEvaluationId])->fetch():null;
$detailRubric=null;
if($detailAssessment){
 $detailRubric=safeRun($pdo,'SELECT * FROM detailed_rubrics WHERE id=?',[$detailAssessment['detailed_rubric_id']])->fetch();
}elseif($detailContext){
 $detailRubric=latestPublishedDetailedRubric($pdo,$detailContext['programme_type'],$detailContext['evaluator_role']);
}
$detailCriteria=$detailRubric?safeRun($pdo,'SELECT * FROM detailed_rubric_criteria WHERE detailed_rubric_id=? ORDER BY sort_order,id',[$detailRubric['id']])->fetchAll():[];
$detailScoreMap=$detailAssessment?array_column(safeRun($pdo,'SELECT detailed_criterion_id,score FROM detailed_assessment_scores WHERE detailed_assessment_id=?',[$detailAssessment['id']])->fetchAll(),'score','detailed_criterion_id'):[];
$detailMaximum=$detailRubric?detailedRubricTotal($pdo,(int)$detailRubric['id']):0;
$detailSavedTotal=(float)($detailAssessment['total_score']??0);
$detailResultReleased=$detailContext?(bool)safeRun($pdo,"SELECT 1 FROM final_results WHERE student_id=? AND semester_id<=>? AND status='released' LIMIT 1",[$detailContext['student_id'],$detailContext['semester_id']])->fetch():false;
$detailPresentationReady=!$detailContext||!in_array($detailContext['evaluator_role'],['report_panel','presentation_panel'],true)||$detailContext['presentation_status']==='completed';
$detailCanEdit=$detailContext&&$detailRubric&&$detailCriteria&&!$detailResultReleased&&$detailPresentationReady;
$detailDomId='detailAssessment'.$detailEvaluationId;
?>
<div class="card-fy mt-3" id="<?= e($detailDomId) ?>">
 <?php if(($_GET['detail_saved']??'')==='1'): ?><div class="alert alert-success small">Detailed assessment saved successfully.</div><?php endif; ?>
 <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap">
  <div><div class="section-title mb-1">Staff-only Detailed Assessment</div><div class="small text-muted">This raw detailed score is visible only to assigned academic staff. Students see only the official released evaluation percentages.</div></div>
  <?php if($detailRubric): ?><div class="text-end"><div class="fw-bold mt-1"><span class="detail-live-total"><?= e(number_format($detailSavedTotal,2)) ?></span> / <?= e(number_format($detailMaximum,2)) ?></div></div><?php endif; ?>
 </div>
 <?php if(!$detailRubric): ?>
  <div class="alert alert-warning small mt-3 mb-0">No published <?= e(roleLabel($detailContext['evaluator_role']??'')) ?> detailed rubric is available for this programme.</div>
 <?php elseif(!$detailCriteria): ?>
  <div class="alert alert-warning small mt-3 mb-0">The published detailed rubric has no criteria.</div>
 <?php else: ?>
  <div class="small text-muted mt-2 mb-3"><?= e($detailRubric['title']) ?> · Each criterion is limited to 10 marks.</div>
  <form method="post" class="detail-assessment-form">
   <?= csrfField() ?><input type="hidden" name="action" value="submit_detailed_assessment"><input type="hidden" name="evaluation_id" value="<?= $detailEvaluationId ?>"><input type="hidden" name="detailed_rubric_id" value="<?= (int)$detailRubric['id'] ?>"><input type="hidden" name="back_to" value="<?= e($backTo) ?>">
   <?php foreach($detailCriteria as $dc): $max=(float)$dc['max_marks'];$value=isset($detailScoreMap[$dc['id']])?(float)$detailScoreMap[$dc['id']]:0; ?>
   <div class="border rounded-3 p-3 mb-2 detail-assessment-row">
    <div class="detail-assessment-copy"><strong class="small"><?= e($dc['criterion_name']) ?></strong><?php if(!empty($dc['description'])): ?><div class="small text-muted"><?= e($dc['description']) ?></div><?php endif; ?></div>
    <div class="detail-assessment-control"><div class="d-flex align-items-center gap-3"><input class="detail-score-range flex-grow-1" type="range" min="0" max="<?= e((string)$max) ?>" step="0.5" name="detailed_scores[<?= (int)$dc['id'] ?>]" value="<?= e((string)$value) ?>" <?= $detailCanEdit?'':'disabled' ?>><output class="detail-score-output fw-bold text-nowrap"><?= e(number_format($value,1)) ?></output></div><div class="small text-muted text-end">Maximum <?= e(number_format($max,1)) ?>/10</div></div>
   </div>
   <?php endforeach; ?>
   <label class="form-label small fw-semibold mt-2">Detailed assessment comments</label><textarea name="detailed_comments" rows="3" class="form-control-fy w-100 mb-3" <?= $detailCanEdit?'':'disabled' ?>><?= e($detailAssessment['comments']??'') ?></textarea>
   <?php if($detailResultReleased): ?><div class="alert alert-success mb-0">The final result is released, so this detailed assessment is read-only.</div>
   <?php elseif(!$detailPresentationReady): ?><div class="alert alert-warning mb-0">The panel session must be marked completed before Report Panel or Presentation Panel detailed marks can be saved.</div>
   <?php elseif($detailCanEdit): ?><button class="btn-fy-orange"><i class="fa-solid fa-floppy-disk me-1"></i><?= $detailAssessment?'Update':'Save' ?> Detailed Assessment</button>
   <?php endif; ?>
  </form>
 <?php endif; ?>
</div>
<style>.detail-assessment-row{display:grid;grid-template-columns:minmax(0,1fr) minmax(280px,42%);gap:24px;align-items:center}.detail-assessment-copy{min-width:0;overflow-wrap:anywhere}.detail-assessment-control{min-width:260px}@media(max-width:767px){.detail-assessment-row{grid-template-columns:1fr}.detail-assessment-control{min-width:0}}</style>
<script>
(function(){const root=document.getElementById(<?= json_encode($detailDomId) ?>);if(!root)return;const ranges=[...root.querySelectorAll('.detail-score-range')],total=root.querySelector('.detail-live-total');function refresh(){let sum=0;ranges.forEach(r=>{sum+=Number(r.value||0);const out=r.closest('.border')?.querySelector('.detail-score-output');if(out)out.textContent=Number(r.value||0).toFixed(1);});if(total)total.textContent=sum.toFixed(2);}ranges.forEach(r=>r.addEventListener('input',refresh));refresh();})();
</script>
