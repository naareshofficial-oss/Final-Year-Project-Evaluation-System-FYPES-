<?php
require_once __DIR__ . '/../../includes/config.php';
requireRole('student');
$user = currentUser();
$studentId = (int)$user['student_id'];
$student = safeRun($pdo, 'SELECT * FROM students WHERE id=?', [$studentId])->fetch();
if (!$student) { logoutUser(); redirect(BASE_URL . '/auth/login.php?error=unauthorized'); }
$view = $_GET['view'] ?? 'overview';
$studentSemesterId = studentSemesterId($pdo, $studentId);
$currentSemesterId = $studentSemesterId;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? null)) redirect('?view=' . urlencode($view) . '&error=csrf');
    $action = $_POST['action'] ?? '';

    if (($student['academic_status'] ?? 'active') !== 'active' && $action !== 'update_project_title') {
        redirect('?view=overview&inactive=1');
    }

    if ($action === 'update_project_title') {
        $title = trim((string)($_POST['project_title'] ?? ''));
        if ($title === '' || mb_strlen($title) > 255) redirect('?view=overview&title_error=1');
        safeRun($pdo,'UPDATE students SET project_title=? WHERE id=?',[$title,$studentId]);
        safeRun($pdo,'UPDATE student_stage_history SET project_title=? WHERE student_id=? AND semester_id<=>?',[$title,$studentId,$studentSemesterId]);
        $student['project_title']=$title;
        auditLog($pdo,$user['id'],'update_project_title','Student updated own project title');
        redirect('?view=overview&title_saved=1');
    }

    if ($action === 'request_supervisor') {
        $supId = (int)($_POST['supervisor_id'] ?? 0);
        $message = trim((string)($_POST['message'] ?? 'I would like to request your supervision for my FYP.'));
        if ($student['supervisor_id']) redirect('?view=my-supervisor&request_error=assigned');
        $eligible = safeRun($pdo, "SELECT l.id,l.user_id FROM lecturers l JOIN users u ON u.id=l.user_id
            JOIN lecturer_roles lr ON lr.lecturer_id=l.id
            WHERE l.id=? AND u.status='active' AND lr.role_type='supervisor' AND lr.programme_type=? AND lr.semester_id=? LIMIT 1",
            [$supId,$student['programme_type'],$studentSemesterId])->fetch();
        $hasOpenRequest = safeRun($pdo, "SELECT 1 FROM supervisor_requests
            WHERE student_id=? AND semester_id<=>? AND status IN ('pending','accepted') AND coordinator_status='pending' LIMIT 1",
            [$studentId,$studentSemesterId])->fetch();
        if ($hasOpenRequest) redirect('?view=my-supervisor&request_error=open');
        if (!$eligible) redirect('?view=my-supervisor&request_error=invalid');
        if (supervisorCurrentLoad($pdo,$supId,$studentSemesterId) >= 10) redirect('?view=my-supervisor&request_error=capacity');
        try {
            $pdo->beginTransaction();
            safeRun($pdo, "INSERT INTO supervisor_requests (student_id,supervisor_id,status,message,semester_id) VALUES (?,?,'pending',?,?)",
                [$studentId,$supId,$message !== '' ? $message : 'I would like to request your supervision for my FYP.',$studentSemesterId]);
            $pdo->commit();
            notify($pdo,(int)$eligible['user_id'],'New Supervision Request',$user['name'] . ' requested you as their FYP supervisor.','supervisor','pending-requests');
            auditLog($pdo,$user['id'],'request_supervisor',"Requested lecturer #$supId");
            redirect('?view=my-supervisor&request=sent');
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            error_log('FYPES supervisor request failed: '.$e->getMessage());
            redirect('?view=my-supervisor&request_error=failed');
        }
    }

    if ($action === 'upload_submission') {
        $catId = (int)($_POST['category_id'] ?? 0);
        $category = safeRun($pdo, 'SELECT * FROM submission_categories WHERE id=? AND programme_type=? AND (semester_id IS NULL OR semester_id=?)',
            [$catId,$student['programme_type'],$currentSemesterId])->fetch();
        if (!$category) redirect('?view=submissions&error=category');
        if ($category['deadline'] && date('Y-m-d') > $category['deadline']) redirect('?view=submissions&error=deadline');

        $googleDocLink = trim($_POST['google_doc_link'] ?? '');
        if ($googleDocLink !== '' && (!filter_var($googleDocLink,FILTER_VALIDATE_URL) || !preg_match('~^https://(docs|drive)\.google\.com/~i',$googleDocLink))) {
            redirect('?view=submissions&error=link');
        }
        $version = (int)safeRun($pdo,'SELECT COALESCE(MAX(version),0)+1 v FROM submissions WHERE student_id=? AND category_id=? AND semester_id<=>?',[$studentId,$catId,$studentSemesterId])->fetch()['v'];
        $filePath=''; $originalName='Google Doc Link';
        if (isset($_FILES['file']) && ($_FILES['file']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            [$ok,$err,$filePath,$originalName] = storeUploadedFile($_FILES['file'],$student['matric_number'].'_'.$catId.'_v'.$version);
            if (!$ok) redirect('?view=submissions&error=' . urlencode($err));
        } elseif ($googleDocLink === '') {
            redirect('?view=submissions&error=nofile');
        }
        safeRun($pdo,"INSERT INTO submissions (student_id,category_id,file_name,file_path,google_doc_link,version,status,semester_id) VALUES (?,?,?,?,?,?,'submitted',?)",
            [$studentId,$catId,$originalName,$filePath,$googleDocLink ?: null,$version,$currentSemesterId]);
        auditLog($pdo,$user['id'],'upload_submission',"Category #$catId, version $version");
        if (!empty($student['supervisor_id'])) {
            $target = safeRun($pdo,'SELECT user_id FROM lecturers WHERE id=?',[$student['supervisor_id']])->fetch();
            if ($target) notify($pdo,(int)$target['user_id'],'New Submission Received',$user['name'].' uploaded a new project submission.','submission','my-students');
        }
        redirect('?view=submissions&uploaded=1');
    }

    // Submitted versions are immutable. Students create a new version instead of deleting history.

    if ($action === 'submit_report') {
        $targetRole = $_POST['target_role'] ?? '';
        $column = $targetRole === 'supervisor' ? 'supervisor_id' : ($targetRole === 'report_panel' ? 'report_panel_id' : null);
        if (!$column || empty($student[$column])) redirect('?view=my-reports&error=assignment');
        if (!isset($_FILES['file'])) redirect('?view=my-reports&error=nofile');
        [$ok,$err,$filePath,$originalName] = storeUploadedFile($_FILES['file'],$student['matric_number'].'_report_'.$targetRole);
        if (!$ok) redirect('?view=my-reports&error=' . urlencode($err));
        safeRun($pdo,"INSERT INTO student_reports (student_id,target_role,file_name,file_path,semester_id) VALUES (?,?,?,?,?)",
            [$studentId,$targetRole,$originalName,$filePath,$currentSemesterId]);
        $target = safeRun($pdo,'SELECT user_id FROM lecturers WHERE id=?',[$student[$column]])->fetch();
        if ($target) notify($pdo,(int)$target['user_id'],'New Report Submitted',$user['name'].' submitted a report for your review.','report',$targetRole==='supervisor'?'supervision-reports':'report-panel-reports');
        auditLog($pdo,$user['id'],'submit_report','Target: '.$targetRole);
        redirect('?view=my-reports&uploaded=1');
    }

    if ($action === 'submit_progress_report') {
        $formId=(int)($_POST['progress_form_id'] ?? 0);
        $form=safeRun($pdo,'SELECT * FROM progress_forms WHERE id=? AND programme_type=? AND (semester_id IS NULL OR semester_id=?)',
            [$formId,$student['programme_type'],$currentSemesterId])->fetch();
        if (!$form) redirect('?view=progress-reports&error=form');
        if ($form['deadline'] && date('Y-m-d') > $form['deadline']) redirect('?view=progress-reports&error=deadline');
        if (!isset($_FILES['file'])) redirect('?view=progress-reports&error=nofile');
        [$ok,$err,$filePath,$originalName]=storeUploadedFile($_FILES['file'],$student['matric_number'].'_progress_'.$formId);
        if (!$ok) redirect('?view=progress-reports&error='.urlencode($err));
        $old=safeRun($pdo,'SELECT file_path FROM progress_submissions WHERE progress_form_id=? AND student_id=?',[$formId,$studentId])->fetch();
        safeRun($pdo,"INSERT INTO progress_submissions (progress_form_id,student_id,file_path,file_name) VALUES (?,?,?,?)
            ON DUPLICATE KEY UPDATE file_path=VALUES(file_path),file_name=VALUES(file_name),submitted_at=NOW()",
            [$formId,$studentId,$filePath,$originalName]);
        if ($old && $old['file_path'] && $old['file_path']!==$filePath) @unlink(__DIR__.'/../../'.$old['file_path']);
        auditLog($pdo,$user['id'],'submit_progress_report',"Form #$formId");
        redirect('?view=progress-reports&uploaded=1');
    }
}

$pt = $student['programme_type'];
$categories = safeRun($pdo,'SELECT * FROM submission_categories WHERE programme_type=? AND (semester_id IS NULL OR semester_id=?) ORDER BY sort_order',[$pt,$currentSemesterId])->fetchAll();
$mySubmissions = safeRun($pdo,'SELECT * FROM submissions WHERE student_id=? AND (semester_id IS NULL OR semester_id=?) ORDER BY uploaded_at DESC',[$studentId,$currentSemesterId])->fetchAll();
$approvedCategoryCount=(int)safeRun($pdo,"SELECT COUNT(DISTINCT sub.category_id) c FROM submissions sub
    WHERE sub.student_id=? AND sub.semester_id<=>? AND sub.id IN (
      SELECT MAX(s2.id) FROM submissions s2 WHERE s2.student_id=? AND s2.semester_id<=>? GROUP BY s2.category_id
    ) AND sub.status='approved'",[$studentId,$studentSemesterId,$studentId,$studentSemesterId])->fetch()['c'];
$uploadedCategoryCount=count(array_unique(array_column($mySubmissions,'category_id')));
$progressPct=count($categories)?min(100,round($approvedCategoryCount/count($categories)*100)):0;
$pendingReqCount=(int)safeRun($pdo,"SELECT COUNT(*) c FROM supervisor_requests WHERE student_id=? AND semester_id<=>? AND status IN ('pending','accepted') AND coordinator_status='pending'",[$studentId,$studentSemesterId])->fetch()['c'];

$navGroups=[
 ['label'=>'Main','items'=>[
   ['icon'=>'gauge-high','label'=>'Overview','view'=>'overview'],
   ['icon'=>'user-tie','label'=>'My Supervisor','view'=>'my-supervisor','badge'=>$pendingReqCount?:null],
   ['icon'=>'users-viewfinder','label'=>'My Panels','view'=>'my-panels'],
 ]],
 ['label'=>'Project','items'=>[
   ['icon'=>'upload','label'=>'Project Submission','view'=>'submissions'],
   ['icon'=>'clock-rotate-left','label'=>'Submission History','view'=>'history'],
   ['icon'=>'file-arrow-up','label'=>'Progress Reports','view'=>'progress-reports'],
   ['icon'=>'file-circle-check','label'=>'My Reports','view'=>'my-reports'],
   ['icon'=>'chart-simple','label'=>'Evaluation Results','view'=>'results'],
   ['icon'=>'scale-balanced','label'=>'Rubrics','view'=>'rubrics'],
   ['icon'=>'calendar-days','label'=>'Timeline','view'=>'timeline'],
 ]],
 ['label'=>'Documents','items'=>[
   ['icon'=>'certificate','label'=>'Certificates','view'=>'certificates'],
   ['icon'=>'user','label'=>'Profile','view'=>'profile'],
 ]],
];
$pageTitleMap=['overview'=>'Overview','my-supervisor'=>'My Supervisor','my-panels'=>'My Panels','submissions'=>'Project Submission','history'=>'Submission History','progress-reports'=>'Progress Reports','my-reports'=>'My Reports','results'=>'Evaluation Results','rubrics'=>'Rubrics','timeline'=>'Timeline','certificates'=>'Certificates','profile'=>'Profile'];
$pageTitle=$pageTitleMap[$view]??'Dashboard';
$themeColor='#F58220';$themeColorSoft='#FFF1E2';
$roleLabelText=programmeTypeLabel($pt);
$levelBadge=strtoupper($student['level']).' STUDENT';
$activeView=$view;
require __DIR__.'/../../includes/dash_head.php';
?>

<?php if ($view==='overview'): ?>
<?php if(($student['academic_status']??'active')!=='active'): ?><div class="alert alert-info small">You do not have an active FYP enrolment in the current semester. Your historical results, rubrics and certificates remain available.</div><?php endif; ?>
<div class="row g-3 mb-4">
<?php
$evalCompleted=(int)safeRun($pdo,"SELECT COUNT(*) c FROM evaluations WHERE student_id=? AND semester_id<=>? AND status='completed'",[$studentId,$studentSemesterId])->fetch()['c'];
$cards=[
 ['diagram-project','Approved Progress',$progressPct.'%','#16A34A','#DCFCE7'],
 ['user-tie','Supervisor Status',$student['supervisor_id']?'Assigned':'Pending','#2563EB','#DBEAFE'],
 ['users-viewfinder','Panel Assignment',($student['report_panel_id']&&$student['presentation_panel_id'])?'Complete':'Pending','#F58220','#FFF1E2'],
 ['clipboard-check','Evaluations Done',$evalCompleted,'#8B5CF6','#EDE9FE'],
];
foreach($cards as [$icon,$label,$val,$color,$soft]): ?>
<div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-icon" style="background:<?= $soft ?>;color:<?= $color ?>"><i class="fa-solid fa-<?= $icon ?>"></i></div><div class="stat-value" style="<?= is_string($val)&&!is_numeric(str_replace('%','',$val))?'font-size:18px':'' ?>"><?= e((string)$val) ?></div><div class="stat-label"><?= e($label) ?></div></div></div>
<?php endforeach; ?>
</div>
<div class="row g-3">
 <div class="col-lg-7">
  <div class="card-fy mb-3">
   <?php if(($_GET['title_saved']??'')==='1'): ?><div class="alert alert-success small">Project title updated successfully.</div><?php elseif(($_GET['title_error']??'')==='1'): ?><div class="alert alert-danger small">Enter a project title of 1–255 characters.</div><?php endif; ?>
   <div class="d-flex justify-content-between align-items-center gap-2"><div class="section-title mb-0">Project Overview</div><button class="btn-fy-outline btn-sm" data-bs-toggle="modal" data-bs-target="#editTitleModal"><i class="fa-solid fa-pen me-1"></i>Edit Title</button></div>
   <h5 class="fw-bold mt-3"><?= e($student['project_title']??'No project title set') ?></h5>
   <p class="text-muted small"><?= e($student['faculty']) ?> · <?= e($student['programme']) ?> · <strong><?= programmeTypeLabel($pt) ?></strong></p>
   <div class="d-flex justify-content-between small mb-1"><span>Approved required categories</span><strong><?= $approvedCategoryCount ?>/<?= count($categories) ?></strong></div>
   <div class="progress-fy"><div style="width:<?= $progressPct ?>%;background:#16A34A"></div></div>
   <div class="small text-muted mt-2">Uploaded categories: <?= $uploadedCategoryCount ?>/<?= count($categories) ?>. Rejected work is not counted as complete.</div>
  </div>
  <div class="card-fy"><div class="section-title">Upcoming Deadlines</div>
  <?php $upcoming=safeRun($pdo,'SELECT * FROM submission_categories WHERE programme_type=? AND deadline>=CURDATE() AND (semester_id IS NULL OR semester_id=?) ORDER BY deadline LIMIT 4',[$pt,$currentSemesterId])->fetchAll(); foreach($upcoming as $u): ?>
   <div class="d-flex justify-content-between py-2 border-bottom small"><span><?= e($u['name']) ?></span><span class="text-muted"><?= date('M j, Y',strtotime($u['deadline'])) ?></span></div>
  <?php endforeach; if(empty($upcoming)): ?><p class="text-muted small mb-0">No upcoming deadlines.</p><?php endif; ?></div>
 </div>
 <div class="modal fade" id="editTitleModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="post"><?= csrfField() ?><input type="hidden" name="action" value="update_project_title"><div class="modal-header"><h6 class="modal-title fw-bold">Edit Project Title</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label small fw-semibold">Project Title</label><textarea class="form-control-fy w-100" name="project_title" rows="3" maxlength="255" required><?= e($student['project_title']??'') ?></textarea><div class="small text-muted mt-2">This update is saved immediately to your current FYP stage.</div></div><div class="modal-footer"><button class="btn-fy-orange w-100">Save Project Title</button></div></form></div></div></div>
 <div class="col-lg-5"><div class="card-fy"><div class="section-title">Recent Notifications</div>
 <?php $recentNotifs2=safeRun($pdo,'SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 5',[$user['id']])->fetchAll(); foreach($recentNotifs2 as $n): ?>
  <div class="py-2 border-bottom"><div class="small fw-semibold"><?= e($n['title']) ?></div><div class="small text-muted"><?= e($n['message']) ?></div></div>
 <?php endforeach; if(empty($recentNotifs2)): ?><p class="text-muted small mb-0">No notifications yet.</p><?php endif; ?></div></div>
</div>
<?php elseif($view==='my-supervisor'): include __DIR__.'/views/my-supervisor.php';
elseif($view==='my-panels'): include __DIR__.'/views/my-panels.php';
elseif($view==='submissions'): include __DIR__.'/views/submissions.php';
elseif($view==='history'): include __DIR__.'/views/history.php';
elseif($view==='progress-reports'): include __DIR__.'/views/progress-reports.php';
elseif($view==='my-reports'): include __DIR__.'/views/my-reports.php';
elseif($view==='results'): include __DIR__.'/views/results.php';
elseif($view==='rubrics'): include __DIR__.'/views/rubrics.php';
elseif($view==='timeline'): include __DIR__.'/views/timeline.php';
elseif($view==='certificates'): include __DIR__.'/views/certificates.php';
elseif($view==='profile'): include __DIR__.'/../shared/profile.php';
else: ?><div class="card-fy text-center py-5 text-muted">Page not found.</div><?php endif; ?>
<?php require __DIR__.'/../../includes/dash_foot.php'; ?>
