<?php
/**
 * FYPES - shared helpers
 */

function safeRun(PDO $pdo, string $sql, array $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log('FYPES SQL error: ' . $e->getMessage() . ' | SQL: ' . $sql);
        // Database errors must propagate so callers cannot continue with partial data.
        throw $e;
    }
}

function drName(string $name): string {
    $name = trim($name);
    foreach (['Dr.', 'Prof.', 'Assoc. Prof.', 'Ir.', 'Ts.'] as $honorific) {
        if (stripos($name, $honorific) === 0) return $name;
    }
    return $name;
}

function e(?string $str): string {
    return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void {
    header('Location: ' . $path);
    exit;
}

function generateCSRFToken(): string {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken(?string $token): bool {
    return isset($_SESSION['csrf_token']) && $token !== null && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(generateCSRFToken()) . '">';
}

function programmeTypeLabel(string $pt): string {
    return [
        'diploma_fyp1' => 'Diploma FYP 1',
        'diploma_fyp2' => 'Diploma FYP 2',
        'degree_fyp1' => 'Degree FYP 1',
        'degree_fyp2' => 'Degree FYP 2',
    ][$pt] ?? $pt;
}

function roleLabel(string $role): string {
    return [
        'coordinator' => 'Coordinator',
        'supervisor' => 'Supervisor',
        'report_panel' => 'Report Panel',
        'presentation_panel' => 'Presentation Panel',
        'admin' => 'Administrator',
        'student' => 'Student',
        'lecturer' => 'Lecturer',
    ][$role] ?? ucwords(str_replace('_', ' ', $role));
}

function validProgrammeTypes(): array {
    return ['diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2'];
}

function validAcademicRoles(): array {
    return ['coordinator','supervisor','report_panel','presentation_panel'];
}

function letterTypeForRole(string $role): string {
    return in_array($role, ['coordinator','supervisor'], true) ? 'appointment' : 'invitation';
}

function letterTypeLabel(string $letterType): string {
    return $letterType === 'appointment' ? 'Appointment Letter' : 'Invitation Letter';
}

function programmeTypeMatchesLevel(string $programmeType, string $level): bool {
    return in_array($programmeType, validProgrammeTypes(), true) && str_starts_with($programmeType, $level . '_');
}

function levelFromProgramme(string $programme): ?string {
    if (stripos($programme, 'Diploma') === 0) return 'diploma';
    if (stripos($programme, 'Bachelor') === 0) return 'degree';
    return null;
}

function validateStudentEmail(string $email): bool {
    return (bool) preg_match('/^[0-9]{1,10}[a-zA-Z]?@student\.unisel\.edu\.my$/i', $email);
}

function studentEmailMatchesMatric(string $email, string $matric): bool {
    return strtolower($email) === strtolower($matric . '@student.unisel.edu.my');
}

function validateLecturerEmail(string $email): bool {
    return (bool) preg_match('/^[a-zA-Z0-9._-]+@unisel\.edu\.my$/i', $email)
        && !preg_match('/@student\.unisel\.edu\.my$/i', $email);
}

function timeAgo(string $datetime): string {
    $timestamp = strtotime($datetime);
    $diff = max(0, time() - $timestamp);
    if ($diff < 60) return 'just now';
    if ($diff < 3600) return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    if ($diff < 604800) return floor($diff / 86400) . 'd ago';
    return date('M j, Y', $timestamp);
}

function submissionStatusLabel(string $status): string {
    return [
        'approved' => 'Accepted',
        'under_review' => 'Under Review',
        'rejected' => 'Rejected',
        'submitted' => 'Submitted',
    ][$status] ?? ucwords(str_replace('_', ' ', $status));
}

function statusBadgeColor(string $status): string {
    return [
        'approved' => 'success', 'accepted' => 'success', 'completed' => 'success',
        'released' => 'success', 'active' => 'success', 'confirmed' => 'success', 'promoted' => 'success',
        'under_review' => 'warning', 'pending' => 'warning', 'planned' => 'warning', 'submitted' => 'info',
        'draft' => 'secondary', 'reopened' => 'warning',
        'rejected' => 'danger', 'failed' => 'danger', 'inactive' => 'danger', 'closed' => 'secondary',
    ][$status] ?? 'secondary';
}

function notify(PDO $pdo, int $userId, string $title, string $message, string $type = 'general', ?string $link = null): void {
    safeRun($pdo, 'INSERT INTO notifications (user_id, title, message, type, link) VALUES (?, ?, ?, ?, ?)',
        [$userId, $title, $message, $type, $link]);
}

function auditLog(PDO $pdo, ?int $userId, string $action, string $details = ''): void {
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    safeRun($pdo, 'INSERT INTO audit_logs (user_id, action, details, ip_address) VALUES (?, ?, ?, ?)',
        [$userId, $action, $details, $ip]);
}

function generateTemporaryPassword(int $length = 14): string {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    $password = '';
    for ($i=0; $i<$length; $i++) $password .= $alphabet[random_int(0, strlen($alphabet)-1)];
    return $password;
}

function generateCode(string $prefix): string {
    return $prefix . '-' . date('Y') . '-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

function formatBytes(int $bytes): string {
    if ($bytes < 1024) return $bytes . ' B';
    if ($bytes < 1048576) return round($bytes / 1024, 1) . ' KB';
    return round($bytes / 1048576, 1) . ' MB';
}

function calculatePercentage(float $score, float $maxMarks): float {
    return $maxMarks > 0 ? round(($score / $maxMarks) * 100, 2) : 0.0;
}

function getGradeFromPercentage(float $percentage): string {
    if ($percentage >= 80) return 'A';
    if ($percentage >= 70) return 'B';
    if ($percentage >= 60) return 'C';
    return 'F';
}

function getSetting(PDO $pdo, string $key, ?string $default = null): ?string {
    static $cache = [];
    if (array_key_exists($key, $cache)) return $cache[$key];
    $stmt = safeRun($pdo, 'SELECT setting_value FROM system_settings WHERE setting_key=?', [$key]);
    $row = $stmt ? $stmt->fetch() : null;
    return $cache[$key] = ($row['setting_value'] ?? $default);
}

function studentAccessBlocked(PDO $pdo, ?string $onDate = null): bool {
    $start = trim((string)getSetting($pdo, 'student_access_block_start', ''));
    $end = trim((string)getSetting($pdo, 'student_access_block_end', ''));
    if ($start === '' || $end === '') return false;
    $date = $onDate ?: date('Y-m-d');
    return $date >= $start && $date <= $end;
}

function currentSemesterId(PDO $pdo): ?int {
    $stmt = safeRun($pdo, 'SELECT id FROM semesters WHERE is_current=1 ORDER BY id DESC LIMIT 1');
    $row = $stmt ? $stmt->fetch() : null;
    return $row ? (int)$row['id'] : null;
}

function studentSemesterId(PDO $pdo, int $studentId): ?int {
    $stmt = safeRun($pdo, 'SELECT semester_id FROM students WHERE id=?', [$studentId]);
    $row = $stmt ? $stmt->fetch() : null;
    if ($row && $row['semester_id'] !== null) return (int)$row['semester_id'];
    // Inactive students may already have a planned upcoming enrolment, while
    // completed students need their latest archived semester for read-only pages.
    $history=safeRun($pdo,"SELECT h.semester_id FROM student_stage_history h JOIN semesters se ON se.id=h.semester_id WHERE h.student_id=? ORDER BY (h.status IN ('planned','active')) DESC,se.start_date DESC,h.id DESC LIMIT 1",[$studentId])->fetch();
    return $history ? (int)$history['semester_id'] : currentSemesterId($pdo);
}

function syncSemesterSettings(PDO $pdo, int $semesterId): void {
    $stmt = safeRun($pdo, 'SELECT name,start_date,end_date FROM semesters WHERE id=?', [$semesterId]);
    $semester = $stmt ? $stmt->fetch() : null;
    if (!$semester) return;
    foreach ([
        'semester_name'=>$semester['name'],
        'semester_start_date'=>$semester['start_date'],
        'semester_end_date'=>$semester['end_date'],
        'academic_session'=>$semester['name'],
    ] as $key=>$value) {
        safeRun($pdo, 'INSERT INTO system_settings (setting_key,setting_value) VALUES (?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)', [$key,$value]);
    }
}

function syncStudentStageHistory(PDO $pdo, int $studentId, string $status = 'active', string $enrolmentType = 'new', ?int $sourceSemesterId = null): void {
    $stmt = safeRun($pdo, 'SELECT id,semester_id,programme_type,project_title,supervisor_id,report_panel_id,presentation_panel_id,attempt_number FROM students WHERE id=?', [$studentId]);
    $s = $stmt ? $stmt->fetch() : null;
    if (!$s || !$s['semester_id']) return;
    safeRun($pdo, "INSERT INTO student_stage_history
        (student_id,semester_id,programme_type,project_title,supervisor_id,report_panel_id,presentation_panel_id,status,outcome,attempt_number,enrolment_type,source_semester_id)
        VALUES (?,?,?,?,?,?,?,?, 'pending',?,?,?)
        ON DUPLICATE KEY UPDATE programme_type=VALUES(programme_type),project_title=VALUES(project_title),
        supervisor_id=VALUES(supervisor_id),report_panel_id=VALUES(report_panel_id),presentation_panel_id=VALUES(presentation_panel_id),
        status=VALUES(status),attempt_number=VALUES(attempt_number),enrolment_type=VALUES(enrolment_type),
        source_semester_id=VALUES(source_semester_id),ended_at=IF(VALUES(status) IN ('active','planned'),NULL,COALESCE(ended_at,NOW()))",
        [$studentId,(int)$s['semester_id'],$s['programme_type'],$s['project_title'],$s['supervisor_id'],$s['report_panel_id'],$s['presentation_panel_id'],$status,(int)($s['attempt_number'] ?? 1),$enrolmentType,$sourceSemesterId]);
}

function lecturerHasProgrammeRole(PDO $pdo, int $lecturerId, string $role, string $programmeType, ?int $semesterId = null): bool {
    $semesterId = $semesterId ?? currentSemesterId($pdo);
    if (!$semesterId) return false;
    $stmt = safeRun($pdo, 'SELECT 1 FROM lecturer_roles lr JOIN lecturers l ON l.id=lr.lecturer_id JOIN users u ON u.id=l.user_id
        WHERE lr.lecturer_id=? AND lr.role_type=? AND lr.programme_type=? AND lr.semester_id=? AND u.status=\'active\' LIMIT 1',
        [$lecturerId, $role, $programmeType, $semesterId]);
    return (bool)($stmt && $stmt->fetch());
}

function coordinatorCanManageProgramme(PDO $pdo, int $lecturerId, string $programmeType, ?int $semesterId = null): bool {
    return lecturerHasProgrammeRole($pdo, $lecturerId, 'coordinator', $programmeType, $semesterId);
}

function expectedRubricTotal(string $programmeType, string $role): int {
    $fyp1 = str_ends_with($programmeType, 'fyp1');
    return match ($role) {
        'supervisor' => $fyp1 ? 40 : 50,
        'report_panel' => $fyp1 ? 30 : 10,
        'presentation_panel' => $fyp1 ? 20 : 30,
        'coordinator' => 10,
        default => 0,
    };
}

function ensureEvaluation(PDO $pdo, int $studentId, int $evaluatorId, string $role, string $programmeType): ?int {
    if (!in_array($role, ['supervisor','report_panel','presentation_panel','coordinator'], true)) return null;
    $rubric = safeRun($pdo, 'SELECT id FROM rubrics WHERE programme_type=? AND evaluator_role=? AND is_locked=1 ORDER BY published_at DESC,id DESC LIMIT 1',
        [$programmeType, $role])->fetch();
    if (!$rubric) return null;
    $semesterId=studentSemesterId($pdo, $studentId);
    $existingStmt = safeRun($pdo, 'SELECT id, evaluator_id, status FROM evaluations WHERE student_id=? AND evaluator_role=? AND semester_id<=>? LIMIT 1',
        [$studentId, $role, $semesterId]);
    $existing = $existingStmt ? $existingStmt->fetch() : null;
    if ($existing) {
        if ((int)$existing['evaluator_id'] !== $evaluatorId && $existing['status'] !== 'completed') {
            safeRun($pdo, 'UPDATE evaluations SET evaluator_id=?, rubric_id=?, total_score=NULL, comments=NULL, evaluated_at=NULL, locked_at=NULL, status=\'pending\' WHERE id=?',
                [$evaluatorId, $rubric['id'], $existing['id']]);
            safeRun($pdo, 'DELETE FROM evaluation_scores WHERE evaluation_id=?', [$existing['id']]);
        }
        return (int)$existing['id'];
    }
    safeRun($pdo, 'INSERT INTO evaluations (student_id,evaluator_id,evaluator_role,rubric_id,status,semester_id) VALUES (?,?,?,?,\'pending\',?)',
        [$studentId,$evaluatorId,$role,$rubric['id'],$semesterId]);
    return (int)$pdo->lastInsertId();
}

function syncStudentEvaluation(PDO $pdo, int $studentId, string $role, ?int $evaluatorId): void {
    $student = safeRun($pdo, 'SELECT programme_type FROM students WHERE id=?', [$studentId])->fetch();
    if (!$student) return;
    if ($evaluatorId === null) {
        $ev = safeRun($pdo, 'SELECT id,status FROM evaluations WHERE student_id=? AND evaluator_role=? AND semester_id<=>?', [$studentId,$role,studentSemesterId($pdo,$studentId)])->fetch();
        if ($ev && $ev['status'] !== 'completed') safeRun($pdo, 'DELETE FROM evaluations WHERE id=?', [$ev['id']]);
        return;
    }
    ensureEvaluation($pdo, $studentId, $evaluatorId, $role, $student['programme_type']);
}

function resetSubmissionReviewsForAssignment(PDO $pdo, int $studentId, string $reviewerRole): void {
    if ($reviewerRole !== 'supervisor') return;
    $semesterId = studentSemesterId($pdo, $studentId);
    safeRun($pdo, "DELETE sr FROM submission_reviews sr JOIN submissions sub ON sub.id=sr.submission_id WHERE sub.student_id=? AND sub.semester_id<=>? AND sr.reviewer_role='supervisor'", [$studentId,$semesterId]);
    safeRun($pdo, "UPDATE submissions SET status='submitted' WHERE student_id=? AND semester_id<=>?", [$studentId,$semesterId]);
}

function recalculateFinalResult(PDO $pdo, int $studentId, int $finalizedBy): ?array {
    $student=safeRun($pdo,'SELECT * FROM students WHERE id=?',[$studentId])->fetch();
    if(!$student || $student['presentation_status'] !== 'completed') return null;
    $rows = safeRun($pdo, 'SELECT ev.evaluator_role,ev.evaluator_id,ev.total_score,ev.status,ev.rubric_id,(SELECT COALESCE(SUM(weight),0) FROM rubric_criteria WHERE rubric_id=ev.rubric_id) rubric_total FROM evaluations ev WHERE ev.student_id=? AND ev.semester_id<=>?', [$studentId,$student['semester_id'] ?? currentSemesterId($pdo)])->fetchAll();
    $required = ['supervisor','report_panel','presentation_panel','coordinator'];
    $expectedEvaluators=['supervisor'=>(int)$student['supervisor_id'],'report_panel'=>(int)$student['report_panel_id'],'presentation_panel'=>(int)$student['presentation_panel_id']];
    $scores = [];
    foreach ($rows as $row) {
        $role=$row['evaluator_role'];
        if(!in_array($role,$required,true)||$row['status']!=='completed') continue;
        if(isset($expectedEvaluators[$role])&&((int)$row['evaluator_id']!==$expectedEvaluators[$role]||$expectedEvaluators[$role]===0)) return null;
        $expected=expectedRubricTotal($student['programme_type'],$role);
        if((int)$row['rubric_total']!==$expected||(float)$row['total_score']<0||(float)$row['total_score']>$expected) return null;
        $scores[$role]=(float)$row['total_score'];
    }
    foreach ($required as $role) if (!array_key_exists($role, $scores)) return null;
    $total = round(array_sum($scores), 2);
    if($total<0||$total>100) return null;
    $grade = getGradeFromPercentage($total);
    $semesterId = $student['semester_id'] !== null ? (int)$student['semester_id'] : currentSemesterId($pdo);
    safeRun($pdo, 'INSERT INTO final_results (student_id,semester_id,final_score,grade,status,finalized_by,finalized_at)
        VALUES (?,?,?,?,\'finalized\',?,NOW()) ON DUPLICATE KEY UPDATE final_score=VALUES(final_score),grade=VALUES(grade),status=\'finalized\',finalized_by=VALUES(finalized_by),finalized_at=NOW(),released_at=NULL',
        [$studentId,$semesterId,$total,$grade,$finalizedBy]);
    return ['score'=>$total,'grade'=>$grade];
}

function secureFileUrl(int $id, string $kind): string {
    if ($kind === 'appointment_letter') return BASE_URL . '/dashboard/certificate_download.php?type=letter&id=' . $id;
    return BASE_URL . '/dashboard/download.php?kind=' . rawurlencode($kind) . '&id=' . $id;
}

function allowedUploadMimeMap(): array {
    return [
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword','application/CDFV2','application/x-ole-storage'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/zip'],
        'ppt' => ['application/vnd.ms-powerpoint','application/CDFV2','application/x-ole-storage'],
        'pptx' => ['application/vnd.openxmlformats-officedocument.presentationml.presentation','application/zip'],
        'jpg' => ['image/jpeg'], 'jpeg' => ['image/jpeg'], 'png' => ['image/png'],
    ];
}

function validateUploadedFile(array $file, array $allowedExt = ALLOWED_UPLOAD_EXT): array {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return [false, 'upload'];
    $maxMb = max(1, (int)getSetting($GLOBALS['pdo'], 'max_file_size_mb', (string)MAX_UPLOAD_SIZE_MB));
    if (($file['size'] ?? 0) > $maxMb * 1048576) return [false, 'filesize'];
    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt, true)) return [false, 'filetype'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $map = allowedUploadMimeMap();
    if (!isset($map[$ext]) || !in_array($mime, $map[$ext], true)) return [false, 'filetype'];
    return [true, $ext];
}

function storeUploadedFile(array $file, string $prefix, string $directory = 'uploads/submissions', array $allowedExt = ALLOWED_UPLOAD_EXT): array {
    [$ok, $info] = validateUploadedFile($file, $allowedExt);
    if (!$ok) return [false, $info, null, null];
    $directory = trim($directory, '/');
    $absoluteDir = dirname(__DIR__) . '/' . $directory . '/';
    if (!is_dir($absoluteDir) && !mkdir($absoluteDir, 0750, true) && !is_dir($absoluteDir)) return [false,'upload',null,null];
    $safeBase = preg_replace('/[^A-Za-z0-9_-]/', '_', pathinfo(basename($file['name']), PATHINFO_FILENAME));
    $safeBase = trim($safeBase, '._-') ?: 'document';
    $storedName = $prefix . '_' . bin2hex(random_bytes(12)) . '_' . $safeBase . '.' . $info;
    $target = $absoluteDir . $storedName;
    if (!move_uploaded_file($file['tmp_name'], $target)) return [false,'upload',null,null];
    @chmod($target, 0640);
    return [true,null,$directory . '/' . $storedName,basename($file['name'])];
}


/* ============================================================================
 * FYPES 2026 workflow helpers
 * ========================================================================== */

function validateMatricNumber(string $matric): bool {
    return (bool)preg_match('/^[0-9]{1,10}[A-Za-z]?$/', trim($matric));
}

function programmeOptions(): array {
    return [
        'diploma' => [
            'Diploma in Computer Science',
            'Diploma in Information Technology',
            'Diploma in Software Engineering',
        ],
        'degree' => [
            'Bachelor of Computer Science (Hons)',
            'Bachelor of Information Technology (Hons)',
            'Bachelor of Software Engineering (Hons)',
        ],
    ];
}

function programmeIsAllowedForLevel(string $programme, string $level): bool {
    $options = programmeOptions();
    return isset($options[$level]) && in_array($programme, $options[$level], true);
}

function supervisorCurrentLoad(PDO $pdo, int $lecturerId, ?int $semesterId = null): int {
    $semesterId ??= currentSemesterId($pdo);
    $stmt = safeRun($pdo, "
        SELECT COUNT(DISTINCT student_id) AS c FROM (
            SELECT id AS student_id
            FROM students
            WHERE supervisor_id=? AND semester_id<=>
            ?
            UNION
            SELECT sr.student_id
            FROM supervisor_requests sr
            JOIN students s ON s.id=sr.student_id
            WHERE sr.supervisor_id=? AND sr.semester_id<=>?
              AND sr.status='accepted' AND sr.coordinator_status='pending'
              AND s.supervisor_id IS NULL
        ) loads
    ", [$lecturerId,$semesterId,$lecturerId,$semesterId]);
    return (int)($stmt->fetch()['c'] ?? 0);
}

function googleOAuthConfig(PDO $pdo): array {
    $dbClientId = trim((string)getSetting($pdo, 'google_client_id', ''));
    $dbClientSecret = trim((string)getSetting($pdo, 'google_client_secret', ''));
    $dbRedirect = trim((string)getSetting($pdo, 'google_redirect_uri', ''));
    return [
        'client_id' => $dbClientId !== '' ? $dbClientId : GOOGLE_CLIENT_ID,
        'client_secret' => $dbClientSecret !== '' ? $dbClientSecret : GOOGLE_CLIENT_SECRET,
        'redirect_uri' => $dbRedirect !== '' ? $dbRedirect : GOOGLE_REDIRECT_URI,
    ];
}

function googleOAuthConfigured(?PDO $pdo = null): bool {
    if ($pdo === null) return GOOGLE_CLIENT_ID !== '' && GOOGLE_CLIENT_SECRET !== '' && GOOGLE_REDIRECT_URI !== '';
    $cfg = googleOAuthConfig($pdo);
    return $cfg['client_id'] !== '' && $cfg['client_secret'] !== '' && $cfg['redirect_uri'] !== ''
        && !str_contains($cfg['client_id'], 'YOUR_') && !str_contains($cfg['client_secret'], 'YOUR_');
}

function emailRuntimeConfig(PDO $pdo): array {
    $db = static function(string $key) use ($pdo): string {
        return trim((string)getSetting($pdo, $key, ''));
    };
    $host = $db('smtp_host');
    $port = $db('smtp_port');
    $username = $db('smtp_username');
    $password = (string)getSetting($pdo, 'smtp_password', '');
    $encryption = strtolower($db('smtp_encryption'));
    $fromEmail = $db('mail_from_email');
    $fromName = $db('mail_from_name');
    return [
        'host' => $host !== '' ? $host : SMTP_HOST,
        'port' => (int)($port !== '' ? $port : SMTP_PORT),
        'username' => $username !== '' ? $username : SMTP_USERNAME,
        'password' => $password !== '' ? $password : SMTP_PASSWORD,
        'encryption' => $encryption !== '' ? $encryption : SMTP_ENCRYPTION,
        'from_email' => $fromEmail !== '' ? $fromEmail : (($username !== '' ? $username : SMTP_USERNAME) ?: MAIL_FROM_EMAIL),
        'from_name' => $fromName !== '' ? $fromName : MAIL_FROM_NAME,
    ];
}

function nextDetailedRubricVersion(PDO $pdo, string $programmeType, string $role): string {
    $versions = safeRun($pdo, 'SELECT version FROM detailed_rubrics WHERE programme_type=? AND evaluator_role=?', [$programmeType,$role])->fetchAll();
    $max = 0.0;
    foreach ($versions as $row) {
        $value = (float)preg_replace('/[^0-9.]/', '', (string)$row['version']);
        $max = max($max, $value);
    }
    $candidate = number_format($max > 0 ? $max + 0.1 : 1.0, 1, '.', '');
    while (safeRun($pdo, 'SELECT 1 FROM detailed_rubrics WHERE programme_type=? AND evaluator_role=? AND version=? LIMIT 1', [$programmeType,$role,$candidate])->fetch()) {
        $candidate = number_format((float)$candidate + 0.1, 1, '.', '');
    }
    return $candidate;
}

function loginUserSession(PDO $pdo, array $user, ?int $lecturerId = null, ?int $studentId = null, string $method = 'password'): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['full_name'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['photo'] = $user['profile_photo'] ?? null;
    $_SESSION['must_change_password'] = (int)($user['must_change_password'] ?? 0);
    if ($lecturerId !== null) $_SESSION['lecturer_id'] = $lecturerId;
    if ($studentId !== null) $_SESSION['student_id'] = $studentId;
    auditLog($pdo, (int)$user['id'], 'login', ucfirst($user['role']) . ' login via ' . $method);
}

function httpPostForm(string $url, array $data): ?array {
    $body = http_build_query($data, '', '&', PHP_QUERY_RFC3986);
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $response = curl_exec($ch);
        $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if ($response === false || $status < 200 || $status >= 300) return null;
    } else {
        $context = stream_context_create(['http'=>[
            'method'=>'POST','timeout'=>20,'ignore_errors'=>true,
            'header'=>"Content-Type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($body)."\r\n",
            'content'=>$body,
        ]]);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) return null;
    }
    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : null;
}

function httpGetJson(string $url, array $headers = []): ?array {
    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER=>true,
            CURLOPT_TIMEOUT=>20,
            CURLOPT_HTTPHEADER=>$headers,
        ]);
        $response=curl_exec($ch);
        $status=(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE);
        curl_close($ch);
        if($response===false||$status<200||$status>=300)return null;
    } else {
        $context=stream_context_create(['http'=>['method'=>'GET','timeout'=>20,'ignore_errors'=>true,'header'=>implode("\r\n",$headers)]]);
        $response=@file_get_contents($url,false,$context);
        if($response===false)return null;
    }
    $decoded=json_decode($response,true);
    return is_array($decoded)?$decoded:null;
}

function smtpReadResponse($socket): string {
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) < 4 || $line[3] === ' ') break;
    }
    return $response;
}

function smtpCommand($socket, string $command, array $expectedCodes): bool {
    if ($command !== '') fwrite($socket, $command . "\r\n");
    $response = smtpReadResponse($socket);
    $code = (int)substr($response, 0, 3);
    return in_array($code, $expectedCodes, true);
}

function sendSystemEmail(PDO $pdo, string $to, string $subject, string $htmlBody, ?string $plainBody = null): bool {
    $cfg = emailRuntimeConfig($pdo);
    $plainBody ??= trim(html_entity_decode(strip_tags(preg_replace('/<br\s*\/?>/i', "\n", $htmlBody)), ENT_QUOTES, 'UTF-8'));
    $boundary = 'b_' . bin2hex(random_bytes(12));
    $fromEmail = $cfg['from_email'] !== '' ? $cfg['from_email'] : 'noreply@localhost';
    $fromName = $cfg['from_name'] !== '' ? $cfg['from_name'] : 'FYPES';
    $headers = [
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $fromEmail,
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];
    $message = "--$boundary\r\nContent-Type: text/plain; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n$plainBody\r\n";
    $message .= "--$boundary\r\nContent-Type: text/html; charset=UTF-8\r\nContent-Transfer-Encoding: 8bit\r\n\r\n$htmlBody\r\n--$boundary--\r\n";
    $sent = false;
    $error = null;
    $provider = $cfg['host'] !== '' ? 'smtp' : 'php_mail';

    if ($cfg['host'] !== '') {
        $transport = $cfg['encryption'] === 'ssl' ? 'ssl://' : '';
        $context = stream_context_create(['ssl'=>[
            'verify_peer'=>true,
            'verify_peer_name'=>true,
            'allow_self_signed'=>false,
            'SNI_enabled'=>true,
        ]]);
        $socket = @stream_socket_client($transport . $cfg['host'] . ':' . max(1,$cfg['port']), $errno, $errstr, 25, STREAM_CLIENT_CONNECT, $context);
        if ($socket) {
            stream_set_timeout($socket, 25);
            $ok = smtpCommand($socket, '', [220]);
            $hostName = $_SERVER['SERVER_NAME'] ?? 'localhost';
            $ok = $ok && smtpCommand($socket, 'EHLO ' . $hostName, [250]);
            if ($ok && $cfg['encryption'] === 'tls') {
                $ok = smtpCommand($socket, 'STARTTLS', [220]);
                if ($ok) {
                    $ok = @stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
                    if ($ok) $ok = smtpCommand($socket, 'EHLO ' . $hostName, [250]);
                }
            }
            if ($ok && $cfg['username'] !== '') {
                $ok = smtpCommand($socket, 'AUTH LOGIN', [334])
                    && smtpCommand($socket, base64_encode($cfg['username']), [334])
                    && smtpCommand($socket, base64_encode($cfg['password']), [235]);
            }
            $ok = $ok && smtpCommand($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            $ok = $ok && smtpCommand($socket, 'RCPT TO:<' . $to . '>', [250,251]);
            $ok = $ok && smtpCommand($socket, 'DATA', [354]);
            if ($ok) {
                $safeSubject = str_replace(["\r","\n"], '', $subject);
                $raw = 'To: <' . $to . ">\r\nSubject: " . $safeSubject . "\r\n" . implode("\r\n", $headers) . "\r\n\r\n" . $message;
                $raw = preg_replace('/^\./m', '..', $raw);
                fwrite($socket, $raw . "\r\n.\r\n");
                $sent = in_array((int)substr(smtpReadResponse($socket),0,3), [250], true);
            }
            @smtpCommand($socket, 'QUIT', [221,250]);
            fclose($socket);
            if (!$sent) $error = 'SMTP delivery failed. Check the host, port, encryption, username, password, and sender address.';
        } else {
            $error = "SMTP connection failed: $errstr";
        }
    } else {
        $sent = @mail($to, $subject, $message, implode("\r\n", $headers));
        if (!$sent) $error = 'PHP mail() returned false. Configure SMTP in Admin Settings or configure Laragon sendmail.';
    }

    try {
        safeRun($pdo, 'INSERT INTO email_delivery_logs (recipient,subject,provider,status,error_message) VALUES (?,?,?,?,?)',
            [$to,$subject,$provider,$sent?'sent':'failed',$error]);
    } catch (Throwable $ignored) {
        error_log('FYPES email log unavailable: '.$ignored->getMessage());
    }
    return $sent;
}

function sendTemporaryPasswordEmail(PDO $pdo, string $to, string $name, string $temporaryPassword, string $role = 'lecturer'): bool {
    $role = in_array($role, ['student','lecturer'], true) ? $role : 'lecturer';
    $systemName = getSetting($pdo,'system_name',APP_NAME) ?: APP_NAME;
    $loginUrl = APP_URL . '/auth/login.php?role=' . rawurlencode($role);
    $roleLabel = ucfirst($role);
    $subject = $systemName . ' ' . strtolower($roleLabel) . ' account created';
    $html = '<div style="font-family:Arial,sans-serif;line-height:1.6;color:#222">'
        . '<h2 style="color:#F58220">'.e($systemName).'</h2>'
        . '<p>Hello '.e($name).',</p>'
        . '<p>Your '.e(strtolower($roleLabel)).' account has been created.</p>'
        . '<p><strong>Email:</strong> '.e($to).'<br><strong>Temporary password:</strong> <code style="font-size:16px">'.e($temporaryPassword).'</code></p>'
        . '<p><a href="'.e($loginUrl).'">Sign in to FYPES</a>. You must create a new password at your first login.</p>'
        . '<p>Do not share this temporary password.</p></div>';
    return sendSystemEmail($pdo,$to,$subject,$html);
}

function latestPublishedDetailedRubric(PDO $pdo, string $programmeType, string $role): ?array {
    $stmt=safeRun($pdo,"SELECT * FROM detailed_rubrics WHERE programme_type=? AND evaluator_role=? AND status='published' ORDER BY published_at DESC,id DESC LIMIT 1",[$programmeType,$role]);
    $row=$stmt->fetch();
    return $row ?: null;
}

function detailedRubricTotal(PDO $pdo, int $rubricId): float {
    $stmt=safeRun($pdo,'SELECT COALESCE(SUM(max_marks),0) total FROM detailed_rubric_criteria WHERE detailed_rubric_id=?',[$rubricId]);
    return (float)($stmt->fetch()['total'] ?? 0);
}

function roleProgrammesForLecturer(PDO $pdo, int $lecturerId, string $role, ?int $semesterId = null): array {
    $semesterId = $semesterId ?? currentSemesterId($pdo);
    if (!$semesterId) return [];
    $stmt=safeRun($pdo,'SELECT programme_type FROM lecturer_roles WHERE lecturer_id=? AND role_type=? AND semester_id=? ORDER BY FIELD(programme_type,\'diploma_fyp1\',\'diploma_fyp2\',\'degree_fyp1\',\'degree_fyp2\')',[$lecturerId,$role,$semesterId]);
    return array_column($stmt->fetchAll(),'programme_type');
}


/**
 * Build the student enrolment plan for an upcoming semester.
 *
 * - FYP1 pass  -> FYP2
 * - FYP1 fail  -> retake FYP1
 * - FYP2 pass  -> completed, no new active enrolment
 * - FYP2 fail  -> retake FYP2
 * - unreleased FYP1 -> provisional FYP2 plan, refreshed when the result is released
 * - unreleased FYP2 -> no upcoming enrolment
 */
function prepareSemesterRollover(PDO $pdo, int $targetSemesterId, int $sourceSemesterId): array {
    if ($targetSemesterId === $sourceSemesterId) return ['promoted'=>0,'retakes'=>0,'completed'=>0,'pending'=>0];
    $target=safeRun($pdo,'SELECT id,start_date FROM semesters WHERE id=?',[$targetSemesterId])->fetch();
    $source=safeRun($pdo,'SELECT id FROM semesters WHERE id=?',[$sourceSemesterId])->fetch();
    if(!$target||!$source)return ['promoted'=>0,'retakes'=>0,'completed'=>0,'pending'=>0];

    // Rebuild only system-prepared rows. New/manual registrations remain untouched.
    safeRun($pdo,"DELETE FROM student_stage_history WHERE semester_id=? AND source_semester_id=? AND status='planned' AND enrolment_type IN ('promoted','retake')",[$targetSemesterId,$sourceSemesterId]);
    $rows=safeRun($pdo,"SELECT * FROM student_stage_history WHERE semester_id=? AND status IN ('promoted','failed','completed') ORDER BY student_id",[$sourceSemesterId])->fetchAll();
    $counts=['promoted'=>0,'retakes'=>0,'completed'=>0,'pending'=>0];
    foreach($rows as $row){
        if($row['status']==='completed'){$counts['completed']++;continue;}
        $isFyp1=str_ends_with($row['programme_type'],'fyp1');
        if($row['status']==='promoted'&&$isFyp1){
            $nextProgramme=str_replace('fyp1','fyp2',$row['programme_type']);$type='promoted';$attempt=1;$counts['promoted']++;
        }elseif($row['status']==='failed'){
            $nextProgramme=$row['programme_type'];$type='retake';$attempt=(int)($row['attempt_number']??1)+1;$counts['retakes']++;
        }else{$counts['pending']++;continue;}
        safeRun($pdo,"INSERT INTO student_stage_history
          (student_id,semester_id,programme_type,project_title,status,outcome,attempt_number,enrolment_type,source_semester_id,started_at)
          VALUES (?,?,?,?, 'planned','pending',?,?,?,?)
          ON DUPLICATE KEY UPDATE programme_type=VALUES(programme_type),project_title=VALUES(project_title),
          status=IF(enrolment_type IN ('new','manual'),status,'planned'),
          attempt_number=IF(enrolment_type IN ('new','manual'),attempt_number,VALUES(attempt_number)),
          enrolment_type=IF(enrolment_type IN ('new','manual'),enrolment_type,VALUES(enrolment_type)),
          source_semester_id=IF(enrolment_type IN ('new','manual'),source_semester_id,VALUES(source_semester_id)),
          supervisor_id=NULL,report_panel_id=NULL,presentation_panel_id=NULL,ended_at=NULL",
          [(int)$row['student_id'],$targetSemesterId,$nextProgramme,$row['project_title'],$attempt,$type,$sourceSemesterId,$target['start_date'].' 00:00:00']);
    }
    safeRun($pdo,'UPDATE semesters SET source_semester_id=?,rollover_prepared_at=NOW() WHERE id=?',[$sourceSemesterId,$targetSemesterId]);
    return $counts;
}

function activateSemester(PDO $pdo, int $targetSemesterId): array {
    $target=safeRun($pdo,'SELECT * FROM semesters WHERE id=?',[$targetSemesterId])->fetch();
    if(!$target)throw new RuntimeException('Semester not found.');
    $current=safeRun($pdo,'SELECT * FROM semesters WHERE is_current=1 ORDER BY id DESC LIMIT 1')->fetch();
    $sourceSemesterId=(int)($target['source_semester_id']?:($current['id']??0));
    if($sourceSemesterId&&$sourceSemesterId!==$targetSemesterId)prepareSemesterRollover($pdo,$targetSemesterId,$sourceSemesterId);

    safeRun($pdo,"UPDATE students SET semester_id=NULL,academic_status='inactive',supervisor_id=NULL,report_panel_id=NULL,presentation_panel_id=NULL,presentation_date=NULL,presentation_venue=NULL,presentation_status='not_scheduled' WHERE semester_id IS NOT NULL");
    $planned=safeRun($pdo,"SELECT * FROM student_stage_history WHERE semester_id=? AND status IN ('planned','active') ORDER BY student_id",[$targetSemesterId])->fetchAll();
    foreach($planned as $row){
        safeRun($pdo,"UPDATE students SET programme_type=?,project_title=?,semester_id=?,academic_status='active',attempt_number=?,supervisor_id=NULL,report_panel_id=NULL,presentation_panel_id=NULL,presentation_date=NULL,presentation_venue=NULL,presentation_status='not_scheduled' WHERE id=?",[$row['programme_type'],$row['project_title'],$targetSemesterId,(int)$row['attempt_number'],(int)$row['student_id']]);
        safeRun($pdo,"UPDATE student_stage_history SET status='active',started_at=COALESCE(started_at,?),ended_at=NULL WHERE id=?",[$target['start_date'].' 00:00:00',(int)$row['id']]);
    }
    if($sourceSemesterId&&$sourceSemesterId!==$targetSemesterId){
        safeRun($pdo,"UPDATE students s JOIN student_stage_history h ON h.student_id=s.id AND h.semester_id=? SET s.academic_status='completed' WHERE h.status='completed' AND s.semester_id IS NULL",[$sourceSemesterId]);
    }
    safeRun($pdo,'UPDATE semesters SET is_current=0');
    safeRun($pdo,'UPDATE semesters SET is_current=1,activated_at=NOW() WHERE id=?',[$targetSemesterId]);
    syncSemesterSettings($pdo,$targetSemesterId);
    return ['active_students'=>(int)safeRun($pdo,"SELECT COUNT(*) c FROM students WHERE semester_id=? AND academic_status='active'",[$targetSemesterId])->fetch()['c'],'lecturers'=>(int)safeRun($pdo,"SELECT COUNT(*) c FROM lecturers l JOIN users u ON u.id=l.user_id WHERE u.status='active'")->fetch()['c'],'roles'=>(int)safeRun($pdo,'SELECT COUNT(*) c FROM lecturer_roles WHERE semester_id=?',[$targetSemesterId])->fetch()['c']];
}

function semesterProgrammeResults(PDO $pdo, int $semesterId, array $programmeTypes = []): array {
    $params=[$semesterId];
    $where='';
    if($programmeTypes){
        $where=' AND h.programme_type IN ('.implode(',',array_fill(0,count($programmeTypes),'?')).')';
        $params=array_merge($params,$programmeTypes);
    }
    return safeRun($pdo,"SELECT h.*,s.matric_number,u.full_name,u.email,fr.final_score,fr.grade,fr.status result_status
        FROM student_stage_history h
        JOIN students s ON s.id=h.student_id
        JOIN users u ON u.id=s.user_id
        LEFT JOIN final_results fr ON fr.student_id=h.student_id AND fr.semester_id=h.semester_id
        WHERE h.semester_id=? $where
        ORDER BY FIELD(h.programme_type,'diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2'),u.full_name",$params)->fetchAll();
}
