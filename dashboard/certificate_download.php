<?php
require_once __DIR__.'/../includes/config.php';
require_once __DIR__.'/../includes/certificate_generator.php';
requireLogin();

$type = $_GET['type'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$user = currentUser();

if ($type === 'certificate') {
    $certificate = safeRun($pdo, "SELECT c.*,u.full_name,s.id student_id,s.matric_number,s.programme_type,s.project_title,
            fr.status result_status,fr.final_score,fr.grade result_grade
        FROM certificates c
        JOIN users u ON u.id=c.user_id
        JOIN students s ON s.user_id=u.id
        JOIN final_results fr ON fr.id=c.final_result_id AND fr.student_id=s.id AND fr.semester_id<=>c.semester_id
        WHERE c.id=? AND c.cert_type='completion'", [$id])->fetch();

    if (!$certificate) {
        http_response_code(404);
        exit('Certificate not found.');
    }

    $allowed = $user['role'] === 'admin'
        || ($user['role'] === 'student' && (int)$user['id'] === (int)$certificate['user_id']);
    if (!$allowed) {
        http_response_code(403);
        exit('Access denied.');
    }

    streamCertificate('completion', $certificate['full_name'], $certificate['grade'] ?: $certificate['result_grade'], null, $certificate['cert_code'], [
        'matric_number' => $certificate['matric_number'],
        'programme_type' => $certificate['programme_type'],
        'project_title' => $certificate['project_title'],
        'final_score' => $certificate['final_score'],
        'code' => $certificate['cert_code'],
        'issued_date' => date('d M Y', strtotime($certificate['issued_at'])),
        'system_name' => getSetting($pdo, 'system_name', APP_NAME),
        'institution_name' => getSetting($pdo, 'institution_name', APP_INSTITUTION),
    ]);
    exit;
}

if ($type === 'letter') {
    $letter = safeRun($pdo, "SELECT al.*,u.id user_id,u.full_name
        FROM appointment_letters al
        JOIN lecturers l ON l.id=al.lecturer_id
        JOIN users u ON u.id=l.user_id
        WHERE al.id=?", [$id])->fetch();

    if (!$letter) {
        http_response_code(404);
        exit('Letter not found.');
    }

    $allowed = $user['role'] === 'admin'
        || ($user['role'] === 'lecturer' && (int)$user['id'] === (int)$letter['user_id'])
        || ($user['role'] === 'lecturer' && coordinatorCanManageProgramme(
            $pdo,
            (int)$user['lecturer_id'],
            $letter['programme_type'],
            (int)$letter['semester_id']
        ));

    if (!$allowed) {
        http_response_code(403);
        exit('Access denied.');
    }

    $letterType = letterTypeForRole($letter['role_type']);
    streamCertificate('invitation', $letter['full_name'], null, roleLabel($letter['role_type']), $letter['letter_code'], [
        'role_type' => $letter['role_type'],
        'letter_type' => $letterType,
        'programme_type' => $letter['programme_type'],
        'code' => $letter['letter_code'],
        'issued_date' => date('d M Y', strtotime($letter['generated_at'])),
        'system_name' => getSetting($pdo, 'system_name', APP_NAME),
        'institution_name' => getSetting($pdo, 'institution_name', APP_INSTITUTION),
    ]);
    exit;
}

http_response_code(400);
echo 'Invalid request.';
