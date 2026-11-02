<?php
require_once __DIR__ . '/../includes/config.php';
requireLogin();
$user=currentUser();
$kind=$_GET['kind']??'';$id=(int)($_GET['id']??0);
$path=null;$downloadName=null;$allowed=false;$programmeType=null;$studentId=null;$ownerLecturerId=null;

switch($kind){
 case 'submission':
  $row=safeRun($pdo,"SELECT sub.file_path,sub.file_name,sub.student_id,s.programme_type,s.supervisor_id,s.report_panel_id,s.presentation_panel_id FROM submissions sub JOIN students s ON s.id=sub.student_id WHERE sub.id=?",[$id])->fetch();
  if($row){$path=$row['file_path'];$downloadName=$row['file_name'];$studentId=(int)$row['student_id'];$programmeType=$row['programme_type'];$allowed=$user['role']==='admin'||($user['role']==='student'&&(int)$user['student_id']===$studentId)||($user['role']==='lecturer'&&(in_array((int)$user['lecturer_id'],[(int)$row['supervisor_id'],(int)$row['report_panel_id'],(int)$row['presentation_panel_id']],true)||coordinatorCanManageProgramme($pdo,(int)$user['lecturer_id'],$programmeType)));}
  break;
 case 'student_report':
  $row=safeRun($pdo,"SELECT sr.file_path,sr.file_name,sr.student_id,sr.target_role,s.programme_type,s.supervisor_id,s.report_panel_id FROM student_reports sr JOIN students s ON s.id=sr.student_id WHERE sr.id=?",[$id])->fetch();
  if($row){$path=$row['file_path'];$downloadName=$row['file_name'];$studentId=(int)$row['student_id'];$programmeType=$row['programme_type'];$assigned=$row['target_role']==='supervisor'?(int)$row['supervisor_id']:(int)$row['report_panel_id'];$allowed=$user['role']==='admin'||($user['role']==='student'&&(int)$user['student_id']===$studentId)||($user['role']==='lecturer'&&((int)$user['lecturer_id']===$assigned||coordinatorCanManageProgramme($pdo,(int)$user['lecturer_id'],$programmeType)));}
  break;
 case 'progress_submission':
  $row=safeRun($pdo,"SELECT ps.file_path,ps.file_name,ps.student_id,s.programme_type,s.supervisor_id FROM progress_submissions ps JOIN students s ON s.id=ps.student_id WHERE ps.id=?",[$id])->fetch();
  if($row){$path=$row['file_path'];$downloadName=$row['file_name'];$studentId=(int)$row['student_id'];$programmeType=$row['programme_type'];$allowed=$user['role']==='admin'||($user['role']==='student'&&(int)$user['student_id']===$studentId)||($user['role']==='lecturer'&&((int)$user['lecturer_id']===(int)$row['supervisor_id']||coordinatorCanManageProgramme($pdo,(int)$user['lecturer_id'],$programmeType)));}
  break;
 case 'progress_form':
  $row=safeRun($pdo,'SELECT file_path,title,programme_type,semester_id FROM progress_forms WHERE id=?',[$id])->fetch();
  if($row){$path=$row['file_path'];$downloadName=$row['title'];$programmeType=$row['programme_type'];$allowed=$user['role']==='admin'||($user['role']==='student'&&safeRun($pdo,'SELECT 1 FROM students WHERE id=? AND programme_type=? AND semester_id=?',[$user['student_id'],$programmeType,$row['semester_id']])->fetch())||($user['role']==='lecturer'&&(int)$row['semester_id']===currentSemesterId($pdo)&&in_array($programmeType,array_column(currentLecturerRoles($pdo),'programme_type'),true));}
  break;
 case 'category_info':
  $row=safeRun($pdo,'SELECT info_file_path file_path,name file_name,programme_type,semester_id FROM submission_categories WHERE id=?',[$id])->fetch();
  if($row){$path=$row['file_path'];$downloadName=$row['file_name'];$programmeType=$row['programme_type'];$allowed=$user['role']==='admin'||($user['role']==='student'&&safeRun($pdo,'SELECT 1 FROM students WHERE id=? AND programme_type=? AND semester_id=?',[$user['student_id'],$programmeType,$row['semester_id']])->fetch())||($user['role']==='lecturer'&&(int)$row['semester_id']===currentSemesterId($pdo)&&in_array($programmeType,array_column(currentLecturerRoles($pdo),'programme_type'),true));}
  break;
}
if(!$allowed||!$path){http_response_code(403);exit('Access denied.');}
$full=realpath(__DIR__.'/../'.$path);$root=realpath(__DIR__.'/../uploads');
$rootPrefix=$root?rtrim($root,DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR:null;
if(!$full||!is_file($full)||!$rootPrefix||strpos($full,$rootPrefix)!==0){http_response_code(404);exit('File not found.');}
$mime=(new finfo(FILEINFO_MIME_TYPE))->file($full)?:'application/octet-stream';
header('Content-Type: '.$mime);header('X-Content-Type-Options: nosniff');header('Content-Length: '.filesize($full));
header('Content-Disposition: inline; filename="'.str_replace(['"',"\r","\n"],'_',basename($downloadName?:$full)).'"');
readfile($full);
