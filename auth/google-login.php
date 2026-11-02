<?php
require_once __DIR__ . '/../includes/config.php';

if (isLoggedIn()) redirect(BASE_URL.'/dashboard/'.$_SESSION['role'].'/index.php');
$googleConfig=googleOAuthConfig($pdo);
if (!googleOAuthConfigured($pdo)) redirect(BASE_URL.'/auth/login.php?error=google_not_configured');

$action=$_GET['action']??'callback';
if($action==='start'){
    $expectedRole=in_array($_GET['role']??'', ['student','lecturer'], true)?$_GET['role']:'student';
    $state=bin2hex(random_bytes(24));
    $_SESSION['google_oauth_state']=$state;
    $_SESSION['google_expected_role']=$expectedRole;
    $_SESSION['google_oauth_started_at']=time();
    $params=[
        'client_id'=>$googleConfig['client_id'],
        'redirect_uri'=>$googleConfig['redirect_uri'],
        'response_type'=>'code',
        'scope'=>'openid email profile',
        'state'=>$state,
        'prompt'=>'select_account',
        'access_type'=>'online',
        'include_granted_scopes'=>'true',
    ];
    redirect('https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query($params,'','&',PHP_QUERY_RFC3986));
}

if(isset($_GET['error'])) redirect(BASE_URL.'/auth/login.php?error=google_denied');
$state=$_GET['state']??'';
$expectedState=$_SESSION['google_oauth_state']??'';
$started=(int)($_SESSION['google_oauth_started_at']??0);
if($state===''||$expectedState===''||!hash_equals($expectedState,$state)||$started<time()-600){
    unset($_SESSION['google_oauth_state'],$_SESSION['google_expected_role'],$_SESSION['google_oauth_started_at']);
    redirect(BASE_URL.'/auth/login.php?error=google_failed');
}
$expectedRole=$_SESSION['google_expected_role']??'student';
unset($_SESSION['google_oauth_state'],$_SESSION['google_expected_role'],$_SESSION['google_oauth_started_at']);

$code=$_GET['code']??'';
if($code==='')redirect(BASE_URL.'/auth/login.php?error=google_failed');
$token=httpPostForm('https://oauth2.googleapis.com/token',[
    'code'=>$code,
    'client_id'=>$googleConfig['client_id'],
    'client_secret'=>$googleConfig['client_secret'],
    'redirect_uri'=>$googleConfig['redirect_uri'],
    'grant_type'=>'authorization_code',
]);
if(!$token||empty($token['access_token']))redirect(BASE_URL.'/auth/login.php?error=google_failed');

$profile=httpGetJson('https://openidconnect.googleapis.com/v1/userinfo',['Authorization: Bearer '.$token['access_token']]);
if(!$profile||empty($profile['email'])||empty($profile['email_verified']))redirect(BASE_URL.'/auth/login.php?error=google_failed');

$email=strtolower(trim($profile['email']));
$googleSub=(string)($profile['sub']??'');
if(str_ends_with($email,'@student.unisel.edu.my'))$domainRole='student';
elseif(str_ends_with($email,'@unisel.edu.my'))$domainRole='lecturer';
else redirect(BASE_URL.'/auth/login.php?error=google_domain');

if($domainRole!==$expectedRole)redirect(BASE_URL.'/auth/login.php?role='.urlencode($expectedRole).'&error=google_role');

if($domainRole==='student'){
    $user=safeRun($pdo,"SELECT u.*,s.id student_id FROM users u JOIN students s ON s.user_id=u.id WHERE LOWER(u.email)=? AND u.role='student'",[$email])->fetch();
    if(!$user||$user['status']!=='active')redirect(BASE_URL.'/auth/login.php?role=student&error=google_account');
    if(!empty($user['google_sub'])&&$googleSub!==''&&!hash_equals((string)$user['google_sub'],$googleSub))redirect(BASE_URL.'/auth/login.php?role=student&error=google_account');
    safeRun($pdo,'UPDATE users SET google_sub=COALESCE(google_sub,?),last_google_login_at=NOW(),email_verified=1,must_change_password=0 WHERE id=?',[$googleSub?:null,$user['id']]);
    $user['must_change_password']=0;
    loginUserSession($pdo,$user,null,(int)$user['student_id'],'google');
    redirect(BASE_URL.'/dashboard/student/index.php');
}

$user=safeRun($pdo,"SELECT u.*,l.id lecturer_id FROM users u JOIN lecturers l ON l.user_id=u.id WHERE LOWER(u.email)=? AND u.role='lecturer'",[$email])->fetch();
if(!$user||$user['status']!=='active')redirect(BASE_URL.'/auth/login.php?role=lecturer&error=google_account');
if(!empty($user['google_sub'])&&$googleSub!==''&&!hash_equals((string)$user['google_sub'],$googleSub))redirect(BASE_URL.'/auth/login.php?role=lecturer&error=google_account');
safeRun($pdo,'UPDATE users SET google_sub=COALESCE(google_sub,?),last_google_login_at=NOW(),email_verified=1,must_change_password=0 WHERE id=?',[$googleSub?:null,$user['id']]);
$user['must_change_password']=0;
loginUserSession($pdo,$user,(int)$user['lecturer_id'],null,'google');
if(empty(currentLecturerRoles($pdo)))redirect(BASE_URL.'/dashboard/pending-role.php');
redirect(BASE_URL.'/dashboard/lecturer/index.php');
