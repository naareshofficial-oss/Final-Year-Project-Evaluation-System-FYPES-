<?php
require_once __DIR__.'/../includes/config.php';
if(isLoggedIn())redirect(BASE_URL.'/dashboard/'.$_SESSION['role'].'/index.php');

$errors=[];$old=[];$systemName=getSetting($pdo,'system_name',APP_NAME)?:APP_NAME;
$options=programmeOptions();

if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!verifyCSRFToken($_POST['csrf_token']??null))$errors[]='Invalid session token. Please try again.';
 else{
  $matric=strtoupper(trim($_POST['matric_number']??''));$name=trim($_POST['full_name']??'');$email=strtolower(trim($_POST['email']??''));
  $password=$_POST['password']??'';$confirm=$_POST['confirm_password']??'';$phone=trim($_POST['phone']??'');
  $level=in_array($_POST['level']??'', ['diploma','degree'], true)?$_POST['level']:'';
  $programme=trim($_POST['programme']??'');$fypStage=in_array($_POST['fyp_stage']??'', ['fyp1','fyp2'], true)?$_POST['fyp_stage']:'';
  $old=compact('matric','name','email','phone','level','programme','fypStage');

  if(!$matric||!$name||!$email||!$phone||!$level||!$programme||!$fypStage)$errors[]='Please fill in all required fields.';
  if(!validateMatricNumber($matric))$errors[]='Matric number must contain 1 to 10 digits and may end with one optional letter. Maximum length is 11 characters.';
  if(!validateStudentEmail($email))$errors[]='Use an official matric@student.unisel.edu.my email address.';
  elseif(!studentEmailMatchesMatric($email,$matric))$errors[]='The student email must exactly match the matric number entered.';
  if(!programmeIsAllowedForLevel($programme,$level))$errors[]='The selected programme does not match the selected Diploma or Degree level.';
  if(strlen($password)<10||!preg_match('/[A-Z]/',$password)||!preg_match('/[a-z]/',$password)||!preg_match('/\d/',$password))$errors[]='Password must have at least 10 characters with uppercase, lowercase and a number.';
  if($password!==$confirm)$errors[]='Passwords do not match.';

  if(!$errors){
   $exists=safeRun($pdo,'SELECT 1 FROM users WHERE email=?',[$email])->fetch()||safeRun($pdo,'SELECT 1 FROM students WHERE matric_number=?',[$matric])->fetch();
   if($exists)$errors[]='An account with this email or matric number already exists.';
   else{
    try{$pdo->beginTransaction();
     safeRun($pdo,"INSERT INTO users (role,full_name,email,password,phone,status,email_verified,must_change_password) VALUES ('student',?,?,?,?, 'inactive',0,0)",[$name,$email,password_hash($password,PASSWORD_BCRYPT),$phone]);
     $uid=(int)$pdo->lastInsertId();$programmeType=$level.'_'.$fypStage;
     safeRun($pdo,"INSERT INTO students (user_id,matric_number,level,programme,faculty,programme_type,semester_id,academic_status,attempt_number) VALUES (?,?,?,?, 'Faculty of Computing, Visual Arts and Communication',?,?,'active',1)",[$uid,$matric,$level,$programme,$programmeType,currentSemesterId($pdo)]);
     $sid=(int)$pdo->lastInsertId();syncStudentStageHistory($pdo,$sid);$pdo->commit();redirect(BASE_URL.'/auth/login.php?registered=pending');
    }catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();$errors[]='Registration failed. The email or matric number may already exist.';}
   }
  }
 }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Create Student Account | <?= e($systemName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@600;700;800&display=swap" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<style>body{font-family:'Plus Jakarta Sans',sans-serif;background:#FAFAF8;min-height:100vh;padding:42px 16px;color:#1E1B2E}.wrap{max-width:650px;margin:auto;background:#fff;border-radius:24px;padding:42px;box-shadow:0 20px 50px rgba(20,20,20,.05);border:1px solid #F0EEE9}.form-control,.form-select{border-radius:12px;padding:11px 14px;border:1.5px solid #ECEBE7;font-size:14px}.form-control:focus,.form-select:focus{border-color:#F58220;box-shadow:0 0 0 4px rgba(245,130,32,.1)}.form-label{font-size:13px;font-weight:700}.btn-primary-fy{display:flex;align-items:center;justify-content:center;background:#F58220;color:#fff;border-radius:12px;padding:12px;font-weight:700;border:none;width:100%}.btn-primary-fy:hover{background:#DD7314}.hint{font-size:11.5px;color:#8A857C}.auth-logo{display:flex;width:max-content;margin:auto;align-items:center;gap:10px;color:#1E1B2E;text-decoration:none}.auth-logo-icon{width:40px;height:40px;border-radius:12px;display:inline-flex;align-items:center;justify-content:center;background:#F58220;color:#fff}.auth-logo-word{font-family:'Fraunces',serif;font-weight:800;font-size:25px}.auth-logo-word span{color:#F58220}@media(max-width:600px){body{padding:18px 12px}.wrap{padding:29px 18px}}</style></head>
<body><div class="wrap"><div class="text-center mb-4"><a href="../index.php" class="auth-logo"><span class="auth-logo-icon"><i class="fa-solid fa-graduation-cap"></i></span><span class="auth-logo-word"><span>FY</span>PES</span></a><p class="text-muted small mt-2 mb-0">Create your Student Account</p></div>
<div class="alert alert-light border small">Only students can self-register. The account remains inactive until an administrator approves it.</div>
<?php if($errors): ?><div class="alert alert-danger small"><ul class="mb-0 ps-3"><?php foreach($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
<form method="post" novalidate><?= csrfField() ?><div class="row g-3">
<div class="col-md-6"><label class="form-label">Matric Number *</label><input id="matric" maxlength="11" name="matric_number" class="form-control" value="<?= e($old['matric']??'') ?>" placeholder="Up to 10 digits + optional letter" required><div class="hint">Examples: 12345, 2023100001, 2023100001A.</div></div>
<div class="col-md-6"><label class="form-label">Full Name *</label><input name="full_name" class="form-control" value="<?= e($old['name']??'') ?>" required></div>
<div class="col-12"><label class="form-label">Student Email *</label><input id="studentEmail" type="email" name="email" class="form-control" value="<?= e($old['email']??'') ?>" placeholder="matric@student.unisel.edu.my" required><div class="hint">The email local part must match the matric number.</div></div>
<div class="col-md-6"><label class="form-label">Level *</label><select id="level" name="level" class="form-select" required><option value="">Select Diploma or Degree</option><option value="diploma" <?= ($old['level']??'')==='diploma'?'selected':'' ?>>Diploma</option><option value="degree" <?= ($old['level']??'')==='degree'?'selected':'' ?>>Degree</option></select></div>
<div class="col-md-6"><label class="form-label">Programme *</label><select id="programme" name="programme" class="form-select" data-selected="<?= e($old['programme']??'') ?>" required><option value="">Select level first</option></select></div>
<div class="col-md-6"><label class="form-label">Current FYP Stage *</label><select name="fyp_stage" class="form-select" required><option value="fyp1" <?= ($old['fypStage']??'fyp1')==='fyp1'?'selected':'' ?>>FYP 1</option><option value="fyp2" <?= ($old['fypStage']??'')==='fyp2'?'selected':'' ?>>FYP 2</option></select></div>
<div class="col-md-6"><label class="form-label">Phone Number *</label><input name="phone" class="form-control" value="<?= e($old['phone']??'') ?>" required></div>
<div class="col-md-6"><label class="form-label">Password *</label><input type="password" name="password" minlength="10" class="form-control" required><div class="hint">10+ characters with uppercase, lowercase and a number.</div></div>
<div class="col-md-6"><label class="form-label">Confirm Password *</label><input type="password" name="confirm_password" minlength="10" class="form-control" required></div>
</div><button class="btn-primary-fy mt-4">Create Account</button></form><p class="text-center small text-muted mt-3 mb-0">Already registered? <a href="login.php" style="color:#F58220;font-weight:700;text-decoration:none">Sign in</a></p></div>
<script>
const programmes=<?= json_encode($options,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?>,level=document.getElementById('level'),programme=document.getElementById('programme');
function refreshProgrammes(){const selected=programme.dataset.selected||'';programme.innerHTML='<option value="">Select Programme</option>';(programmes[level.value]||[]).forEach(p=>{const o=document.createElement('option');o.value=p;o.textContent=p;if(p===selected)o.selected=true;programme.appendChild(o);});programme.dataset.selected='';}
level.addEventListener('change',refreshProgrammes);refreshProgrammes();
document.getElementById('matric').addEventListener('input',function(){let v=this.value.toUpperCase().replace(/[^0-9A-Z]/g,'');const digits=(v.match(/^\d{0,10}/)||[''])[0];const rest=v.slice(digits.length).replace(/[^A-Z]/g,'').slice(0,1);this.value=(digits+rest).slice(0,11);if(this.value)document.getElementById('studentEmail').value=this.value.toLowerCase()+'@student.unisel.edu.my';});
</script></body></html>
