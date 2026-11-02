<?php
/**
 * FYPES - Authentication & RBAC helpers
 */

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'role' => $_SESSION['role'],
        'name' => $_SESSION['name'],
        'email' => $_SESSION['email'],
        'photo' => $_SESSION['photo'] ?? null,
        'lecturer_id' => $_SESSION['lecturer_id'] ?? null,
        'student_id' => $_SESSION['student_id'] ?? null,
    ];
}

/**
 * Returns array of lecturer role_type/programme_type rows for the logged-in lecturer.
 */
function currentLecturerRoles(PDO $pdo): array {
    if (!isLoggedIn() || $_SESSION['role'] !== 'lecturer' || empty($_SESSION['lecturer_id'])) return [];
    $semesterId = currentSemesterId($pdo);
    if (!$semesterId) return [];
    $stmt = safeRun($pdo, "SELECT * FROM lecturer_roles WHERE lecturer_id = ? AND semester_id = ? ORDER BY FIELD(role_type,'coordinator','supervisor','report_panel','presentation_panel'),programme_type", [$_SESSION['lecturer_id'],$semesterId]);
    return $stmt ? $stmt->fetchAll() : [];
}

function lecturerHasRole(PDO $pdo, string $roleType, ?string $programmeType = null): bool {
    foreach (currentLecturerRoles($pdo) as $r) {
        if ($r['role_type'] === $roleType && ($programmeType === null || $r['programme_type'] === $programmeType)) return true;
    }
    return false;
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect(BASE_URL . '/auth/login.php');
    }
}

function requireRole(string $role): void {
    if (empty($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== $role) {
        redirect(BASE_URL . '/auth/login.php');
    }
    if ($role === 'student') {
        global $pdo;
        if (isset($pdo) && studentAccessBlocked($pdo)) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params=session_get_cookie_params();
                setcookie(session_name(),'',time()-42000,$params['path'],$params['domain'],$params['secure'],$params['httponly']);
            }
            session_destroy();
            redirect(BASE_URL . '/auth/login.php?error=student_access_blocked');
        }
    }
}

function logoutUser(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    session_destroy();
}

/**
 * Get unread notification count for current user
 */
function unreadNotificationCount(PDO $pdo): int {
    if (!isLoggedIn()) return 0;
    $stmt = safeRun($pdo, "SELECT COUNT(*) c FROM notifications WHERE user_id = ? AND is_read = 0", [$_SESSION['user_id']]);
    $row = $stmt ? $stmt->fetch() : ['c' => 0];
    return (int) $row['c'];
}
