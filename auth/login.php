<?php
require_once __DIR__ . '/../includes/config.php';

if (isLoggedIn()) {
    redirect(BASE_URL . '/dashboard/' . $_SESSION['role'] . '/index.php');
}

$error = $_GET['error'] ?? null;
$registered = $_GET['registered'] ?? null;
$activeTab = in_array($_GET['role'] ?? '', ['student','lecturer','admin'], true) ? $_GET['role'] : 'student';
$systemName = getSetting($pdo, 'system_name', APP_NAME) ?: APP_NAME;
$institutionName = getSetting($pdo, 'institution_name', APP_INSTITUTION) ?: APP_INSTITUTION;
$googleReady = googleOAuthConfigured($pdo);

$settingsRows = safeRun($pdo, "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('semester_name','semester_start_date','semester_end_date')")->fetchAll();
$settings = array_column($settingsRows, 'setting_value', 'setting_key');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'] ?? null)) {
        $error = 'invalid_csrf';
    } else {
        $role = in_array($_POST['login_role'] ?? '', ['student','lecturer','admin'], true) ? $_POST['login_role'] : '';
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $activeTab = $role ?: 'student';

        if ($role === 'admin') {
            $user = safeRun($pdo, "SELECT * FROM users WHERE email=? AND role='admin'", [$email])->fetch();
            if ($user && $user['status']==='active' && password_verify($password,$user['password'])) {
                loginUserSession($pdo,$user,null,null,'password');
                if (!empty($_SESSION['must_change_password'])) redirect(BASE_URL.'/auth/change-password.php');
                redirect(BASE_URL.'/dashboard/admin/index.php');
            }
            $error = $user && $user['status']!=='active' ? 'inactive_account' : 'invalid_credentials';
        } elseif ($role === 'lecturer') {
            $user = safeRun($pdo, "SELECT u.*,l.id lecturer_id FROM users u JOIN lecturers l ON l.user_id=u.id WHERE u.email=? AND u.role='lecturer'", [$email])->fetch();
            if ($user && $user['status']==='active' && password_verify($password,$user['password'])) {
                loginUserSession($pdo,$user,(int)$user['lecturer_id'],null,'password');
                if (!empty($_SESSION['must_change_password'])) redirect(BASE_URL.'/auth/change-password.php');
                if (empty(currentLecturerRoles($pdo))) redirect(BASE_URL.'/dashboard/pending-role.php');
                redirect(BASE_URL.'/dashboard/lecturer/index.php');
            }
            $error = $user && $user['status']!=='active' ? 'inactive_account' : 'invalid_credentials';
        } elseif ($role === 'student') {
            $user = safeRun($pdo, "SELECT u.*,s.id student_id FROM users u JOIN students s ON s.user_id=u.id WHERE u.email=? AND u.role='student'", [$email])->fetch();
            if ($user && $user['status']==='active' && password_verify($password,$user['password'])) {
                if (studentAccessBlocked($pdo)) {
                    $error='student_access_blocked';
                } else {
                    loginUserSession($pdo,$user,null,(int)$user['student_id'],'password');
                    if (!empty($_SESSION['must_change_password'])) redirect(BASE_URL.'/auth/change-password.php');
                    redirect(BASE_URL.'/dashboard/student/index.php');
                }
            } elseif ($user && $user['status']!=='active') {
                $error='inactive_account';
            } else {
                $error='invalid_credentials';
            }
        } else {
            $error = 'invalid_credentials';
        }
    }
}

$errorMessages = [
    'invalid_credentials'=>'The email, password, or selected account type is incorrect.',
    'inactive_account'=>'This account is inactive. Please contact the administrator.',
    'student_access_blocked'=>'Student access is temporarily closed by the administrator for the configured date range.',
    'invalid_csrf'=>'Your session expired. Please try again.',
    'google_not_configured'=>'Google login needs a real Google OAuth Client ID and Client Secret. An administrator can enter them under Admin Dashboard → Settings → Google Sign-In.',
    'google_denied'=>'Google sign-in was cancelled or denied.',
    'google_failed'=>'Google could not verify this account. Please try again.',
    'google_domain'=>'Only official @student.unisel.edu.my student accounts and @unisel.edu.my lecturer accounts are accepted.',
    'google_account'=>'No active FYPES account matches that Google email.',
    'google_role'=>'The Google email domain does not match the selected Student or Lecturer login.',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Sign In | <?= e($systemName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>
*{box-sizing:border-box}body{font-family:'Plus Jakarta Sans',sans-serif;background:#FAFAF8;min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;position:relative;color:#1E1B2E}
.semester-badge{position:fixed;top:18px;right:18px;background:#fff;border:1px solid #F0EEE9;border-radius:12px;padding:8px 14px;font-size:11px;color:#8A857C;box-shadow:0 4px 14px rgba(20,20,20,.04);line-height:1.5}.semester-badge strong{color:#3A3735}.semester-badge .dot{color:#F58220}
.auth-card{width:100%;max-width:430px;background:#fff;border-radius:24px;padding:42px 38px;box-shadow:0 20px 60px rgba(20,20,20,.06);border:1px solid #F0EEE9}
.auth-logo{display:flex;width:max-content;margin:0 auto 8px;align-items:center;justify-content:center;gap:10px;color:#1E1B2E;text-decoration:none}.auth-logo-icon{width:40px;height:40px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:#F58220;color:#fff}.auth-logo-word{font-family:'Fraunces',serif;font-weight:800;font-size:25px}.auth-logo-word span{color:#F58220}
.role-tabs{display:flex;gap:6px;background:#FAFAF8;padding:5px;border-radius:14px;margin:22px 0;border:1px solid #F0EEE9}.role-tabs button{flex:1;text-align:center;padding:9px 5px;border:0;border-radius:10px;font-size:12.5px;font-weight:700;color:#A8A49C;background:transparent}.role-tabs button.active{background:#F58220;color:#fff}
.form-control{border-radius:12px;padding:12px 14px;border:1.5px solid #ECEBE7;font-size:14px}.form-control:focus{border-color:#F58220;box-shadow:0 0 0 4px rgba(245,130,32,.1)}
.btn-primary-fy,.google-btn{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;min-height:46px;border-radius:12px;font-weight:700;text-decoration:none}.btn-primary-fy{background:#F58220;color:#fff;border:0}.btn-primary-fy:hover{background:#DD7314}.google-btn{background:#fff;color:#3A3735;border:1.5px solid #E4E1DB}.google-btn:hover{border-color:#BDB8AF;color:#1E1B2E}.divider{display:flex;align-items:center;gap:12px;color:#A8A49C;font-size:11px;margin:16px 0}.divider:before,.divider:after{content:"";height:1px;background:#ECEBE7;flex:1}
@media(max-width:600px){body{align-items:flex-start;flex-direction:column;padding:16px}.semester-badge{position:static;width:100%;max-width:430px;margin:0 auto 12px}.auth-card{padding:31px 21px}}
</style>
</head>
<body>
<?php if(!empty($settings['semester_start_date'])): ?><div class="semester-badge"><?= e($settings['semester_name']??'Current Semester') ?><br>Start: <strong><?= date('M j, Y',strtotime($settings['semester_start_date'])) ?></strong><span class="dot mx-1">·</span>End: <strong><?= date('M j, Y',strtotime($settings['semester_end_date'])) ?></strong></div><?php endif; ?>
<div class="auth-card">
 <div class="text-center">
  <a href="../index.php" class="auth-logo" title="<?= e($systemName) ?>"><span class="auth-logo-icon"><i class="fa-solid fa-graduation-cap"></i></span><span class="auth-logo-word"><span>FY</span>PES</span></a>
  <div class="small text-muted"><?= e($institutionName) ?></div>
  <h1 class="h4 fw-bold mt-3 mb-1">Welcome</h1><p class="text-muted small mb-0">Sign in to your <?= e($systemName) ?> account</p>
 </div>
 <div class="role-tabs" role="tablist">
  <?php foreach(['student'=>'Student','lecturer'=>'Lecturer','admin'=>'Admin'] as $key=>$label): ?><button type="button" data-role="<?= $key ?>" class="<?= $activeTab===$key?'active':'' ?>"><?= $label ?></button><?php endforeach; ?>
 </div>
 <?php if($registered==='pending'): ?><div class="alert alert-success small">Account created. An administrator must approve it before you can sign in.</div><?php endif; ?>
 <?php if($error): ?><div class="alert alert-danger small"><?= e($errorMessages[$error]??'Sign-in failed.') ?></div><?php endif; ?>
 <form method="post"><?= csrfField() ?><input type="hidden" name="login_role" id="loginRole" value="<?= e($activeTab) ?>">
  <label class="form-label small fw-semibold">Email</label><input type="email" name="email" class="form-control mb-3" required autocomplete="username" placeholder="Official UNISEL email">
  <label class="form-label small fw-semibold">Password</label><input type="password" name="password" class="form-control mb-2" required autocomplete="current-password">
  <div class="text-end mb-3"><a href="forgot-password.php" class="small" style="color:#F58220;font-weight:700;text-decoration:none">Forgot password?</a></div>
  <button class="btn-primary-fy">Sign In</button>
 </form>
 <div id="googleArea" class="<?= $activeTab==='admin'?'d-none':'' ?>">
  <div class="divider">OR</div>
  <a id="googleLink" href="google-login.php?action=start&role=<?= e($activeTab) ?>" class="google-btn <?= !$googleReady?'opacity-50':'' ?>"><i class="fa-brands fa-google"></i>Continue with Google</a>
  <?php if(!$googleReady): ?><div class="small text-muted text-center mt-2">Google login is not configured yet. An administrator can add the Google OAuth credentials in Admin Settings.</div><?php endif; ?>
 </div>
 <p class="text-center small text-muted mt-4 mb-0">Student without an account? <a href="register.php" style="color:#F58220;font-weight:700;text-decoration:none">Create account</a></p>
</div>
<script>
const tabs=document.querySelectorAll('.role-tabs button'),roleInput=document.getElementById('loginRole'),googleArea=document.getElementById('googleArea'),googleLink=document.getElementById('googleLink');
tabs.forEach(btn=>btn.addEventListener('click',()=>{tabs.forEach(x=>x.classList.remove('active'));btn.classList.add('active');const role=btn.dataset.role;roleInput.value=role;googleArea.classList.toggle('d-none',role==='admin');googleLink.href='google-login.php?action=start&role='+encodeURIComponent(role);}));
</script>
</body></html>
