<?php
/** FYPES core configuration */
$localConfigFile = __DIR__ . '/local_config.php';
$localConfig = file_exists($localConfigFile) ? require $localConfigFile : [];
if (!is_array($localConfig)) $localConfig = [];
$configValue = static function(string $envName, string $localKey, $default = '') use ($localConfig) {
    $env = getenv($envName);
    return ($env !== false && $env !== '') ? $env : ($localConfig[$localKey] ?? $default);
};

define('DEBUG_MODE', false);
ini_set('display_errors', DEBUG_MODE ? '1' : '0');
ini_set('display_startup_errors', DEBUG_MODE ? '1' : '0');
error_reporting(DEBUG_MODE ? E_ALL : 0);

define('DB_HOST', $configValue('FYPES_DB_HOST','db_host','localhost'));
define('DB_NAME', $configValue('FYPES_DB_NAME','db_name','fypes_db'));
define('DB_USER', $configValue('FYPES_DB_USER','db_user','root'));
define('DB_PASS', $configValue('FYPES_DB_PASS','db_pass',''));
define('DB_CHARSET', 'utf8mb4');

define('APP_NAME', 'FYPES');
define('APP_FULL_NAME', 'Final Year Project Evaluation System');
define('APP_INSTITUTION', 'Universiti Selangor (UNISEL)');
define('BASE_URL', $configValue('FYPES_BASE_URL','base_url','/fypes'));
define('APP_URL', rtrim($configValue('FYPES_APP_URL','app_url','http://localhost/fypes'), '/'));
define('MAX_UPLOAD_SIZE_MB', 50);
define('ALLOWED_UPLOAD_EXT', ['pdf','doc','docx','ppt','pptx','jpg','jpeg','png']);

/*
 * Google sign-in configuration.
 * Create a Google OAuth 2.0 Web application and register:
 *   http://localhost/fypes/auth/google-login.php
 * as an authorised redirect URI, then set these environment variables.
 */
define('GOOGLE_CLIENT_ID', trim((string)($configValue('FYPES_GOOGLE_CLIENT_ID','google_client_id',''))));
define('GOOGLE_CLIENT_SECRET', trim((string)($configValue('FYPES_GOOGLE_CLIENT_SECRET','google_client_secret',''))));
define('GOOGLE_REDIRECT_URI', trim((string)($configValue('FYPES_GOOGLE_REDIRECT_URI','google_redirect_uri',APP_URL . '/auth/google-login.php'))));

/*
 * Email configuration. SMTP is used when FYPES_SMTP_HOST is set.
 * Otherwise PHP mail() is used, which can be configured through Laragon sendmail.
 */
define('SMTP_HOST', trim((string)($configValue('FYPES_SMTP_HOST','smtp_host',''))));
define('SMTP_PORT', (int)($configValue('FYPES_SMTP_PORT','smtp_port',587)));
define('SMTP_USERNAME', trim((string)($configValue('FYPES_SMTP_USERNAME','smtp_username',''))));
define('SMTP_PASSWORD', (string)($configValue('FYPES_SMTP_PASSWORD','smtp_password','')));
define('SMTP_ENCRYPTION', strtolower(trim((string)($configValue('FYPES_SMTP_ENCRYPTION','smtp_encryption','tls'))))); // tls, ssl, none
define('MAIL_FROM_EMAIL', trim((string)($configValue('FYPES_MAIL_FROM_EMAIL','mail_from_email','noreply@unisel.edu.my'))));
define('MAIL_FROM_NAME', trim((string)($configValue('FYPES_MAIL_FROM_NAME','mail_from_name','FYPES'))));

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
date_default_timezone_set('Asia/Kuala_Lumpur');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]
    );
} catch (PDOException $e) {
    error_log('FYPES database connection error: ' . $e->getMessage());
    die(DEBUG_MODE ? htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') : 'The database is unavailable. Please start MySQL and verify the FYPES database configuration.');
}

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Normalize the lecturer department once per session for existing databases.
if (empty($_SESSION['fypes_department_normalized'])) {
    try {
        safeRun($pdo, "UPDATE lecturers SET department='Department of Computing' WHERE department IS NULL OR department<>'Department of Computing'");
        $_SESSION['fypes_department_normalized'] = true;
    } catch (Throwable $e) {
        error_log('FYPES department normalization failed: ' . $e->getMessage());
    }
}

