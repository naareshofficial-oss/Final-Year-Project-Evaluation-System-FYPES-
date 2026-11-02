<?php
require_once __DIR__.'/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');
if(!isLoggedIn()){echo json_encode(['answer'=>'Please sign in to use the assistant.']);exit;}
if(getSetting($pdo,'chatbot_enabled','1')!=='1'){echo json_encode(['answer'=>'The assistant is currently disabled by the administrator.']);exit;}
if(!verifyCSRFToken($_POST['csrf_token']??null)){http_response_code(403);echo json_encode(['answer'=>'Your session expired. Refresh the page and try again.']);exit;}
$q=trim((string)($_POST['q']??''));if($q===''){echo json_encode(['answer'=>'Please type a question.']);exit;}
$role=$_SESSION['role']??'all';$aud=in_array($role,['student','lecturer','admin'],true)?$role:'all';
$lc=mb_strtolower($q);
$answer=null;
if(str_contains($lc,'forgot')||str_contains($lc,'reset password')){
 $answer=match($aud){'student'=>'Choose Student on the login screen, click Forgot Password, verify your student email and matric number, then set a new password.','lecturer'=>'Choose Lecturer on the login screen, click Forgot Password, verify your staff email and registered phone number, then set a new password.','admin'=>'Choose Admin on the login screen, click Forgot Password, verify your admin email and registered phone number, then set a new password.',default=>null};
}
if(!$answer&&$aud==='student'&&(str_contains($lc,'title')||str_contains($lc,'project overview'))) $answer='Open Overview, locate Project Overview, click Edit Title, enter the new project title, and save it.';
if(!$answer&&$aud==='student'&&(str_contains($lc,'supervisor')||str_contains($lc,'supervisee'))) $answer='Open My Supervisor. Available supervisors show their current load out of 10, such as 8/10. You may keep one open request at a time.';
if(!$answer&&$aud==='lecturer'&&str_contains($lc,'rubric')) $answer='Use Rubric Criteria inside the relevant sidebar section. It shows only that role’s published evaluation rubric and detailed rubric version. Coordinators edit and publish versions from Rubric Management.';
if(!$answer&&$aud==='lecturer'&&str_contains($lc,'reopen')) $answer='A coordinator can open Submission Categories or Progress Forms and use Reopen with new deadline after the previous deadline has expired. Existing submissions remain preserved.';
if(!$answer&&$aud==='admin'&&(str_contains($lc,'role')||str_contains($lc,'lecturer'))) $answer='Open Role Assignment to assign, edit, or delete Coordinator, Supervisor, Report Panel, and Presentation Panel roles. Coordinator assignments automatically issue a coordinator appointment letter.';
if(!$answer&&$aud==='admin'&&(str_contains($lc,'dark')||str_contains($lc,'system name')||str_contains($lc,'institution'))) $answer='Open Settings. Update System Name, Institution Name, or Global Dashboard Theme, then click Save validated settings. The change applies to every dashboard.';

$matched=null;
if(!$answer){
 $faqs=safeRun($pdo,"SELECT * FROM chatbot_faqs WHERE audience='all' OR audience=?",[$aud])->fetchAll();
 $normalize=function(string $v):array{$v=mb_strtolower(preg_replace('/[^a-z0-9\s]/i',' ',$v));$words=preg_split('/\s+/',$v,-1,PREG_SPLIT_NO_EMPTY);$stop=array_flip(['the','a','an','and','or','to','of','for','is','are','i','my','me','how','what','when','where','can','do','does','in','on','with','please']);return array_values(array_unique(array_filter($words,fn($w)=>mb_strlen($w)>2&&!isset($stop[$w]))));};
 $qw=$normalize($q);$best=null;$bestScore=0;
 foreach($faqs as $faq){$hay=mb_strtolower(($faq['question']??'').' '.($faq['category']??'').' '.($faq['keywords']??''));$fw=$normalize($hay);$score=count(array_intersect($qw,$fw))*3;foreach(array_filter(array_map('trim',explode(',',mb_strtolower($faq['keywords']??'')))) as $kw)if($kw!==''&&str_contains($lc,$kw))$score+=5;if($score>$bestScore){$bestScore=$score;$best=$faq;}}
 if($best&&$bestScore>=4){$answer=$best['answer'];$matched=$best['id'];}
}
if(!$answer)$answer=match($aud){'student'=>'I could not match that confidently. Try asking about your supervisor, project title, submissions, progress reports, panels, results, or password.','lecturer'=>'I could not match that confidently. Try asking about supervision requests, assigned students, rubrics, panels, deadlines, evaluations, or invitation letters.','admin'=>'I could not match that confidently. Try asking about users, lecturer roles, coordinator letters, settings, semesters, or certificates.',default=>'I could not match that question confidently.'};
safeRun($pdo,'INSERT INTO chatbot_logs(user_id,question,matched_faq_id) VALUES (?,?,?)',[(int)$_SESSION['user_id'],$q,$matched]);
echo json_encode(['answer'=>$answer,'matched'=>(bool)$matched],JSON_UNESCAPED_UNICODE);
