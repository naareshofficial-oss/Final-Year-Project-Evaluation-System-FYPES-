<?php
require_once __DIR__ . '/../../includes/config.php';
requireRole('admin');
$user=currentUser();
$view=$_GET['view']??'overview';
$currentSemesterId=currentSemesterId($pdo);
$validPts=validProgrammeTypes();
$validAcademicRoles=validAcademicRoles();

function adminRoleHasDependencies(PDO $pdo,array $role,?int $semesterId):bool{
 $lid=(int)$role['lecturer_id'];$pt=$role['programme_type'];$type=$role['role_type'];$semesterId=(int)($role['semester_id']??$semesterId);
 if($type==='supervisor')return (bool)safeRun($pdo,'SELECT 1 FROM students WHERE supervisor_id=? AND programme_type=? AND semester_id<=>? LIMIT 1',[$lid,$pt,$semesterId])->fetch();
 if($type==='report_panel')return (bool)safeRun($pdo,'SELECT 1 FROM students WHERE report_panel_id=? AND programme_type=? AND semester_id<=>? LIMIT 1',[$lid,$pt,$semesterId])->fetch();
 if($type==='presentation_panel')return (bool)safeRun($pdo,'SELECT 1 FROM students WHERE presentation_panel_id=? AND programme_type=? AND semester_id<=>? LIMIT 1',[$lid,$pt,$semesterId])->fetch();
 return (bool)safeRun($pdo,'SELECT 1 FROM evaluations ev JOIN students s ON s.id=ev.student_id WHERE ev.evaluator_id=? AND ev.evaluator_role=? AND s.programme_type=? AND ev.semester_id<=>? LIMIT 1',[$lid,$type,$pt,$semesterId])->fetch();
}

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!verifyCSRFToken($_POST['csrf_token']??null))redirect('?view='.urlencode($view).'&error=csrf');
 $action=$_POST['action']??'';

 if($action==='save_settings'){
  $incoming=$_POST['settings']??[];
  $allowed=['system_name','institution_name','academic_session','max_file_size_mb','chatbot_enabled','dark_mode','google_client_id','google_client_secret','google_redirect_uri','smtp_host','smtp_port','smtp_username','smtp_password','smtp_encryption','mail_from_email','mail_from_name','student_access_block_start','student_access_block_end'];
  $blockStart=trim((string)($incoming['student_access_block_start']??''));$blockEnd=trim((string)($incoming['student_access_block_end']??''));if(($blockStart==='' xor $blockEnd==='')||($blockStart!==''&&$blockEnd!==''&&$blockStart>$blockEnd))redirect('?view=settings&error=dates');
  foreach($allowed as $key){
   if(in_array($key,['google_client_secret','smtp_password'],true)&&trim((string)($incoming[$key]??''))==='')continue;
   $value=trim((string)($incoming[$key]??''));
   if($key==='max_file_size_mb')$value=(string)max(1,min(100,(int)$value));
   if($key==='smtp_port')$value=(string)max(1,min(65535,(int)$value));
   if(in_array($key,['chatbot_enabled','dark_mode'],true))$value=$value==='1'?'1':'0';
   if($key==='smtp_encryption'&&!in_array($value,['tls','ssl','none'],true))$value='tls';
   if(in_array($key,['system_name','institution_name','academic_session'],true)&&$value==='')continue;
   safeRun($pdo,"INSERT INTO system_settings(setting_key,setting_value,description) VALUES (?,?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)",[$key,$value,ucwords(str_replace('_',' ',$key))]);
  }
  auditLog($pdo,$user['id'],'update_settings','Validated settings and integrations updated');redirect('?view=settings&saved=1');
 }


 if($action==='toggle_status'){
  $uid=(int)($_POST['user_id']??0);$target=safeRun($pdo,'SELECT u.*,l.id lecturer_id FROM users u LEFT JOIN lecturers l ON l.user_id=u.id WHERE u.id=?',[$uid])->fetch();
  if($target&&$uid!==(int)$user['id']){
   $new=$target['status']==='active'?'inactive':'active';$blocked=false;
   if($new==='inactive'&&!empty($target['lecturer_id'])){
    $lid=(int)$target['lecturer_id'];
    $blocked=(bool)safeRun($pdo,'SELECT 1 FROM students WHERE semester_id<=>? AND (supervisor_id=? OR report_panel_id=? OR presentation_panel_id=?) LIMIT 1',[$currentSemesterId,$lid,$lid,$lid])->fetch()
      ||(bool)safeRun($pdo,"SELECT 1 FROM evaluations WHERE evaluator_id=? AND semester_id<=>? AND status<>'completed' LIMIT 1",[$lid,$currentSemesterId])->fetch();
   }
   if($blocked)redirect('?view=users&tab='.urlencode($target['role']==='student'?'students':'lecturers').'&statuserr=dependencies');
   safeRun($pdo,"UPDATE users SET status=?,email_verified=IF(?='active',1,email_verified) WHERE id=?",[$new,$new,$uid]);auditLog($pdo,$user['id'],'toggle_user_status',"User #$uid -> $new");
  }
  redirect('?view=users');
 }

 if($action==='delete_user'){
  $uid=(int)($_POST['user_id']??0);$target=safeRun($pdo,"SELECT u.*,l.id lecturer_id,s.id student_id FROM users u LEFT JOIN lecturers l ON l.user_id=u.id LEFT JOIN students s ON s.user_id=u.id WHERE u.id=? AND u.role IN ('student','lecturer')",[$uid])->fetch();
  if($target&&$uid!==(int)$user['id']){
   $pdo->beginTransaction();
   try{
    if(!empty($target['lecturer_id'])){
     $lid=(int)$target['lecturer_id'];
     safeRun($pdo,'UPDATE students SET supervisor_id=NULL WHERE supervisor_id=?',[$lid]);
     safeRun($pdo,'UPDATE students SET report_panel_id=NULL WHERE report_panel_id=?',[$lid]);
     safeRun($pdo,'UPDATE students SET presentation_panel_id=NULL WHERE presentation_panel_id=?',[$lid]);
     safeRun($pdo,'UPDATE student_stage_history SET supervisor_id=NULL WHERE supervisor_id=?',[$lid]);
     safeRun($pdo,'UPDATE student_stage_history SET report_panel_id=NULL WHERE report_panel_id=?',[$lid]);
     safeRun($pdo,'UPDATE student_stage_history SET presentation_panel_id=NULL WHERE presentation_panel_id=?',[$lid]);
     safeRun($pdo,'DELETE FROM supervisor_requests WHERE supervisor_id=?',[$lid]);
     safeRun($pdo,'DELETE FROM submission_reviews WHERE reviewer_id=?',[$lid]);
     safeRun($pdo,'UPDATE student_reports SET reviewed_by=NULL WHERE reviewed_by=?',[$lid]);
     safeRun($pdo,'DELETE FROM evaluations WHERE evaluator_id=?',[$lid]);
     safeRun($pdo,'UPDATE final_results SET finalized_by=NULL WHERE finalized_by=?',[$lid]);
    }
    safeRun($pdo,'DELETE FROM users WHERE id=?',[$uid]);
    $pdo->commit();auditLog($pdo,$user['id'],'delete_user',"Deleted {$target['role']} account {$target['email']}");
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();redirect('?view=users&tab='.($target['role']==='student'?'students':'lecturers').'&deleted=blocked');}
  }
  redirect('?view=users&tab='.(($target['role']??'student')==='student'?'students':'lecturers').'&deleted=1');
 }

 if($action==='assign_role'){
  $lid=(int)($_POST['lecturer_id']??0);$role=$_POST['role_type']??'';$pt=$_POST['programme_type']??'';
  $active=safeRun($pdo,"SELECT l.user_id,u.full_name FROM lecturers l JOIN users u ON u.id=l.user_id WHERE l.id=? AND u.status='active'",[$lid])->fetch();
  if($active&&in_array($role,$validAcademicRoles,true)&&in_array($pt,$validPts,true)){
   if($role==='coordinator'){
    $existing=safeRun($pdo,"SELECT lecturer_id FROM lecturer_roles WHERE role_type='coordinator' AND programme_type=? AND semester_id=? AND lecturer_id<>? LIMIT 1",[$pt,$currentSemesterId,$lid])->fetch();
    if($existing)redirect('?view=role-assignment&msg=coordinator_exists');
   }
   safeRun($pdo,'INSERT IGNORE INTO lecturer_roles (lecturer_id,role_type,programme_type,semester_id,assigned_by) VALUES (?,?,?,?,?)',[$lid,$role,$pt,$currentSemesterId,$user['id']]);
   if($role==='coordinator'){
    $studentsForCoordinator=safeRun($pdo,'SELECT id FROM students WHERE programme_type=? AND semester_id=? AND academic_status=\'active\'',[$pt,$currentSemesterId])->fetchAll();
    foreach($studentsForCoordinator as $studentRow)ensureEvaluation($pdo,(int)$studentRow['id'],$lid,'coordinator',$pt);
   }
   notify($pdo,(int)$active['user_id'],'New Academic Role','You were assigned as '.roleLabel($role).' for '.programmeTypeLabel($pt).'.','role','overview');
   auditLog($pdo,$user['id'],'assign_role',"Lecturer #$lid $role $pt");
  }
  redirect('?view=role-assignment&msg=ok');
 }

 if($action==='edit_role'){
  $rid=(int)($_POST['role_id']??0);$newRole=$_POST['role_type']??'';$newPt=$_POST['programme_type']??'';
  $current=safeRun($pdo,'SELECT * FROM lecturer_roles WHERE id=? AND semester_id=?',[$rid,$currentSemesterId])->fetch();
  if($current&&in_array($newRole,$validAcademicRoles,true)&&in_array($newPt,$validPts,true)){
   if(adminRoleHasDependencies($pdo,$current,$currentSemesterId)&&($current['role_type']!==$newRole||$current['programme_type']!==$newPt))redirect('?view=role-assignment&msg=dependencies');
   if($newRole==='coordinator'){
    $other=safeRun($pdo,"SELECT 1 FROM lecturer_roles WHERE role_type='coordinator' AND programme_type=? AND semester_id=? AND id<>? LIMIT 1",[$newPt,(int)$current['semester_id'],$rid])->fetch();
    if($other)redirect('?view=role-assignment&msg=coordinator_exists');
   }
   if($current['role_type']==='coordinator'&&($newRole!=='coordinator'||$current['programme_type']!==$newPt)){
    safeRun($pdo,"DELETE FROM appointment_letters WHERE lecturer_id=? AND role_type='coordinator' AND programme_type=? AND semester_id=?",[$current['lecturer_id'],$current['programme_type'],(int)$current['semester_id']]);
   }
   safeRun($pdo,'UPDATE lecturer_roles SET role_type=?,programme_type=?,assigned_by=?,assigned_at=NOW() WHERE id=?',[$newRole,$newPt,$user['id'],$rid]);
   auditLog($pdo,$user['id'],'edit_role',"Role #$rid -> $newRole $newPt");
  }
  redirect('?view=role-assignment&msg=edited');
 }

 if($action==='remove_role'){
  $rid=(int)($_POST['role_id']??0);$r=safeRun($pdo,'SELECT * FROM lecturer_roles WHERE id=? AND semester_id=?',[$rid,$currentSemesterId])->fetch();
  if($r&&adminRoleHasDependencies($pdo,$r,$currentSemesterId))redirect('?view=role-assignment&msg=dependencies');
  if($r){
   if($r['role_type']==='coordinator')safeRun($pdo,"DELETE FROM appointment_letters WHERE lecturer_id=? AND role_type='coordinator' AND programme_type=? AND semester_id=?",[$r['lecturer_id'],$r['programme_type'],(int)$r['semester_id']]);
   safeRun($pdo,'DELETE FROM lecturer_roles WHERE id=?',[$rid]);auditLog($pdo,$user['id'],'remove_role',"Role #$rid");
  }
  redirect('?view=role-assignment&msg=removed');
 }

 if($action==='add_lecturer'){
  $name=trim($_POST['full_name']??'');$email=strtolower(trim($_POST['email']??''));$department='Department of Computing';$phone=trim($_POST['phone']??'');
  if(!$name||!$phone||!validateLecturerEmail($email))redirect('?view=users&tab=lecturers&adderr=email');
  try{
   $pdo->beginTransaction();$temporaryPassword=generateTemporaryPassword();$hash=password_hash($temporaryPassword,PASSWORD_BCRYPT);
   safeRun($pdo,"INSERT INTO users (role,full_name,email,password,phone,status,email_verified,must_change_password) VALUES ('lecturer',?,?,?,?, 'active',1,1)",[$name,$email,$hash,$phone]);
   $uid=(int)$pdo->lastInsertId();safeRun($pdo,"INSERT INTO lecturers (user_id,faculty,department,max_supervisees) VALUES (?,'Faculty of Computing, Visual Arts and Communication',?,10)",[$uid,$department]);$pdo->commit();
   $sent=sendTemporaryPasswordEmail($pdo,$email,$name,$temporaryPassword,'lecturer');
   if($sent)safeRun($pdo,'UPDATE users SET temp_password_emailed_at=NOW() WHERE id=?',[$uid]);
   $_SESSION['new_account_email']=$email;$_SESSION['new_account_mail_sent']=$sent;$_SESSION['new_account_password']=$sent?null:$temporaryPassword;
   auditLog($pdo,$user['id'],'add_lecturer',$email.' email='.($sent?'sent':'failed'));
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();redirect('?view=users&tab=lecturers&adderr=exists');}
  redirect('?view=users&tab=lecturers&added=1');
 }

 if($action==='add_student'){
  $name=trim($_POST['full_name']??'');$email=strtolower(trim($_POST['email']??''));$matric=strtoupper(trim($_POST['matric_number']??''));
  $level=in_array($_POST['level']??'', ['diploma','degree'], true)?$_POST['level']:'';$fypStage=in_array($_POST['fyp_stage']??'', ['fyp1','fyp2'], true)?$_POST['fyp_stage']:'';
  $programme=trim($_POST['programme']??'');$title=trim($_POST['project_title']??'');$phone=trim($_POST['phone']??'');$pt=$level&&$fypStage?$level.'_'.$fypStage:'';
  $targetSemesterId=(int)($_POST['target_semester_id']??$currentSemesterId);
  $targetSemester=safeRun($pdo,'SELECT * FROM semesters WHERE id=?',[$targetSemesterId])->fetch();
  $isCurrentTarget=$targetSemester&&(int)$targetSemester['id']===(int)$currentSemesterId;
  // Brand-new enrolments prepared for a future semester begin at FYP1. FYP2
  // entries in a future semester are produced by promotion or retake planning.
  $futureStageValid=$isCurrentTarget||$fypStage==='fyp1';
  if(!$targetSemester||!$futureStageValid||!$name||!$phone||!validateMatricNumber($matric)||!validateStudentEmail($email)||!studentEmailMatchesMatric($email,$matric)||!in_array($pt,$validPts,true)||!programmeIsAllowedForLevel($programme,$level))redirect('?view=users&tab=students&semester_id='.$targetSemesterId.'&adderr=identity');
  try{$pdo->beginTransaction();$temporaryPassword=generateTemporaryPassword();$hash=password_hash($temporaryPassword,PASSWORD_BCRYPT);
   safeRun($pdo,"INSERT INTO users (role,full_name,email,password,phone,status,email_verified,must_change_password) VALUES ('student',?,?,?,?, 'active',1,1)",[$name,$email,$hash,$phone]);
   $uid=(int)$pdo->lastInsertId();
   if($isCurrentTarget){
    safeRun($pdo,"INSERT INTO students (user_id,matric_number,level,programme,faculty,programme_type,project_title,semester_id,academic_status,attempt_number) VALUES (?,?,?,?,'Faculty of Computing, Visual Arts and Communication',?,?,?,'active',1)",[$uid,$matric,$level,$programme,$pt,$title,$targetSemesterId]);
    $newStudentId=(int)$pdo->lastInsertId();syncStudentStageHistory($pdo,$newStudentId,'active','new');
   }else{
    safeRun($pdo,"INSERT INTO students (user_id,matric_number,level,programme,faculty,programme_type,project_title,semester_id,academic_status,attempt_number) VALUES (?,?,?,?,'Faculty of Computing, Visual Arts and Communication',?,?,NULL,'inactive',1)",[$uid,$matric,$level,$programme,$pt,$title]);
    $newStudentId=(int)$pdo->lastInsertId();
    safeRun($pdo,"INSERT INTO student_stage_history (student_id,semester_id,programme_type,project_title,status,outcome,attempt_number,enrolment_type,started_at) VALUES (?,?,?,?,'planned','pending',1,'new',?)",[$newStudentId,$targetSemesterId,$pt,$title,$targetSemester['start_date'].' 00:00:00']);
   }
   $pdo->commit();$sent=sendTemporaryPasswordEmail($pdo,$email,$name,$temporaryPassword,'student');if($sent)safeRun($pdo,'UPDATE users SET temp_password_emailed_at=NOW() WHERE id=?',[$uid]);$_SESSION['new_account_password']=$sent?null:$temporaryPassword;$_SESSION['new_account_email']=$email;$_SESSION['new_account_mail_sent']=$sent;auditLog($pdo,$user['id'],'add_student',"$matric $pt semester=$targetSemesterId email=".($sent?'sent':'failed'));
  }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Admin add student failed: '.$e->getMessage());redirect('?view=users&tab=students&semester_id='.$targetSemesterId.'&adderr=exists');}
  redirect('?view=users&tab=students&semester_id='.$targetSemesterId.'&added=1');
 }

 if($action==='send_message'){
  $subject=trim($_POST['subject']??'');$body=trim($_POST['body']??'');$aud=in_array($_POST['audience']??'', ['all','students','lecturers','coordinators'],true)?$_POST['audience']:'all';
  if($subject&&$body){
   safeRun($pdo,'INSERT INTO admin_messages (sender_id,audience,subject,body) VALUES (?,?,?,?)',[$user['id'],$aud,$subject,$body]);
   if($aud==='students')$targets=safeRun($pdo,"SELECT u.id FROM users u JOIN students s ON s.user_id=u.id WHERE u.role='student' AND u.status='active' AND s.semester_id=?",[$currentSemesterId])->fetchAll();
   elseif($aud==='lecturers')$targets=safeRun($pdo,"SELECT id FROM users WHERE role='lecturer' AND status='active'")->fetchAll();
   elseif($aud==='coordinators')$targets=safeRun($pdo,"SELECT DISTINCT u.id FROM users u JOIN lecturers l ON l.user_id=u.id JOIN lecturer_roles lr ON lr.lecturer_id=l.id WHERE lr.role_type='coordinator' AND lr.semester_id=? AND u.status='active'",[$currentSemesterId])->fetchAll();
   else $targets=safeRun($pdo,"SELECT id FROM users WHERE role IN ('student','lecturer') AND status='active'")->fetchAll();
   foreach($targets as $t)notify($pdo,(int)$t['id'],$subject,$body,'announcement');
  }
  redirect('?view=messages');
 }

 if($action==='add_faq'){
  $category=trim($_POST['category']??'');$question=trim($_POST['question']??'');$answer=trim($_POST['answer']??'');$keywords=trim($_POST['keywords']??'');
  if($category!==''&&$question!==''&&$answer!==''){
   safeRun($pdo,"INSERT INTO chatbot_faqs (category,audience,question,answer,keywords,created_by) VALUES (?,'all',?,?,?,?)",[$category,$question,$answer,$keywords,$user['id']]);
   auditLog($pdo,$user['id'],'add_faq',$question);
  }
  redirect('?view=faq&faqmsg=added');
 }
 if($action==='delete_faq'){
  $faqId=(int)($_POST['faq_id']??0);$faq=safeRun($pdo,'SELECT question FROM chatbot_faqs WHERE id=?',[$faqId])->fetch();
  if($faq){safeRun($pdo,'DELETE FROM chatbot_faqs WHERE id=?',[$faqId]);auditLog($pdo,$user['id'],'delete_faq',$faq['question']);}
  redirect('?view=faq&faqmsg=deleted');
 }

 if($action==='generate_certificate'){
  $resultId=(int)($_POST['final_result_id']??0);$fr=safeRun($pdo,"SELECT fr.*,s.user_id,u.full_name FROM final_results fr JOIN students s ON s.id=fr.student_id JOIN users u ON u.id=s.user_id WHERE fr.id=? AND fr.semester_id=? AND fr.status='released' AND fr.grade IN ('A','B','C')",[$resultId,$currentSemesterId])->fetch();
  if($fr){$exists=safeRun($pdo,'SELECT id FROM certificates WHERE final_result_id=?',[$resultId])->fetch();if(!$exists){$code=generateCode('FYPES-CERT');safeRun($pdo,"INSERT INTO certificates (user_id,final_result_id,cert_type,cert_code,title,grade,template,issued_by,issued_by_role,semester_id) VALUES (?,?,'completion',?,'Certificate of Completion - Final Year Project',?,'completion',?,'admin',?)",[$fr['user_id'],$resultId,$code,$fr['grade'],$user['id'],$fr['semester_id']]);notify($pdo,(int)$fr['user_id'],'Completion Certificate Issued','Your FYP completion certificate is available.','certificate','certificates');auditLog($pdo,$user['id'],'generate_certificate',"Result #$resultId");}}
  redirect('?view=certificates');
 }

 if($action==='generate_letter'){
  $lid=(int)($_POST['target_lecturer_id']??0);$pt=$_POST['programme_type']??'';
  $isCoordinator=in_array($pt,$validPts,true)&&lecturerHasProgrammeRole($pdo,$lid,'coordinator',$pt,$currentSemesterId);
  if($isCoordinator){
   $code=generateCode('FYPES-APT');$lu=safeRun($pdo,'SELECT user_id FROM lecturers WHERE id=?',[$lid])->fetch();
   safeRun($pdo,"INSERT INTO appointment_letters (lecturer_id,role_type,programme_type,semester_id,letter_code,file_path,generated_by,generated_at) VALUES (?,'coordinator',?,?,?,NULL,?,NOW()) ON DUPLICATE KEY UPDATE letter_code=VALUES(letter_code),file_path=NULL,generated_by=VALUES(generated_by),generated_at=NOW()",[$lid,$pt,$currentSemesterId,$code,$user['id']]);
   if($lu)notify($pdo,(int)$lu['user_id'],'Coordinator Appointment Letter Issued','Your '.programmeTypeLabel($pt).' Coordinator appointment letter is available.','letter','invitation-letters');
   auditLog($pdo,$user['id'],'generate_coordinator_appointment_letter',"Lecturer #$lid $pt");
  }
  redirect('?view=appointment-letters&issued=1');
 }
 if($action==='create_semester'){
  $name=trim($_POST['name']??'');$start=$_POST['start_date']??'';$end=$_POST['end_date']??'';
  if($name&&$start&&$end&&$start<=$end){
   $overlap=safeRun($pdo,'SELECT 1 FROM semesters WHERE NOT (end_date<? OR start_date>?) LIMIT 1',[$start,$end])->fetch();
   if($overlap)redirect('?view=semesters&error=overlap');
   $sourceSemester=$currentSemesterId;
   $pdo->beginTransaction();
   try{
    safeRun($pdo,'INSERT INTO semesters (name,start_date,end_date,is_current,source_semester_id) VALUES (?,?,?,0,?)',[$name,$start,$end,$sourceSemester]);
    $newSemester=(int)$pdo->lastInsertId();
    if($sourceSemester)prepareSemesterRollover($pdo,$newSemester,$sourceSemester);
    $pdo->commit();
    auditLog($pdo,$user['id'],'create_semester',"Semester #$newSemester $name; progression prepared from #$sourceSemester");
   }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Semester creation failed: '.$e->getMessage());redirect('?view=semesters&error=failed');}
  }
  redirect('?view=semesters&created=1');
 }
 if($action==='refresh_semester_rollover'){
  $semesterId=(int)($_POST['semester_id']??0);
  $semester=safeRun($pdo,'SELECT * FROM semesters WHERE id=? AND is_current=0',[$semesterId])->fetch();
  if($semester){
   $source=(int)($semester['source_semester_id']?:$currentSemesterId);
   $pdo->beginTransaction();try{prepareSemesterRollover($pdo,$semesterId,$source);$pdo->commit();auditLog($pdo,$user['id'],'refresh_semester_rollover',"Semester #$semesterId from #$source");}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();}
  }
  redirect('?view=semesters&sem_id='.$semesterId.'&prepared=1');
 }
 if($action==='set_current_semester'){
  $semesterId=(int)($_POST['semester_id']??0);$exists=safeRun($pdo,'SELECT id FROM semesters WHERE id=? AND activated_at IS NULL',[$semesterId])->fetch();
  if($exists){
   $pdo->beginTransaction();
   try{activateSemester($pdo,$semesterId);$pdo->commit();auditLog($pdo,$user['id'],'set_current_semester',"Semester #$semesterId activated with semester-scoped roles reset");}
   catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Semester activation failed: '.$e->getMessage());redirect('?view=semesters&error=activation');}
  }
  redirect('?view=semesters&sem_id='.$semesterId.'&activated=1');
 }
 if($action==='save_semester_dates'){
  $name=trim($_POST['semester_name']??'');$start=$_POST['semester_start_date']??'';$end=$_POST['semester_end_date']??'';
  if($name&&$start&&$end&&$start<=$end){safeRun($pdo,"UPDATE semesters SET name=?,start_date=?,end_date=? WHERE id=?",[$name,$start,$end,$currentSemesterId]);foreach(['semester_name'=>$name,'semester_start_date'=>$start,'semester_end_date'=>$end] as $k=>$v)safeRun($pdo,'UPDATE system_settings SET setting_value=? WHERE setting_key=?',[$v,$k]);auditLog($pdo,$user['id'],'update_semester_dates',$name);}redirect('?view=profile&saved=1');
 }
 if($action==='delete_semester'){
  $semesterId=(int)($_POST['semester_id']??0);$semester=safeRun($pdo,'SELECT * FROM semesters WHERE id=?',[$semesterId])->fetch();
  if($semester&&!$semester['is_current']&&empty($semester['activated_at'])){
   $hasProtected=(bool)safeRun($pdo,'SELECT 1 FROM submissions WHERE semester_id=? LIMIT 1',[$semesterId])->fetch()
    ||(bool)safeRun($pdo,'SELECT 1 FROM final_results WHERE semester_id=? LIMIT 1',[$semesterId])->fetch()
    ||(bool)safeRun($pdo,'SELECT 1 FROM evaluations WHERE semester_id=? LIMIT 1',[$semesterId])->fetch()
    ||(bool)safeRun($pdo,'SELECT 1 FROM progress_submissions ps JOIN progress_forms pf ON pf.id=ps.progress_form_id WHERE pf.semester_id=? LIMIT 1',[$semesterId])->fetch()
    ||(bool)safeRun($pdo,'SELECT 1 FROM semesters WHERE source_semester_id=? LIMIT 1',[$semesterId])->fetch();
   if(!$hasProtected){
    $pdo->beginTransaction();try{
     safeRun($pdo,"UPDATE students SET semester_id=NULL,academic_status='inactive',supervisor_id=NULL,report_panel_id=NULL,presentation_panel_id=NULL WHERE semester_id=?",[$semesterId]);
     safeRun($pdo,'DELETE FROM student_stage_history WHERE semester_id=?',[$semesterId]);
     safeRun($pdo,'DELETE FROM submission_categories WHERE semester_id=?',[$semesterId]);
     safeRun($pdo,'DELETE FROM progress_forms WHERE semester_id=?',[$semesterId]);
     safeRun($pdo,'DELETE FROM timelines WHERE semester_id=?',[$semesterId]);
     safeRun($pdo,'DELETE FROM semesters WHERE id=?',[$semesterId]);
     $pdo->commit();auditLog($pdo,$user['id'],'delete_semester',"Semester #$semesterId");redirect('?view=semesters&deleted=1');
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();error_log('Semester deletion failed: '.$e->getMessage());}
   }
  }
  redirect('?view=semesters&error=delete');
 }
}

/* ---------------- DATA FOR OVERVIEW ---------------- */

$counts = [
    'students' => safeRun($pdo, "SELECT COUNT(*) c FROM students WHERE semester_id=?",[$currentSemesterId])->fetch()['c'],
    'lecturers' => safeRun($pdo, "SELECT COUNT(*) c FROM lecturers l JOIN users u ON u.id=l.user_id WHERE u.status='active'")->fetch()['c'],
    'coordinators' => safeRun($pdo, "SELECT COUNT(DISTINCT lecturer_id) c FROM lecturer_roles WHERE role_type='coordinator' AND semester_id=?",[$currentSemesterId])->fetch()['c'],
    'supervisors' => safeRun($pdo, "SELECT COUNT(DISTINCT lecturer_id) c FROM lecturer_roles WHERE role_type='supervisor' AND semester_id=?",[$currentSemesterId])->fetch()['c'],
    'report_panels' => safeRun($pdo, "SELECT COUNT(DISTINCT lecturer_id) c FROM lecturer_roles WHERE role_type='report_panel' AND semester_id=?",[$currentSemesterId])->fetch()['c'],
    'presentation_panels' => safeRun($pdo, "SELECT COUNT(DISTINCT lecturer_id) c FROM lecturer_roles WHERE role_type='presentation_panel' AND semester_id=?",[$currentSemesterId])->fetch()['c'],
    'completed_eval' => safeRun($pdo, "SELECT COUNT(*) c FROM evaluations WHERE semester_id=? AND status='completed'",[$currentSemesterId])->fetch()['c'],
    'submissions' => safeRun($pdo, "SELECT COUNT(*) c FROM submissions WHERE semester_id=?",[$currentSemesterId])->fetch()['c'],
];
$pendingRoleLecturers = safeRun($pdo, "SELECT l.id, u.full_name, u.email FROM lecturers l JOIN users u ON u.id=l.user_id
    LEFT JOIN lecturer_roles lr ON lr.lecturer_id=l.id AND lr.semester_id=? WHERE u.status='active' AND lr.id IS NULL",[$currentSemesterId])->fetchAll();

$navGroups = [
  ['label'=>'Main','items'=>[
    ['icon'=>'gauge-high','label'=>'Overview','view'=>'overview'],
    ['icon'=>'users','label'=>'Users','view'=>'users'],
    ['icon'=>'sitemap','label'=>'Role Assignment','view'=>'role-assignment','badge'=>count($pendingRoleLecturers) ?: null],
    ['icon'=>'comment-dots','label'=>'Messages','view'=>'messages'],
  ]],
  ['label'=>'Monitoring','items'=>[
    ['icon'=>'file-lines','label'=>'Submissions','view'=>'submissions'],
    ['icon'=>'box-archive','label'=>'Document Archive','view'=>'archive'],
    ['icon'=>'clipboard-check','label'=>'Evaluations','view'=>'evaluations'],
    ['icon'=>'calendar-days','label'=>'Timeline','view'=>'timeline'],
  ]],
  ['label'=>'Documents','items'=>[
    ['icon'=>'certificate','label'=>'Certificates','view'=>'certificates'],
    ['icon'=>'envelope-open-text','label'=>'Letters','view'=>'appointment-letters'],
  ]],
  ['label'=>'Records','items'=>[
    ['icon'=>'clock-rotate-left','label'=>'Semester Archive','view'=>'semesters'],
    ['icon'=>'chart-line','label'=>'Analytics','view'=>'analytics'],
    ['icon'=>'list-check','label'=>'Audit Logs','view'=>'audit-logs'],
  ]],
  ['label'=>'System','items'=>[
    ['icon'=>'circle-question','label'=>'FAQ','view'=>'faq'],
    ['icon'=>'gear','label'=>'Settings','view'=>'settings'],
    ['icon'=>'user','label'=>'Profile','view'=>'profile'],
  ]],
];
$pageTitle = 'Admin Dashboard';
$themeColor = '#F58220'; $themeColorSoft = '#FFF1E2'; $roleLabelText = 'System Administrator';
$activeView = $view;
require __DIR__ . '/../../includes/dash_head.php';
?>

<?php if ($view === 'overview'): ?>
  <div class="row g-3 mb-4">
    <?php $cards=[['users','Total Students',$counts['students'],'#3B82F6','#DBEAFE'],['chalkboard-user','Total Lecturers',$counts['lecturers'],'#8B5CF6','#EDE9FE'],['layer-group','Coordinators',$counts['coordinators'],'#F58220','#FFF1E2'],['clipboard-check','Completed Evaluations',$counts['completed_eval'],'#16A34A','#DCFCE7']];foreach($cards as [$icon,$label,$val,$color,$soft]): ?>
    <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon" style="background:<?= $soft ?>;color:<?= $color ?>"><i class="fa-solid fa-<?= $icon ?>"></i></div><div class="stat-value"><?= (int)$val ?></div><div class="stat-label"><?= e($label) ?></div></div></div><?php endforeach; ?>
  </div>
  <div class="row g-3"><div class="col-lg-7"><div class="card-fy"><div class="section-title">System Snapshot</div><canvas id="snapshotChart" height="140"></canvas></div></div><div class="col-lg-5"><div class="card-fy"><div class="section-title">Role Coverage</div>
  <?php foreach([['Coordinators','coordinators','#F58220'],['Supervisors','supervisors','#3B82F6'],['Report Panels','report_panels','#8B5CF6'],['Presentation Panels','presentation_panels','#16A34A']] as [$label,$key,$color]): ?><div class="d-flex justify-content-between small mb-2"><span><?= e($label) ?></span><strong><?= (int)$counts[$key] ?></strong></div><div class="progress-fy mb-3"><div style="width:<?= min(100,(int)$counts[$key]*7) ?>%;background:<?= $color ?>"></div></div><?php endforeach; ?>
  </div></div></div>
  <script>new Chart(document.getElementById('snapshotChart'),{type:'bar',data:{labels:['Students','Lecturers','Coordinators','Supervisors','Report Panels','Presentation Panels'],datasets:[{data:[<?= $counts['students'] ?>,<?= $counts['lecturers'] ?>,<?= $counts['coordinators'] ?>,<?= $counts['supervisors'] ?>,<?= $counts['report_panels'] ?>,<?= $counts['presentation_panels'] ?>],backgroundColor:['#F58220','#F8A055','#FBC28A','#FDDBB5','#FEEEDA','#DCFCE7'],borderRadius:8}]},options:{plugins:{legend:{display:false}},scales:{y:{beginAtZero:true},x:{grid:{display:false}}}}});</script>

<?php elseif ($view === 'users'): include __DIR__ . '/views/users.php';
elseif ($view === 'role-assignment'): include __DIR__ . '/views/role-assignment.php';
elseif ($view === 'submissions'): include __DIR__ . '/views/submissions.php';
elseif ($view === 'archive'): include __DIR__ . '/views/archive.php';
elseif ($view === 'evaluations'): include __DIR__ . '/views/evaluations.php';
elseif ($view === 'timeline'): include __DIR__ . '/views/timeline.php';
elseif ($view === 'certificates'): include __DIR__ . '/views/certificates.php';
elseif ($view === 'appointment-letters'): include __DIR__ . '/views/appointment-letters.php';
elseif ($view === 'analytics'): include __DIR__ . '/views/analytics.php';
elseif ($view === 'audit-logs'): include __DIR__ . '/views/audit-logs.php';
elseif ($view === 'faq'): include __DIR__ . '/views/faq.php';
elseif ($view === 'settings'): include __DIR__ . '/views/settings.php';
elseif ($view === 'messages'): include __DIR__ . '/views/messages.php';
elseif ($view === 'semesters'): include __DIR__ . '/views/semesters.php';
elseif ($view === 'profile'): include __DIR__ . '/views/admin-profile.php';
endif;

require __DIR__ . '/../../includes/dash_foot.php';
