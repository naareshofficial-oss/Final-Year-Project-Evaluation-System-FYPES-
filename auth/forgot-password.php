<?php
require_once __DIR__.'/../includes/config.php';
if(isLoggedIn())redirect(BASE_URL.'/dashboard/'.$_SESSION['role'].'/index.php');

$systemName=getSetting($pdo,'system_name',APP_NAME)?:APP_NAME;
$error=null;$success=null;
$step=(!empty($_SESSION['password_reset_user_id'])&&(int)($_SESSION['password_reset_expires']??0)>=time())?'reset':'verify';

if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!verifyCSRFToken($_POST['csrf_token']??null))$error='Your session expired. Please try again.';
    elseif(($_POST['action']??'')==='verify_identity'){
        $attempts=(int)($_SESSION['password_reset_attempts']??0);
        $lastAttempt=(int)($_SESSION['password_reset_last_attempt']??0);
        if($attempts>=8&&$lastAttempt>time()-900){$error='Too many attempts. Please wait 15 minutes.';}
        else{
            if($lastAttempt<=time()-900)$attempts=0;
            $_SESSION['password_reset_attempts']=$attempts+1;$_SESSION['password_reset_last_attempt']=time();
            $role=in_array($_POST['role']??'', ['student','lecturer','admin'], true)?$_POST['role']:'';
            $email=strtolower(trim($_POST['email']??''));$verification=trim($_POST['verification_value']??'');
            $user=safeRun($pdo,'SELECT * FROM users WHERE LOWER(email)=? AND role=? AND status=\'active\'',[$email,$role])->fetch();
            $verified=false;
            if($user&&$role==='student'){
                $profile=safeRun($pdo,'SELECT matric_number FROM students WHERE user_id=?',[$user['id']])->fetch();
                $verified=$profile&&strcasecmp(trim($profile['matric_number']),$verification)===0;
            }elseif($user&&in_array($role,['lecturer','admin'],true)){
                $normalise=fn($v)=>preg_replace('/\D+/','',(string)$v);
                $verified=$normalise($user['phone'])!==''&&hash_equals($normalise($user['phone']),$normalise($verification));
            }
            if($verified){
                session_regenerate_id(true);
                $_SESSION['password_reset_user_id']=(int)$user['id'];
                $_SESSION['password_reset_expires']=time()+900;
                $_SESSION['password_reset_role']=$role;
                unset($_SESSION['password_reset_attempts'],$_SESSION['password_reset_last_attempt']);
                $step='reset';
            }else $error='The account details could not be verified.';
        }
    }elseif(($_POST['action']??'')==='reset_password'){
        $uid=(int)($_SESSION['password_reset_user_id']??0);$expires=(int)($_SESSION['password_reset_expires']??0);
        $new=$_POST['new_password']??'';$confirm=$_POST['confirm_password']??'';
        if(!$uid||$expires<time()){$error='The reset session expired. Verify your identity again.';$step='verify';unset($_SESSION['password_reset_user_id'],$_SESSION['password_reset_expires'],$_SESSION['password_reset_role']);}
        elseif(strlen($new)<10||!preg_match('/[A-Z]/',$new)||!preg_match('/[a-z]/',$new)||!preg_match('/\d/',$new))$error='Use at least 10 characters with uppercase, lowercase and a number.';
        elseif($new!==$confirm)$error='The passwords do not match.';
        else{
            $old=safeRun($pdo,'SELECT password FROM users WHERE id=?',[$uid])->fetch();
            if($old&&password_verify($new,$old['password']))$error='Choose a password different from the current password.';
            else{
                safeRun($pdo,'UPDATE users SET password=?,must_change_password=0,updated_at=NOW() WHERE id=?',[password_hash($new,PASSWORD_BCRYPT),$uid]);
                auditLog($pdo,$uid,'forgot_password_reset','Password reset after identity verification');
                unset($_SESSION['password_reset_user_id'],$_SESSION['password_reset_expires'],$_SESSION['password_reset_role']);
                session_regenerate_id(true);
                $success='Password changed successfully. You can now sign in with your new password.';$step='verify';
            }
        }
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset Password | <?= e($systemName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Fraunces:wght@600;700;800&display=swap" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>*{box-sizing:border-box}body{background:#FAFAF8;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Plus Jakarta Sans',sans-serif;padding:20px;color:#1E1B2E}.box{width:100%;max-width:490px;background:#fff;border:1px solid #EEECE7;border-radius:22px;padding:38px;box-shadow:0 18px 50px rgba(0,0,0,.06)}.auth-logo{display:inline-flex;align-items:center;gap:10px;color:#1E1B2E;text-decoration:none}.auth-logo-icon{width:40px;height:40px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:#F58220;color:#fff}.auth-logo-word{font-family:'Fraunces',serif;font-weight:800;font-size:25px}.auth-logo-word span{color:#F58220}.form-control,.form-select{border-radius:11px;padding:11px 13px;border:1.5px solid #ECEBE7}.form-control:focus,.form-select:focus{border-color:#F58220;box-shadow:0 0 0 4px rgba(245,130,32,.1)}.btnx{display:flex;align-items:center;justify-content:center;width:100%;background:#F58220;color:#fff;padding:12px;border-radius:12px;border:0;font-weight:700}.btnx:hover{background:#DD7314}.hint{font-size:11.5px;color:#8A857C}@media(max-width:520px){.box{padding:29px 20px}}</style></head>
<body><div class="box"><div class="text-center mb-4"><a href="../index.php" class="auth-logo"><span class="auth-logo-icon"><i class="fa-solid fa-graduation-cap"></i></span><span class="auth-logo-word"><span>FY</span>PES</span></a></div>
<h1 class="h4 fw-bold"><?= $step==='reset'?'Create a new password':'Forgot your password?' ?></h1>
<p class="text-muted small"><?= $step==='reset'?'Your identity is verified. This reset session expires after 15 minutes.':'Verify your account details, then choose a new password. This works for Student, Lecturer, and Admin accounts.' ?></p>
<?php if($error): ?><div class="alert alert-danger small"><?= e($error) ?></div><?php endif; ?><?php if($success): ?><div class="alert alert-success small"><?= e($success) ?></div><?php endif; ?>
<?php if($step==='verify'): ?><form method="post"><?= csrfField() ?><input type="hidden" name="action" value="verify_identity">
<label class="form-label small fw-semibold">Account type</label><select name="role" id="resetRole" class="form-select mb-3" required><option value="student">Student</option><option value="lecturer">Lecturer</option><option value="admin">Admin</option></select>
<label class="form-label small fw-semibold">Registered email</label><input type="email" name="email" class="form-control mb-3" required>
<label id="verifyLabel" class="form-label small fw-semibold">Matric number</label><input type="text" name="verification_value" id="verifyValue" class="form-control mb-2" required><div id="verifyHint" class="hint mb-3">Enter the matric number stored in your student profile.</div>
<button class="btnx">Verify Account</button></form>
<?php else: ?><form method="post"><?= csrfField() ?><input type="hidden" name="action" value="reset_password"><label class="form-label small fw-semibold">New password</label><input type="password" name="new_password" minlength="10" class="form-control mb-2" required><div class="hint mb-3">At least 10 characters with uppercase, lowercase and a number.</div><label class="form-label small fw-semibold">Confirm new password</label><input type="password" name="confirm_password" minlength="10" class="form-control mb-3" required><button class="btnx">Save New Password</button></form><?php endif; ?>
<a href="login.php" class="d-block text-center small mt-3" style="color:#F58220;font-weight:700;text-decoration:none">Back to sign in</a></div>
<script>const role=document.getElementById('resetRole');if(role){const update=()=>{const student=role.value==='student';document.getElementById('verifyLabel').textContent=student?'Matric number':'Registered phone number';document.getElementById('verifyHint').textContent=student?'Enter the matric number stored in your student profile.':'Enter the phone number stored in your account.';document.getElementById('verifyValue').placeholder=student?'Example: 2023100001A':'Example: 012-3456789';};role.addEventListener('change',update);update();}</script></body></html>
