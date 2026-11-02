<?php
require_once __DIR__ . '/../../includes/config.php';
requireRole('lecturer');
$user=currentUser();$lecturerId=(int)$user['lecturer_id'];
$roles=currentLecturerRoles($pdo);if(empty($roles))redirect(BASE_URL.'/dashboard/pending-role.php');
$roleTypes=array_unique(array_column($roles,'role_type'));
$isCoordinator=in_array('coordinator',$roleTypes,true);$isSupervisor=in_array('supervisor',$roleTypes,true);
$isReportPanel=in_array('report_panel',$roleTypes,true);$isPresentationPanel=in_array('presentation_panel',$roleTypes,true);
$coordProgrammeTypes=array_values(array_unique(array_column(array_filter($roles,fn($r)=>$r['role_type']==='coordinator'),'programme_type')));
$view=$_GET['view']??'overview';$currentSemesterId=currentSemesterId($pdo);

function lecturerRoleProgrammes(array $roles,string $role):array{return array_values(array_unique(array_column(array_filter($roles,fn($r)=>$r['role_type']===$role),'programme_type')));}
function coordinatorOwnsTarget(PDO $pdo,int $lecturerId,int $studentId):?array{$s=safeRun($pdo,'SELECT * FROM students WHERE id=?',[$studentId])->fetch();return $s&&((int)$s['semester_id']===(int)currentSemesterId($pdo))&&coordinatorCanManageProgramme($pdo,$lecturerId,$s['programme_type'])?$s:null;}

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!verifyCSRFToken($_POST['csrf_token']??null))redirect('?view='.urlencode($view).'&error=csrf');
 $action=$_POST['action']??'';

 if($action==='respond_request'&&$isSupervisor){
  $reqId=(int)($_POST['request_id']??0);$decision=$_POST['decision']??'';$note=trim($_POST['response_note']??'');
  if(in_array($decision,['accepted','rejected'],true)){
   $req=safeRun($pdo,"SELECT sr.*,s.programme_type,s.user_id FROM supervisor_requests sr JOIN students s ON s.id=sr.student_id WHERE sr.id=? AND sr.supervisor_id=? AND sr.status='pending' AND sr.semester_id=s.semester_id AND s.semester_id=?",[$reqId,$lecturerId,$currentSemesterId])->fetch();
   if($req&&lecturerHasProgrammeRole($pdo,$lecturerId,'supervisor',$req['programme_type'])){
    if($decision==='accepted'){
     if(supervisorCurrentLoad($pdo,$lecturerId,$currentSemesterId)>=10)redirect('?view=pending-requests&error=capacity');
    }
    safeRun($pdo,'UPDATE supervisor_requests SET status=?,response_note=?,responded_at=NOW() WHERE id=?',[$decision,$note,$reqId]);
    if($decision==='accepted'){
     $coords=safeRun($pdo,"SELECT l.user_id FROM lecturer_roles lr JOIN lecturers l ON l.id=lr.lecturer_id WHERE lr.role_type='coordinator' AND lr.programme_type=? AND lr.semester_id=?",[$req['programme_type'],$currentSemesterId])->fetchAll();
     foreach($coords as $c)notify($pdo,(int)$c['user_id'],'Supervision Awaiting Confirmation',$user['name'].' accepted a student request. Coordinator confirmation is required.','supervision','supervision-oversight');
    }
    notify($pdo,(int)$req['user_id'],$decision==='accepted'?'Supervisor Accepted Request':'Supervisor Declined Request',$decision==='accepted'?$user['name'].' accepted your request. It is awaiting coordinator confirmation.':$user['name'].' declined your request.'.($note?' Note: '.$note:''),'supervisor','my-supervisor');
    auditLog($pdo,$user['id'],'respond_supervisor_request',"Request #$reqId -> $decision");
   }
  }
  redirect('?view=pending-requests');
 }

 if($action==='submit_evaluation'){
  $evalId=(int)($_POST['evaluation_id']??0);$comments=trim($_POST['comments']??'');$scores=is_array($_POST['scores']??null)?$_POST['scores']:[];$allowedBack=['evaluate-supervision','evaluate-report-panel','evaluate-presentation-panel','evaluate-coordinator'];$backTo=in_array($_POST['back_to']??'', $allowedBack,true)?$_POST['back_to']:'overview';
  $ev=safeRun($pdo,"SELECT ev.*,s.programme_type,s.user_id,s.presentation_status,s.semester_id student_semester,r.evaluator_role rubric_role,r.programme_type rubric_programme,r.is_locked FROM evaluations ev JOIN students s ON s.id=ev.student_id JOIN rubrics r ON r.id=ev.rubric_id WHERE ev.id=? AND ev.evaluator_id=? AND ev.semester_id=? AND s.semester_id=ev.semester_id",[$evalId,$lecturerId,$currentSemesterId])->fetch();
  if($ev&&in_array($ev['status'],['pending','reopened'],true)&&$ev['rubric_role']===$ev['evaluator_role']&&$ev['rubric_programme']===$ev['programme_type']&&(int)$ev['is_locked']===1&&(!in_array($ev['evaluator_role'],['report_panel','presentation_panel'],true)||$ev['presentation_status']==='completed')&&lecturerHasProgrammeRole($pdo,$lecturerId,$ev['evaluator_role'],$ev['programme_type'])){
   $criteria=safeRun($pdo,'SELECT id,weight FROM rubric_criteria WHERE rubric_id=? ORDER BY id',[$ev['rubric_id']])->fetchAll();
   $expectedIds=array_map('intval',array_column($criteria,'id'));$submittedIds=array_map('intval',array_keys($scores));sort($expectedIds);sort($submittedIds);
   if($expectedIds!==$submittedIds)redirect('?view='.urlencode($backTo).'&eval_id='.$evalId.'&error=scores');
   $total=0;$validated=[];
   foreach($criteria as $c){$score=filter_var($scores[$c['id']],FILTER_VALIDATE_FLOAT);if($score===false||$score<0||$score>(float)$c['weight'])redirect('?view='.urlencode($backTo).'&eval_id='.$evalId.'&error=scores');$total+=(float)$score;$validated[]=[(int)$c['id'],(float)$score,calculatePercentage((float)$score,(float)$c['weight'])];}
   $pdo->beginTransaction();try{
    safeRun($pdo,'DELETE FROM evaluation_scores WHERE evaluation_id=?',[$evalId]);
    foreach($validated as [$cid,$score,$pct])safeRun($pdo,'INSERT INTO evaluation_scores (evaluation_id,criteria_id,score,percentage_score) VALUES (?,?,?,?)',[$evalId,$cid,$score,$pct]);
    safeRun($pdo,"UPDATE evaluations SET total_score=?,comments=?,status='completed',evaluated_at=NOW(),locked_at=NOW() WHERE id=? AND evaluator_id=?",[$total,$comments,$evalId,$lecturerId]);
    $pdo->commit();notify($pdo,(int)$ev['user_id'],roleLabel($ev['evaluator_role']).' Evaluation Completed','Your evaluation component has been submitted. Final marks remain hidden until coordinator release.','evaluation','results');auditLog($pdo,$user['id'],'submit_evaluation',"Evaluation #$evalId score=$total");
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();}
  }
  redirect('?view='.urlencode($backTo));
 }

 if($action==='submit_detailed_assessment'){
  $evalId=(int)($_POST['evaluation_id']??0);$rubricId=(int)($_POST['detailed_rubric_id']??0);$comments=trim($_POST['detailed_comments']??'');$scores=is_array($_POST['detailed_scores']??null)?$_POST['detailed_scores']:[];
  $allowedBack=['evaluate-supervision','evaluate-report-panel','evaluate-presentation-panel','evaluate-coordinator'];$backTo=in_array($_POST['back_to']??'', $allowedBack,true)?$_POST['back_to']:'overview';
  $ev=safeRun($pdo,"SELECT ev.*,s.programme_type,s.presentation_status,s.semester_id student_semester FROM evaluations ev JOIN students s ON s.id=ev.student_id WHERE ev.id=? AND ev.evaluator_id=? AND ev.semester_id=? AND s.semester_id=ev.semester_id",[$evalId,$lecturerId,$currentSemesterId])->fetch();
  $existing=$ev?safeRun($pdo,'SELECT id,detailed_rubric_id FROM detailed_assessments WHERE evaluation_id=?',[$evalId])->fetch():null;
  $rubric=$ev?safeRun($pdo,"SELECT * FROM detailed_rubrics WHERE id=? AND programme_type=? AND evaluator_role=? AND (status='published' OR id=?)",[$rubricId,$ev['programme_type'],$ev['evaluator_role'],(int)($existing['detailed_rubric_id']??0)])->fetch():null;
  $released=$ev?(bool)safeRun($pdo,"SELECT 1 FROM final_results WHERE student_id=? AND semester_id<=>? AND status='released' LIMIT 1",[$ev['student_id'],$ev['semester_id']])->fetch():true;
  $presentationReady=$ev&&(!in_array($ev['evaluator_role'],['report_panel','presentation_panel'],true)||$ev['presentation_status']==='completed');
  if($ev&&$rubric&&!$released&&$presentationReady&&lecturerHasProgrammeRole($pdo,$lecturerId,$ev['evaluator_role'],$ev['programme_type'])){
   $criteria=safeRun($pdo,'SELECT id,max_marks FROM detailed_rubric_criteria WHERE detailed_rubric_id=? ORDER BY id',[$rubricId])->fetchAll();
   $expectedIds=array_map('intval',array_column($criteria,'id'));$submittedIds=array_map('intval',array_keys($scores));sort($expectedIds);sort($submittedIds);
   if($criteria&&$expectedIds===$submittedIds){
    $total=0.0;$validated=[];$valid=true;
    foreach($criteria as $criterion){$value=filter_var($scores[$criterion['id']],FILTER_VALIDATE_FLOAT);$max=(float)$criterion['max_marks'];if($value===false||(float)$value<0||(float)$value>$max||(float)$value>10){$valid=false;break;}$total+=(float)$value;$validated[]=[(int)$criterion['id'],(float)$value];}
    if($valid){
     $pdo->beginTransaction();try{
      safeRun($pdo,"INSERT INTO detailed_assessments(evaluation_id,detailed_rubric_id,total_score,comments,status,assessed_at) VALUES (?,?,?,?,'completed',NOW()) ON DUPLICATE KEY UPDATE detailed_rubric_id=VALUES(detailed_rubric_id),total_score=VALUES(total_score),comments=VALUES(comments),status='completed',assessed_at=NOW()",[$evalId,$rubricId,$total,$comments]);
      $assessment=safeRun($pdo,'SELECT id FROM detailed_assessments WHERE evaluation_id=?',[$evalId])->fetch();$assessmentId=(int)$assessment['id'];
      safeRun($pdo,'DELETE FROM detailed_assessment_scores WHERE detailed_assessment_id=?',[$assessmentId]);
      foreach($validated as [$criterionId,$score])safeRun($pdo,'INSERT INTO detailed_assessment_scores(detailed_assessment_id,detailed_criterion_id,score) VALUES (?,?,?)',[$assessmentId,$criterionId,$score]);
      $pdo->commit();auditLog($pdo,$user['id'],'submit_detailed_assessment',"Evaluation #$evalId detailed score=$total");
     }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();}
    }
   }
  }
  redirect('?view='.urlencode($backTo).'&eval_id='.$evalId.'&detail_saved=1');
 }

 if($action==='review_submission'&&$isSupervisor){
  $subId=(int)($_POST['submission_id']??0);$status=$_POST['sub_status']??'';$remarks=trim($_POST['remarks']??'');$back='?view=my-students';
  if(in_array($status,['under_review','approved','rejected'],true)){
   $sub=safeRun($pdo,"SELECT sub.*,s.supervisor_id,s.programme_type,s.user_id FROM submissions sub JOIN students s ON s.id=sub.student_id WHERE sub.id=? AND sub.semester_id=? AND s.semester_id=sub.semester_id",[$subId,$currentSemesterId])->fetch();
   if($sub&&(int)$sub['supervisor_id']===$lecturerId&&lecturerHasProgrammeRole($pdo,$lecturerId,'supervisor',$sub['programme_type'])){
    safeRun($pdo,"INSERT INTO submission_reviews (submission_id,reviewer_id,reviewer_role,status,remarks) VALUES (?,?,'supervisor',?,?) ON DUPLICATE KEY UPDATE reviewer_id=VALUES(reviewer_id),status=VALUES(status),remarks=VALUES(remarks),reviewed_at=NOW()",[$subId,$lecturerId,$status,$remarks]);
    safeRun($pdo,'UPDATE submissions SET status=? WHERE id=?',[$status,$subId]);
    $decisionLabel=submissionStatusLabel($status);
    notify($pdo,(int)$sub['user_id'],'Submission Reviewed','Your supervisor recorded the decision: '.$decisionLabel.'.','submission','history');
    auditLog($pdo,$user['id'],'review_submission',"Submission #$subId supervisor -> $status");
   }
  }
  redirect($back);
 }

 if($action==='review_report'){
  $reportId=(int)($_POST['report_id']??0);$marks=($_POST['marks']??'')!==''?(float)$_POST['marks']:null;$remarks=trim($_POST['remarks']??'');$backTo=in_array($_POST['back_to']??'', ['supervision-reports','report-panel-reports'],true)?$_POST['back_to']:'overview';
  $report=safeRun($pdo,"SELECT sr.*,s.supervisor_id,s.report_panel_id,s.programme_type,s.user_id FROM student_reports sr JOIN students s ON s.id=sr.student_id WHERE sr.id=? AND sr.semester_id=? AND s.semester_id=sr.semester_id",[$reportId,$currentSemesterId])->fetch();
  $authorized=$report&&(($report['target_role']==='supervisor'&&$report['supervisor_id']==$lecturerId)||($report['target_role']==='report_panel'&&$report['report_panel_id']==$lecturerId));
  if($authorized&&lecturerHasProgrammeRole($pdo,$lecturerId,$report['target_role'],$report['programme_type'])&&($marks===null||($marks>=0&&$marks<=100))){safeRun($pdo,"UPDATE student_reports SET marks=?,remarks=?,status='reviewed',reviewed_by=?,reviewed_at=NOW() WHERE id=?",[$marks,$remarks,$lecturerId,$reportId]);notify($pdo,(int)$report['user_id'],'Report Reviewed',roleLabel($report['target_role']).' reviewed your report.','report','my-reports');auditLog($pdo,$user['id'],'review_report',"Report #$reportId");}
  redirect('?view='.urlencode($backTo));
 }

 if($action==='confirm_supervision'&&$isCoordinator){
  $reqId=(int)($_POST['request_id']??0);
  $req=safeRun($pdo,"SELECT sr.*,sr.supervisor_id requested_supervisor_id,s.programme_type,s.user_id,s.supervisor_id current_supervisor_id FROM supervisor_requests sr JOIN students s ON s.id=sr.student_id WHERE sr.id=? AND sr.status='accepted' AND sr.coordinator_status='pending' AND sr.semester_id=s.semester_id AND s.semester_id=?",[$reqId,$currentSemesterId])->fetch();
  if(!$req||!coordinatorCanManageProgramme($pdo,$lecturerId,$req['programme_type'])||!lecturerHasProgrammeRole($pdo,(int)$req['requested_supervisor_id'],'supervisor',$req['programme_type']))redirect('?view=supervision-oversight&supervision_error=invalid');
  if(supervisorCurrentLoad($pdo,(int)$req['requested_supervisor_id'],$currentSemesterId)>=10)redirect('?view=supervision-oversight&supervision_error=capacity');
  try{
   $pdo->beginTransaction();
   safeRun($pdo,"UPDATE supervisor_requests SET coordinator_status='confirmed',coordinator_decided_by=?,coordinator_decided_at=NOW() WHERE id=? AND coordinator_status='pending'",[$lecturerId,$reqId]);
   safeRun($pdo,'UPDATE students SET supervisor_id=? WHERE id=?',[$req['requested_supervisor_id'],$req['student_id']]);
   syncStudentStageHistory($pdo,(int)$req['student_id']);
   safeRun($pdo,"UPDATE supervisor_requests SET status='rejected',response_note='Another request was confirmed.',responded_at=NOW() WHERE student_id=? AND semester_id=? AND id<>? AND status='pending'",[$req['student_id'],$currentSemesterId,$reqId]);
   $supervisorEval=ensureEvaluation($pdo,(int)$req['student_id'],(int)$req['requested_supervisor_id'],'supervisor',$req['programme_type']);
   $coordinatorEval=ensureEvaluation($pdo,(int)$req['student_id'],$lecturerId,'coordinator',$req['programme_type']);
   if(!$supervisorEval||!$coordinatorEval)throw new RuntimeException('Published evaluation rubric missing.');
   $pdo->commit();
   notify($pdo,(int)$req['user_id'],'Supervisor Confirmed','Your supervisor assignment is now official.','supervisor','my-supervisor');
   auditLog($pdo,$user['id'],'confirm_supervision',"Request #$reqId");
   redirect('?view=supervision-oversight&supervision=confirmed');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('FYPES confirm supervision failed: '.$e->getMessage());redirect('?view=supervision-oversight&supervision_error=rubric');}
 }

 if($action==='override_supervision'&&$isCoordinator){
  $reqId=(int)($_POST['request_id']??0);$newId=(int)($_POST['new_supervisor_id']??0);$note=trim($_POST['coordinator_note']??'');
  $req=safeRun($pdo,"SELECT sr.*,sr.supervisor_id requested_supervisor_id,s.programme_type,s.user_id,s.supervisor_id current_supervisor_id FROM supervisor_requests sr JOIN students s ON s.id=sr.student_id WHERE sr.id=? AND sr.status='accepted' AND sr.coordinator_status='pending' AND sr.semester_id=s.semester_id AND s.semester_id=?",[$reqId,$currentSemesterId])->fetch();
  if(!$req||!$newId||!coordinatorCanManageProgramme($pdo,$lecturerId,$req['programme_type'])||!lecturerHasProgrammeRole($pdo,$newId,'supervisor',$req['programme_type']))redirect('?view=supervision-oversight&supervision_error=invalid');
  $lockedSupervisor=(bool)safeRun($pdo,"SELECT 1 FROM evaluations WHERE student_id=? AND evaluator_role='supervisor' AND semester_id=? AND status='completed' LIMIT 1",[$req['student_id'],$currentSemesterId])->fetch();
  if($lockedSupervisor)redirect('?view=supervision-oversight&supervision_error=locked');
  if(supervisorCurrentLoad($pdo,$newId,$currentSemesterId)>=10)redirect('?view=supervision-oversight&supervision_error=capacity');
  try{
   $pdo->beginTransaction();
   safeRun($pdo,"UPDATE supervisor_requests SET coordinator_status='overridden',status='rejected',coordinator_note=?,coordinator_decided_by=?,coordinator_decided_at=NOW(),responded_at=COALESCE(responded_at,NOW()) WHERE id=?",[$note,$lecturerId,$reqId]);
   safeRun($pdo,"INSERT INTO supervisor_requests (student_id,supervisor_id,status,coordinator_status,coordinator_note,coordinator_decided_by,coordinator_decided_at,message,responded_at,semester_id) VALUES (?,?,'accepted','confirmed',?,?,NOW(),'Assigned by coordinator.',NOW(),?)",[$req['student_id'],$newId,$note,$lecturerId,$currentSemesterId]);
   safeRun($pdo,'UPDATE students SET supervisor_id=? WHERE id=?',[$newId,$req['student_id']]);
   if((int)$req['current_supervisor_id']!==$newId)resetSubmissionReviewsForAssignment($pdo,(int)$req['student_id'],'supervisor');
   syncStudentStageHistory($pdo,(int)$req['student_id']);
   syncStudentEvaluation($pdo,(int)$req['student_id'],'supervisor',$newId);
   $supervisorEval=safeRun($pdo,"SELECT id FROM evaluations WHERE student_id=? AND evaluator_role='supervisor' AND evaluator_id=? AND semester_id=? LIMIT 1",[$req['student_id'],$newId,$currentSemesterId])->fetch();
   $coordinatorEval=ensureEvaluation($pdo,(int)$req['student_id'],$lecturerId,'coordinator',$req['programme_type']);
   if(!$supervisorEval||!$coordinatorEval)throw new RuntimeException('Published evaluation rubric missing.');
   $pdo->commit();
   notify($pdo,(int)$req['user_id'],'Supervisor Reassigned','Your coordinator assigned and confirmed a new supervisor.','supervisor','my-supervisor');
   auditLog($pdo,$user['id'],'override_supervision',"Student #{$req['student_id']} -> lecturer #$newId");
   redirect('?view=supervision-oversight&supervision=overridden');
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('FYPES override supervision failed: '.$e->getMessage());redirect('?view=supervision-oversight&supervision_error=rubric');}
 }

 if($action==='assign_panels'&&$isCoordinator){
  $studentId=(int)($_POST['student_id']??0);$reportId=(int)($_POST['report_panel_id']??0);$presentationId=(int)($_POST['presentation_panel_id']??0);$dateInput=trim((string)($_POST['panel_date']??''));$venue=trim($_POST['presentation_venue']??'');$s=coordinatorOwnsTarget($pdo,$lecturerId,$studentId);
  $presDate=null;if($dateInput!==''&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$dateInput)){[$yy,$mm,$dd]=array_map('intval',explode('-',$dateInput));if(checkdate($mm,$dd,$yy))$presDate=$dateInput.' 00:00:00';}
  $reportLocked=$s&&(int)$s['report_panel_id']!==$reportId&&(bool)safeRun($pdo,"SELECT 1 FROM evaluations WHERE student_id=? AND evaluator_role='report_panel' AND semester_id=? AND status='completed' LIMIT 1",[$studentId,$currentSemesterId])->fetch();
  $presentationLocked=$s&&(int)$s['presentation_panel_id']!==$presentationId&&(bool)safeRun($pdo,"SELECT 1 FROM evaluations WHERE student_id=? AND evaluator_role='presentation_panel' AND semester_id=? AND status='completed' LIMIT 1",[$studentId,$currentSemesterId])->fetch();
  $scheduleFrozen=$s&&$s['presentation_status']==='completed';
  $valid=$s&&!$scheduleFrozen&&!$reportLocked&&!$presentationLocked&&$reportId&&$presentationId&&$presDate&&$reportId!==$presentationId&&$reportId!==(int)$s['supervisor_id']&&$presentationId!==(int)$s['supervisor_id']&&lecturerHasProgrammeRole($pdo,$reportId,'report_panel',$s['programme_type'])&&lecturerHasProgrammeRole($pdo,$presentationId,'presentation_panel',$s['programme_type']);
  if($valid){$pdo->beginTransaction();try{
   safeRun($pdo,"UPDATE students SET report_panel_id=?,presentation_panel_id=?,presentation_date=?,presentation_venue=?,presentation_status='scheduled' WHERE id=?",[$reportId,$presentationId,$presDate,$venue?:null,$studentId]);
   syncStudentStageHistory($pdo,$studentId);syncStudentEvaluation($pdo,$studentId,'report_panel',$reportId);syncStudentEvaluation($pdo,$studentId,'presentation_panel',$presentationId);ensureEvaluation($pdo,$studentId,$lecturerId,'coordinator',$s['programme_type']);
   $pdo->commit();
   foreach([[$reportId,'Report Panel','report-panel-students'],[$presentationId,'Presentation Panel','presentation-panel-students']] as [$pid,$label,$link]){$pu=safeRun($pdo,'SELECT user_id FROM lecturers WHERE id=?',[$pid])->fetch();if($pu)notify($pdo,(int)$pu['user_id'],'New '.$label.' Assignment','You have been assigned to a student. The shared panel date is '.date('M j, Y',strtotime($presDate)).'. Evaluation unlocks only after the coordinator marks the panel session completed.','panel',$link);}
   notify($pdo,(int)$s['user_id'],'Panels Assigned','Your Report Panel and Presentation Panel have been assigned for '.date('M j, Y',strtotime($presDate)).'.','panel','my-panels');auditLog($pdo,$user['id'],'assign_panels',"Student #$studentId report=$reportId presentation=$presentationId date=$dateInput");
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$valid=false;error_log('FYPES assign panels failed: '.$e->getMessage());}}
  redirect('?view=monitor'.($valid?'&saved=1':'&error=invalid'));
 }

 if($action==='complete_presentation'&&$isCoordinator){
  $studentId=(int)($_POST['student_id']??0);$s=coordinatorOwnsTarget($pdo,$lecturerId,$studentId);
  if($s&&$s['presentation_status']==='scheduled'&&$s['presentation_date']&&date('Y-m-d',strtotime($s['presentation_date']))<=date('Y-m-d')&&$s['report_panel_id']&&$s['presentation_panel_id']){
   safeRun($pdo,"UPDATE students SET presentation_status='completed' WHERE id=?",[$studentId]);
   foreach([[(int)$s['report_panel_id'],'Report Panel','evaluate-report-panel'],[(int)$s['presentation_panel_id'],'Presentation Panel','evaluate-presentation-panel']] as [$panelId,$label,$link]){$panelUser=safeRun($pdo,'SELECT user_id FROM lecturers WHERE id=?',[$panelId])->fetch();if($panelUser)notify($pdo,(int)$panelUser['user_id'],$label.' Evaluation Ready','The shared panel session has been marked completed. You may now submit your evaluation.','evaluation',$link);}
   auditLog($pdo,$user['id'],'complete_panel_session',"Student #$studentId");
  }
  redirect('?view=monitor&completed=1');
 }

 if($action==='finalize_result'&&$isCoordinator){$studentId=(int)($_POST['student_id']??0);$s=coordinatorOwnsTarget($pdo,$lecturerId,$studentId);if($s){$result=recalculateFinalResult($pdo,$studentId,$lecturerId);if($result)auditLog($pdo,$user['id'],'finalize_result',"Student #$studentId score={$result['score']}");}redirect('?view=evaluate-coordinator&student_id='.$studentId);}
 if($action==='release_result'&&$isCoordinator){
  $studentId=(int)($_POST['student_id']??0);$s=coordinatorOwnsTarget($pdo,$lecturerId,$studentId);
  if($s){
   $result=safeRun($pdo,"SELECT fr.id,fr.grade,s.user_id FROM final_results fr JOIN students s ON s.id=fr.student_id WHERE fr.student_id=? AND fr.semester_id=? AND fr.status='finalized'",[$studentId,$currentSemesterId])->fetch();
   if($result){
    $pdo->beginTransaction();
    try{
     safeRun($pdo,"UPDATE final_results SET status='released',released_at=NOW() WHERE id=? AND semester_id=?",[$result['id'],$currentSemesterId]);
     $pdo->commit();
     notify($pdo,(int)$result['user_id'],'Final Result Released','Your finalized FYP result is now available.','result','results');
     auditLog($pdo,$user['id'],'release_result',"Student #$studentId grade={$result['grade']}");
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('FYPES result release failed: '.$e->getMessage());}
   }
  }
  redirect('?view=evaluate-coordinator&student_id='.$studentId);
 }

 if($action==='set_student_progression'&&$isCoordinator){
  $studentId=(int)($_POST['student_id']??0);$semesterId=(int)($_POST['semester_id']??0);$decision=$_POST['decision']??'';
  $history=safeRun($pdo,"SELECT h.*,s.user_id,s.academic_status,fr.status result_status FROM student_stage_history h JOIN students s ON s.id=h.student_id LEFT JOIN final_results fr ON fr.student_id=h.student_id AND fr.semester_id=h.semester_id WHERE h.student_id=? AND h.semester_id=?",[$studentId,$semesterId])->fetch();
  if($history&&$semesterId===$currentSemesterId&&$history['result_status']==='released'&&coordinatorCanManageProgramme($pdo,$lecturerId,$history['programme_type'],$semesterId)){
   $isFyp1=str_ends_with($history['programme_type'],'fyp1');$valid=($isFyp1&&in_array($decision,['retake_fyp1','promote_fyp2'],true))||(!$isFyp1&&in_array($decision,['retake_fyp2','completed_fyp'],true));
   if($valid){
    $status=in_array($decision,['retake_fyp1','retake_fyp2'],true)?'failed':($decision==='promote_fyp2'?'promoted':'completed');$outcome=$status==='failed'?'fail':'pass';
    $pdo->beginTransaction();try{
     safeRun($pdo,'UPDATE student_stage_history SET status=?,outcome=?,ended_at=NOW() WHERE id=?',[$status,$outcome,(int)$history['id']]);
     if($status==='completed'){
      safeRun($pdo,"UPDATE students SET academic_status='completed',semester_id=NULL,supervisor_id=NULL,report_panel_id=NULL,presentation_panel_id=NULL WHERE id=?",[$studentId]);
      safeRun($pdo,"UPDATE users SET status='inactive' WHERE id=?",[(int)$history['user_id']]);
     }else{
      safeRun($pdo,"UPDATE students SET academic_status='active',semester_id=? WHERE id=?",[$semesterId,$studentId]);
      safeRun($pdo,"UPDATE users SET status='active' WHERE id=?",[(int)$history['user_id']]);
     }
     $future=safeRun($pdo,"SELECT id FROM semesters WHERE is_current=0 AND activated_at IS NULL AND source_semester_id=?",[$semesterId])->fetchAll();foreach($future as $futureSemester)prepareSemesterRollover($pdo,(int)$futureSemester['id'],$semesterId);
     $pdo->commit();auditLog($pdo,$user['id'],'set_student_progression',"Student #$studentId -> $decision");
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
   }
  }
  redirect('?view=student-results&semester_id='.$semesterId.'&progression=1');
 }

 if($action==='reopen_evaluation'&&$isCoordinator){
  $evalId=(int)($_POST['evaluation_id']??0);$ev=safeRun($pdo,"SELECT ev.*,s.user_id,s.programme_type,s.semester_id FROM evaluations ev JOIN students s ON s.id=ev.student_id WHERE ev.id=? AND ev.semester_id=? AND s.semester_id=ev.semester_id",[$evalId,$currentSemesterId])->fetch();
  if($ev&&$ev['status']==='completed'&&coordinatorOwnsTarget($pdo,$lecturerId,(int)$ev['student_id'])){
   $released=safeRun($pdo,"SELECT 1 FROM final_results WHERE student_id=? AND semester_id<=>? AND status='released' LIMIT 1",[$ev['student_id'],$ev['semester_id']])->fetch();
   if(!$released){$pdo->beginTransaction();try{safeRun($pdo,"UPDATE evaluations SET status='reopened',locked_at=NULL WHERE id=?",[$evalId]);safeRun($pdo,"DELETE FROM final_results WHERE student_id=? AND semester_id<=>? AND status='finalized'",[$ev['student_id'],$ev['semester_id']]);$pdo->commit();$eu=safeRun($pdo,'SELECT user_id FROM lecturers WHERE id=?',[$ev['evaluator_id']])->fetch();if($eu)notify($pdo,(int)$eu['user_id'],'Evaluation Reopened',roleLabel($ev['evaluator_role']).' evaluation was reopened for correction.','evaluation',match($ev['evaluator_role']){'supervisor'=>'evaluate-supervision','report_panel'=>'evaluate-report-panel','presentation_panel'=>'evaluate-presentation-panel',default=>'evaluate-coordinator'});auditLog($pdo,$user['id'],'reopen_evaluation',"Evaluation #$evalId");}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();}}
  }
  redirect('?view=evaluate-coordinator&student_id='.(int)($ev['student_id']??0));
 }

 if($action==='update_evaluation_rubric'&&$isCoordinator){
  $rubricId=(int)($_POST['rubric_id']??0);$rubric=safeRun($pdo,"SELECT * FROM rubrics WHERE id=? AND is_locked=1",[$rubricId])->fetch();
  if($rubric&&coordinatorCanManageProgramme($pdo,$lecturerId,$rubric['programme_type'])){
   $existing=safeRun($pdo,'SELECT id FROM rubric_criteria WHERE rubric_id=? ORDER BY sort_order,id',[$rubricId])->fetchAll();$names=is_array($_POST['criteria_name']??null)?$_POST['criteria_name']:[];$weights=is_array($_POST['weight']??null)?$_POST['weight']:[];$rows=[];$total=0;$valid=true;
   foreach($existing as $row){$id=(int)$row['id'];$name=trim((string)($names[$id]??''));$weight=(int)($weights[$id]??0);if($name===''||$weight<1){$valid=false;break;}$rows[]=[$id,$name,$weight];$total+=$weight;}
   $newNames=is_array($_POST['new_criteria_name']??null)?$_POST['new_criteria_name']:[];$newWeights=is_array($_POST['new_weight']??null)?$_POST['new_weight']:[];$newRows=[];foreach($newNames as $i=>$name){$name=trim((string)$name);$weight=(int)($newWeights[$i]??0);if($name===''&&$weight===0)continue;if($name===''||$weight<1){$valid=false;break;}$newRows[]=[$name,$weight];$total+=$weight;}
   $expected=expectedRubricTotal($rubric['programme_type'],$rubric['evaluator_role']);
   if(!$valid||$total!==$expected)redirect('?view=rubrics&section=evaluation&rubric_id='.$rubricId.'&edit=1&rubricmsg=total');
   $pdo->beginTransaction();try{foreach($rows as [$id,$name,$weight])safeRun($pdo,'UPDATE rubric_criteria SET criteria_name=?,weight=?,max_marks=? WHERE id=? AND rubric_id=?',[$name,$weight,$weight,$id,$rubricId]);$order=(int)safeRun($pdo,'SELECT COALESCE(MAX(sort_order),0) m FROM rubric_criteria WHERE rubric_id=?',[$rubricId])->fetch()['m'];foreach($newRows as [$name,$weight])safeRun($pdo,'INSERT INTO rubric_criteria(rubric_id,criteria_name,weight,max_marks,sort_order) VALUES (?,?,?,?,?)',[$rubricId,$name,$weight,$weight,++$order]);safeRun($pdo,'UPDATE rubrics SET published_at=NOW() WHERE id=?',[$rubricId]);$pdo->commit();auditLog($pdo,$user['id'],'update_evaluation_rubric',"Rubric #$rubricId updated");}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}
  }
  redirect('?view=rubrics&section=evaluation&rubric_id='.$rubricId.'&rubricmsg=updated');
 }

 if($action==='update_published_detailed_rubric'&&$isCoordinator){
  $rubricId=(int)($_POST['detailed_rubric_id']??0);$rubric=safeRun($pdo,"SELECT * FROM detailed_rubrics WHERE id=? AND status='published'",[$rubricId])->fetch();
  if($rubric&&coordinatorCanManageProgramme($pdo,$lecturerId,$rubric['programme_type'])){
   $existing=safeRun($pdo,'SELECT id FROM detailed_rubric_criteria WHERE detailed_rubric_id=? ORDER BY sort_order,id',[$rubricId])->fetchAll();$names=is_array($_POST['criterion_name']??null)?$_POST['criterion_name']:[];$descriptions=is_array($_POST['description']??null)?$_POST['description']:[];$marks=is_array($_POST['max_marks']??null)?$_POST['max_marks']:[];$rows=[];$valid=true;
   foreach($existing as $row){$id=(int)$row['id'];$name=trim((string)($names[$id]??''));$description=trim((string)($descriptions[$id]??''));$mark=(float)($marks[$id]??0);if($name===''||$mark<0||$mark>10){$valid=false;break;}$rows[]=[$id,$name,$description,$mark];}
   $newNames=is_array($_POST['new_criterion_name']??null)?$_POST['new_criterion_name']:[];$newDescriptions=is_array($_POST['new_description']??null)?$_POST['new_description']:[];$newMarks=is_array($_POST['new_max_marks']??null)?$_POST['new_max_marks']:[];$newRows=[];foreach($newNames as $i=>$name){$name=trim((string)$name);$description=trim((string)($newDescriptions[$i]??''));$mark=(float)($newMarks[$i]??0);if($name===''&&$mark==0)continue;if($name===''||$mark<0||$mark>10){$valid=false;break;}$newRows[]=[$name,$description,$mark];}
   if($valid){$pdo->beginTransaction();try{foreach($rows as [$id,$name,$description,$mark])safeRun($pdo,'UPDATE detailed_rubric_criteria SET criterion_name=?,description=?,max_marks=? WHERE id=? AND detailed_rubric_id=?',[$name,$description,$mark,$id,$rubricId]);$order=(int)safeRun($pdo,'SELECT COALESCE(MAX(sort_order),0) m FROM detailed_rubric_criteria WHERE detailed_rubric_id=?',[$rubricId])->fetch()['m'];foreach($newRows as [$name,$description,$mark])safeRun($pdo,'INSERT INTO detailed_rubric_criteria(detailed_rubric_id,criterion_name,description,max_marks,sort_order) VALUES (?,?,?,?,?)',[$rubricId,$name,$description,$mark,++$order]);safeRun($pdo,'UPDATE detailed_rubrics SET published_at=NOW() WHERE id=?',[$rubricId]);$pdo->commit();auditLog($pdo,$user['id'],'update_detailed_rubric',"Detailed rubric #$rubricId updated");}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();throw $e;}}
  }
  redirect('?view=rubrics&section=detailed&detailed_id='.$rubricId.'&rubricmsg=updated');
 }

 // Legacy draft/version rubric actions were removed. Published rubrics are edited directly above.

 if($action==='add_timeline'&&$isCoordinator){$pt=$_POST['programme_type']??'';if(coordinatorCanManageProgramme($pdo,$lecturerId,$pt)&&in_array($_POST['event_type']??'', ['submission','evaluation','result_release','presentation'],true)&&($_POST['start_date']??'')<=($_POST['end_date']??''))safeRun($pdo,'INSERT INTO timelines (programme_type,event_name,event_type,start_date,end_date,created_by,semester_id) VALUES (?,?,?,?,?,?,?)',[$pt,trim($_POST['event_name']),$_POST['event_type'],$_POST['start_date'],$_POST['end_date'],$lecturerId,$currentSemesterId]);redirect('?view=timelines');}
 if($action==='delete_timeline'&&$isCoordinator){$id=(int)$_POST['timeline_id'];$row=safeRun($pdo,'SELECT programme_type FROM timelines WHERE id=? AND semester_id=?',[$id,$currentSemesterId])->fetch();if($row&&coordinatorCanManageProgramme($pdo,$lecturerId,$row['programme_type']))safeRun($pdo,'DELETE FROM timelines WHERE id=? AND semester_id=?',[$id,$currentSemesterId]);redirect('?view=timelines');}
 if($action==='add_category'&&$isCoordinator){$pt=$_POST['programme_type']??'';if(coordinatorCanManageProgramme($pdo,$lecturerId,$pt)){$path=null;if(isset($_FILES['info_file'])&&($_FILES['info_file']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){[$ok,$err,$path]=storeUploadedFile($_FILES['info_file'], 'category_info', 'uploads/category_info', ['pdf','doc','docx']);if(!$ok)$path=null;}safeRun($pdo,'INSERT INTO submission_categories (programme_type,name,description,sort_order,deadline,info_file_path,created_by,semester_id) VALUES (?,?,?,(SELECT x FROM (SELECT COALESCE(MAX(sort_order),0)+1 x FROM submission_categories WHERE programme_type=?) q),?,?,?,?)',[$pt,trim($_POST['name']),trim($_POST['description']),$pt,$_POST['deadline']?:null,$path,$lecturerId,$currentSemesterId]);}redirect('?view=submission-categories');}
 if($action==='edit_category'&&$isCoordinator){
  $categoryId=(int)($_POST['category_id']??0);$name=trim($_POST['name']??'');$description=trim($_POST['description']??'');$deadline=trim($_POST['deadline']??'');
  $category=safeRun($pdo,'SELECT * FROM submission_categories WHERE id=? AND semester_id=?',[$categoryId,$currentSemesterId])->fetch();
  if($category&&$name!==''&&coordinatorCanManageProgramme($pdo,$lecturerId,$category['programme_type'])){
   $newPath=$category['info_file_path'];
   if(isset($_FILES['info_file'])&&($_FILES['info_file']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){
    [$ok,$err,$storedPath]=storeUploadedFile($_FILES['info_file'],'category_info','uploads/category_info',['pdf','doc','docx']);
    if($ok){$newPath=$storedPath;if($category['info_file_path']&&$category['info_file_path']!==$newPath)@unlink(__DIR__.'/../../'.$category['info_file_path']);}
   }
   safeRun($pdo,'UPDATE submission_categories SET name=?,description=?,deadline=?,info_file_path=? WHERE id=?',[$name,$description,$deadline!==''?$deadline:null,$newPath,$categoryId]);
   auditLog($pdo,$user['id'],'edit_submission_category',"Category #$categoryId");
  }
  redirect('?view=submission-categories&catmsg=updated');
 }
 if($action==='delete_category'&&$isCoordinator){
  $categoryId=(int)($_POST['category_id']??0);$category=safeRun($pdo,'SELECT * FROM submission_categories WHERE id=? AND semester_id=?',[$categoryId,$currentSemesterId])->fetch();
  $used=$category?(bool)safeRun($pdo,'SELECT 1 FROM submissions WHERE category_id=? LIMIT 1',[$categoryId])->fetch():true;
  if($category&&!$used&&coordinatorCanManageProgramme($pdo,$lecturerId,$category['programme_type'])){
   safeRun($pdo,'DELETE FROM submission_categories WHERE id=?',[$categoryId]);
   if($category['info_file_path'])@unlink(__DIR__.'/../../'.$category['info_file_path']);
   auditLog($pdo,$user['id'],'delete_submission_category',"Category #$categoryId");
   redirect('?view=submission-categories&catmsg=deleted');
  }
  redirect('?view=submission-categories&catmsg=used');
 }
 if($action==='reopen_category'&&$isCoordinator){
  $categoryId=(int)($_POST['category_id']??0);$newDeadline=$_POST['new_deadline']??'';
  $category=safeRun($pdo,'SELECT * FROM submission_categories WHERE id=? AND semester_id=?',[$categoryId,$currentSemesterId])->fetch();
  if($category&&coordinatorCanManageProgramme($pdo,$lecturerId,$category['programme_type'])&&$category['deadline']&&$category['deadline']<date('Y-m-d')&&$newDeadline>date('Y-m-d')){safeRun($pdo,'UPDATE submission_categories SET deadline=? WHERE id=?',[$newDeadline,$categoryId]);auditLog($pdo,$user['id'],'reopen_submission_category',"Category #$categoryId -> $newDeadline");}
  redirect('?view=submission-categories&reopened=1');
 }
 if($action==='reopen_progress_form'&&$isCoordinator){
  $formId=(int)($_POST['progress_form_id']??0);$newDeadline=$_POST['new_deadline']??'';
  $form=safeRun($pdo,'SELECT * FROM progress_forms WHERE id=? AND semester_id=?',[$formId,$currentSemesterId])->fetch();
  if($form&&coordinatorCanManageProgramme($pdo,$lecturerId,$form['programme_type'])&&$form['deadline']&&$form['deadline']<date('Y-m-d')&&$newDeadline>date('Y-m-d')){safeRun($pdo,'UPDATE progress_forms SET deadline=? WHERE id=?',[$newDeadline,$formId]);auditLog($pdo,$user['id'],'reopen_progress_form',"Progress form #$formId -> $newDeadline");}
  redirect('?view=progress-forms&reopened=1');
 }

 if($action==='edit_progress_form'&&$isCoordinator){
  $formId=(int)($_POST['progress_form_id']??0);$form=safeRun($pdo,'SELECT * FROM progress_forms WHERE id=? AND semester_id=?',[$formId,$currentSemesterId])->fetch();
  if($form&&coordinatorCanManageProgramme($pdo,$lecturerId,$form['programme_type'])){$title=trim($_POST['title']??'');$description=trim($_POST['description']??'');$week=(int)($_POST['week_number']??0);$deadline=trim($_POST['deadline']??'');$path=$form['file_path'];if(isset($_FILES['form_file'])&&($_FILES['form_file']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){[$ok,$err,$newPath]=storeUploadedFile($_FILES['form_file'],'progress_form','uploads/progress_forms',['pdf','doc','docx']);if($ok){if($path&&$path!==$newPath)@unlink(__DIR__.'/../../'.$path);$path=$newPath;}}if($title!=='')safeRun($pdo,'UPDATE progress_forms SET title=?,description=?,week_number=?,deadline=?,file_path=? WHERE id=?',[$title,$description,$week?:null,$deadline?:null,$path,$formId]);auditLog($pdo,$user['id'],'edit_progress_form',"Progress form #$formId");}
  redirect('?view=progress-forms&formmsg=updated');
 }
 if($action==='delete_progress_form'&&$isCoordinator){
  $formId=(int)($_POST['progress_form_id']??0);$form=safeRun($pdo,'SELECT * FROM progress_forms WHERE id=? AND semester_id=?',[$formId,$currentSemesterId])->fetch();
  if($form&&coordinatorCanManageProgramme($pdo,$lecturerId,$form['programme_type'])){$files=safeRun($pdo,'SELECT file_path FROM progress_submissions WHERE progress_form_id=?',[$formId])->fetchAll();safeRun($pdo,'DELETE FROM progress_forms WHERE id=?',[$formId]);foreach($files as $file)if($file['file_path'])@unlink(__DIR__.'/../../'.$file['file_path']);if($form['file_path'])@unlink(__DIR__.'/../../'.$form['file_path']);auditLog($pdo,$user['id'],'delete_progress_form',"Progress form #$formId");}
  redirect('?view=progress-forms&formmsg=deleted');
 }

 if($action==='create_progress_form'&&$isCoordinator){$pt=$_POST['programme_type']??'';if(coordinatorCanManageProgramme($pdo,$lecturerId,$pt)){$path=null;if(isset($_FILES['form_file'])&&($_FILES['form_file']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){[$ok,$err,$path]=storeUploadedFile($_FILES['form_file'], 'progress_form', 'uploads/progress_forms', ['pdf','doc','docx']);if(!$ok)$path=null;}safeRun($pdo,'INSERT INTO progress_forms (programme_type,title,description,file_path,week_number,deadline,created_by,semester_id) VALUES (?,?,?,?,?,?,?,?)',[$pt,trim($_POST['title']),trim($_POST['description']),$path,(int)$_POST['week_number']?:null,$_POST['deadline']?:null,$lecturerId,$currentSemesterId]);}redirect('?view=progress-forms');}
 if($action==='generate_role_invitation'&&$isCoordinator){
  $targetId=(int)($_POST['target_lecturer_id']??0);$pt=$_POST['programme_type']??'';$letterRole=$_POST['letter_role']??'';
  if(in_array($letterRole,['supervisor','report_panel','presentation_panel'],true)&&coordinatorCanManageProgramme($pdo,$lecturerId,$pt,$currentSemesterId)&&lecturerHasProgrammeRole($pdo,$targetId,$letterRole,$pt,$currentSemesterId)){
   $letterType=letterTypeForRole($letterRole);$prefix=$letterType==='appointment'?'FYPES-APT':'FYPES-INV';$code=generateCode($prefix);
   safeRun($pdo,"INSERT INTO appointment_letters(lecturer_id,role_type,programme_type,semester_id,letter_code,generated_by,generated_at) VALUES (?,?,?,?,?,?,NOW()) ON DUPLICATE KEY UPDATE letter_code=VALUES(letter_code),generated_by=VALUES(generated_by),generated_at=NOW()",[$targetId,$letterRole,$pt,$currentSemesterId,$code,$user['id']]);
   $target=safeRun($pdo,'SELECT user_id FROM lecturers WHERE id=?',[$targetId])->fetch();if($target)notify($pdo,(int)$target['user_id'],letterTypeLabel($letterType).' Issued','Your '.programmeTypeLabel($pt).' '.roleLabel($letterRole).' '.strtolower(letterTypeLabel($letterType)).' is available.','letter','invitation-letters');
   auditLog($pdo,$user['id'],'generate_role_letter',"Lecturer #$targetId $letterRole $letterType $pt");
  }
  redirect('?view=invitation-letters&issued=1');
 }

}

$pendingReqCount=$isSupervisor?(int)safeRun($pdo,"SELECT COUNT(*) c FROM supervisor_requests WHERE supervisor_id=? AND semester_id=? AND status='pending'",[$lecturerId,$currentSemesterId])->fetch()['c']:0;
$pendingCounts=[];foreach(['supervisor','report_panel','presentation_panel','coordinator'] as $r)$pendingCounts[$r]=(int)safeRun($pdo,"SELECT COUNT(*) c FROM evaluations WHERE evaluator_id=? AND evaluator_role=? AND semester_id=? AND status<>'completed'",[$lecturerId,$r,$currentSemesterId])->fetch()['c'];
$oversightCount=0;if($isCoordinator&&$coordProgrammeTypes){$ph=implode(',',array_fill(0,count($coordProgrammeTypes),'?'));$oversightCount=(int)safeRun($pdo,"SELECT COUNT(*) c FROM supervisor_requests sr JOIN students s ON s.id=sr.student_id WHERE sr.status='accepted' AND sr.coordinator_status='pending' AND sr.semester_id=? AND s.programme_type IN ($ph)",array_merge([$currentSemesterId],$coordProgrammeTypes))->fetch()['c'];}
$navGroups=[['label'=>'Main','items'=>[['icon'=>'gauge-high','label'=>'Overview','view'=>'overview']]]];
if($isSupervisor)$navGroups[]=['label'=>'Supervision','items'=>[['icon'=>'user-graduate','label'=>'My Students','view'=>'my-students'],['icon'=>'inbox','label'=>'Pending Requests','view'=>'pending-requests','badge'=>$pendingReqCount?:null],['icon'=>'file-circle-check','label'=>'Reports','view'=>'supervision-reports'],['icon'=>'clipboard-check','label'=>'Evaluate Supervision','view'=>'evaluate-supervision','badge'=>$pendingCounts['supervisor']?:null],['icon'=>'scale-balanced','label'=>'Rubric Criteria','view'=>'supervisor-rubric']]];
if($isReportPanel)$navGroups[]=['label'=>'Report Panel','items'=>[['icon'=>'users-viewfinder','label'=>'Assigned Students','view'=>'report-panel-students'],['icon'=>'file-circle-check','label'=>'Submitted Reports','view'=>'report-panel-reports'],['icon'=>'clipboard-check','label'=>'Evaluate Reports','view'=>'evaluate-report-panel','badge'=>$pendingCounts['report_panel']?:null],['icon'=>'scale-balanced','label'=>'Rubric Criteria','view'=>'report-panel-rubric']]];
if($isPresentationPanel)$navGroups[]=['label'=>'Presentation Panel','items'=>[['icon'=>'person-chalkboard','label'=>'Presentation Students','view'=>'presentation-panel-students'],['icon'=>'clipboard-check','label'=>'Evaluate Presentations','view'=>'evaluate-presentation-panel','badge'=>$pendingCounts['presentation_panel']?:null],['icon'=>'scale-balanced','label'=>'Rubric Criteria','view'=>'presentation-panel-rubric']]];
if($isCoordinator)$navGroups[]=['label'=>'Coordination','items'=>[['icon'=>'user-check','label'=>'Supervision Oversight','view'=>'supervision-oversight','badge'=>$oversightCount?:null],['icon'=>'users-viewfinder','label'=>'Assign Panels','view'=>'monitor'],['icon'=>'clipboard-check','label'=>'Progress & Results','view'=>'evaluate-coordinator','badge'=>$pendingCounts['coordinator']?:null],['icon'=>'chart-column','label'=>'Student Marks & Grades','view'=>'student-results'],['icon'=>'scale-balanced','label'=>'Rubric Management','view'=>'rubrics'],['icon'=>'list-check','label'=>'Rubric Criteria','view'=>'coordinator-rubric'],['icon'=>'calendar-days','label'=>'Timelines','view'=>'timelines'],['icon'=>'folder-tree','label'=>'Submission Categories','view'=>'submission-categories'],['icon'=>'file-arrow-up','label'=>'Progress Forms','view'=>'progress-forms'],['icon'=>'folder-open','label'=>'Student Submissions','view'=>'student-submissions']]];
$documentItems=[['icon'=>'envelope-open-text','label'=>'Letters','view'=>'invitation-letters']];$documentItems[]=['icon'=>'user','label'=>'Profile','view'=>'profile'];$navGroups[]=['label'=>'Documents','items'=>$documentItems];
$labels=[];foreach(['coordinator','supervisor','report_panel','presentation_panel'] as $r)if(in_array($r,$roleTypes,true))$labels[]=roleLabel($r);$roleLabelText=implode(' · ',$labels);
$pageTitleMap=['overview'=>'Overview','my-students'=>'My Students','pending-requests'=>'Pending Requests','evaluate-supervision'=>'Evaluate Supervision','report-panel-students'=>'Report Panel Students','presentation-panel-students'=>'Presentation Panel Students','report-panel-reports'=>'Report Panel Reports','evaluate-report-panel'=>'Evaluate Reports','evaluate-presentation-panel'=>'Evaluate Presentations','evaluate-coordinator'=>'Progress & Final Results','student-results'=>'Student Marks & Grades','my-rubrics'=>'Rubric Criteria','supervisor-rubric'=>'Supervisor Rubric Criteria','report-panel-rubric'=>'Report Panel Rubric Criteria','presentation-panel-rubric'=>'Presentation Panel Rubric Criteria','coordinator-rubric'=>'Coordinator Rubric Criteria','rubrics'=>'Rubric Management','timelines'=>'Timeline Management','submission-categories'=>'Submission Categories','monitor'=>'Assign Panels','supervision-oversight'=>'Supervision Oversight','progress-forms'=>'Progress Forms','student-submissions'=>'Student Submissions','invitation-letters'=>'Letters','profile'=>'Profile'];
$pageTitle=$pageTitleMap[$view]??'Dashboard';$themeColor='#F58220';$themeColorSoft='#FFF1E2';$activeView=$view;require __DIR__.'/../../includes/dash_head.php';
?>
<?php if($view==='overview'): ?>
<div class="row g-3 mb-4"><?php $mySup=$isSupervisor?(int)safeRun($pdo,'SELECT COUNT(*) c FROM students WHERE supervisor_id=? AND semester_id=?',[$lecturerId,$currentSemesterId])->fetch()['c']:0;$done=(int)safeRun($pdo,"SELECT COUNT(*) c FROM evaluations WHERE evaluator_id=? AND semester_id=? AND status='completed'",[$lecturerId,$currentSemesterId])->fetch()['c'];$cards=[['user-graduate','Supervised Students',$mySup],['inbox','Pending Requests',$pendingReqCount],['clipboard-check','Pending Evaluations',array_sum($pendingCounts)],['circle-check','Evaluations Completed',$done]];foreach($cards as [$i,$l,$v]): ?><div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon"><i class="fa-solid fa-<?= $i ?>"></i></div><div class="stat-value"><?= $v ?></div><div class="stat-label"><?= e($l) ?></div></div></div><?php endforeach; ?></div>
<div class="card-fy mb-3"><div class="section-title">Your Roles</div><div class="d-flex gap-2 flex-wrap"><?php foreach($roles as $r): ?><span class="badge-fy badge-info"><?= roleLabel($r['role_type']) ?> — <?= programmeTypeLabel($r['programme_type']) ?></span><?php endforeach; ?></div></div>
<?php if($isSupervisor): ?><div class="card-fy"><div class="section-title">Recent Supervisees</div><?php $recent=safeRun($pdo,'SELECT s.*,u.full_name FROM students s JOIN users u ON u.id=s.user_id WHERE s.supervisor_id=? AND s.semester_id=? ORDER BY s.id DESC LIMIT 5',[$lecturerId,$currentSemesterId])->fetchAll();foreach($recent as $s): ?><div class="d-flex justify-content-between py-2 border-bottom"><div><span class="fw-semibold small"><?= e($s['full_name']) ?></span><div class="small text-muted"><?= e($s['project_title']) ?></div></div><span class="badge-fy badge-info"><?= programmeTypeLabel($s['programme_type']) ?></span></div><?php endforeach;if(empty($recent)): ?><p class="text-muted small mb-0">No supervisees yet.</p><?php endif; ?></div><?php endif; ?>
<?php elseif($view==='my-students'&&$isSupervisor):include __DIR__.'/views/my-students.php';
elseif($view==='pending-requests'&&$isSupervisor):include __DIR__.'/views/pending-requests.php';
elseif($view==='evaluate-supervision'&&$isSupervisor):include __DIR__.'/views/evaluate-supervision.php';
elseif($view==='supervision-reports'&&$isSupervisor):include __DIR__.'/views/supervision-reports.php';
elseif($view==='report-panel-students'&&$isReportPanel):$panelRole='report_panel';include __DIR__.'/views/panel-students.php';
elseif($view==='presentation-panel-students'&&$isPresentationPanel):$panelRole='presentation_panel';include __DIR__.'/views/panel-students.php';
elseif($view==='report-panel-reports'&&$isReportPanel):include __DIR__.'/views/report-panel-reports.php';
elseif($view==='evaluate-report-panel'&&$isReportPanel):$panelRole='report_panel';$panelView='evaluate-report-panel';include __DIR__.'/views/evaluate-panel.php';
elseif($view==='evaluate-presentation-panel'&&$isPresentationPanel):$panelRole='presentation_panel';$panelView='evaluate-presentation-panel';include __DIR__.'/views/evaluate-panel.php';
elseif($view==='evaluate-coordinator'&&$isCoordinator):include __DIR__.'/views/evaluate-coordinator.php';
elseif($view==='supervisor-rubric'&&$isSupervisor):$rubricRoleFilter='supervisor';include __DIR__.'/views/my-rubrics.php';
elseif($view==='report-panel-rubric'&&$isReportPanel):$rubricRoleFilter='report_panel';include __DIR__.'/views/my-rubrics.php';
elseif($view==='presentation-panel-rubric'&&$isPresentationPanel):$rubricRoleFilter='presentation_panel';include __DIR__.'/views/my-rubrics.php';
elseif($view==='coordinator-rubric'&&$isCoordinator):$rubricRoleFilter='coordinator';include __DIR__.'/views/my-rubrics.php';
elseif($view==='my-rubrics'):include __DIR__.'/views/my-rubrics.php';
elseif($view==='rubrics'&&$isCoordinator):include __DIR__.'/views/rubrics.php';
elseif($view==='timelines'&&$isCoordinator):include __DIR__.'/views/timelines.php';
elseif($view==='submission-categories'&&$isCoordinator):include __DIR__.'/views/submission-categories.php';
elseif($view==='monitor'&&$isCoordinator):include __DIR__.'/views/monitor.php';
elseif($view==='supervision-oversight'&&$isCoordinator):include __DIR__.'/views/supervision-oversight.php';
elseif($view==='student-results'&&$isCoordinator):include __DIR__.'/views/student-results.php';
elseif($view==='progress-forms'&&$isCoordinator):include __DIR__.'/views/progress-forms.php';
elseif($view==='student-submissions'&&$isCoordinator):include __DIR__.'/views/student-submissions.php';
elseif($view==='invitation-letters'):include __DIR__.'/views/invitation-letters.php';
elseif($view==='profile'):include __DIR__.'/../shared/profile.php';
else:?><div class="card-fy text-center py-5 text-muted">You do not have access to this section.</div><?php endif; ?>
<?php require __DIR__.'/../../includes/dash_foot.php'; ?>
