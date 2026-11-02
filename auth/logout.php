<?php
require_once __DIR__ . '/../includes/config.php';
if (isLoggedIn()) {
    auditLog($pdo, $_SESSION['user_id'], 'logout', '');
}
logoutUser();
redirect(BASE_URL . '/index.php');
