<?php
/**
 * Shared profile view — included by admin/lecturer/student dashboards.
 * Expects $pdo, $user (currentUser()) to be in scope.
 */
$userId = $user['id'];
$msg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile' && verifyCSRFToken($_POST['csrf_token'] ?? null)) {
    $name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio'] ?? '');
    safeRun($pdo, "UPDATE users SET full_name=?, phone=?, bio=? WHERE id=?", [$name, $phone, $bio, $userId]);
    $_SESSION['name'] = $name;

    if ($user['role'] === 'lecturer' && !empty($_POST['office'])) {
        safeRun($pdo, "UPDATE lecturers SET office=?, research_interests=? WHERE user_id=?",
            [trim($_POST['office']), trim($_POST['research_interests'] ?? ''), $userId]);
    }
    if ($user['role'] === 'student') {
        safeRun($pdo, "UPDATE students SET age=?, gender=? WHERE user_id=?",
            [(int)($_POST['age'] ?? 0) ?: null, $_POST['gender'] ?? null, $userId]);
    }
    $msg = 'updated';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password' && verifyCSRFToken($_POST['csrf_token'] ?? null)) {
    $current = $_POST['current_password']; $new = $_POST['new_password']; $confirm = $_POST['confirm_new_password'];
    $row = safeRun($pdo, "SELECT password FROM users WHERE id=?", [$userId])->fetch();
    if (!password_verify($current, $row['password'])) {
        $msg = 'wrong_password';
    } elseif ($new !== $confirm || strlen($new) < 8) {
        $msg = 'password_mismatch';
    } else {
        safeRun($pdo, "UPDATE users SET password=? WHERE id=?", [password_hash($new, PASSWORD_BCRYPT), $userId]);
        $msg = 'password_updated';
    }
}

$fullUser = safeRun($pdo, "SELECT * FROM users WHERE id=?", [$userId])->fetch();
$lecturerRow = $user['role']==='lecturer' ? safeRun($pdo, "SELECT * FROM lecturers WHERE user_id=?", [$userId])->fetch() : null;
$studentRow = $user['role']==='student' ? safeRun($pdo, "SELECT * FROM students WHERE user_id=?", [$userId])->fetch() : null;
?>
<?php if ($msg === 'updated'): ?><div class="alert alert-success small">Profile updated successfully.</div><?php endif; ?>
<?php if ($msg === 'password_updated'): ?><div class="alert alert-success small">Password changed successfully.</div><?php endif; ?>
<?php if ($msg === 'wrong_password'): ?><div class="alert alert-danger small">Current password is incorrect.</div><?php endif; ?>
<?php if ($msg === 'password_mismatch'): ?><div class="alert alert-danger small">New passwords don't match or are too short (min 8 chars).</div><?php endif; ?>

<div class="row g-3">
  <div class="col-lg-7">
    <div class="card-fy mb-3">
      <div class="section-title">Profile Information</div>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="update_profile">
        <div class="d-flex align-items-center gap-3 mb-4">
          <div class="user-avatar" style="width:64px;height:64px;font-size:24px;background:var(--theme-soft);color:var(--theme);">
            <?= strtoupper(substr($fullUser['full_name'],0,1)) ?>
          </div>
          <div>
            <div class="fw-bold"><?= e($fullUser['full_name']) ?></div>
            <div class="text-muted small"><?= e($fullUser['email']) ?></div>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Full Name</label>
            <input class="form-control-fy w-100" name="full_name" value="<?= e($fullUser['full_name']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Phone Number</label>
            <input class="form-control-fy w-100" name="phone" value="<?= e($fullUser['phone']) ?>">
          </div>
          <?php if ($lecturerRow): ?>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Office</label>
            <input class="form-control-fy w-100" name="office" value="<?= e($lecturerRow['office']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Research Interests</label>
            <input class="form-control-fy w-100" name="research_interests" value="<?= e($lecturerRow['research_interests']) ?>">
          </div>
          <?php endif; ?>
          <?php if ($studentRow): ?>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Age</label>
            <input class="form-control-fy w-100" type="number" name="age" value="<?= e((string)$studentRow['age']) ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label small fw-semibold">Gender</label>
            <select class="form-control-fy w-100" name="gender">
              <option value="male" <?= $studentRow['gender']==='male'?'selected':'' ?>>Male</option>
              <option value="female" <?= $studentRow['gender']==='female'?'selected':'' ?>>Female</option>
              <option value="other" <?= $studentRow['gender']==='other'?'selected':'' ?>>Other</option>
            </select>
          </div>
          <?php endif; ?>
          <div class="col-12">
            <label class="form-label small fw-semibold">Biography</label>
            <textarea class="form-control-fy w-100" name="bio" rows="3"><?= e($fullUser['bio']) ?></textarea>
          </div>
        </div>
        <button class="btn-fy-orange mt-4">Save Changes</button>
      </form>
    </div>
  </div>
  <div class="col-lg-5">
    <div class="card-fy">
      <div class="section-title">Change Password</div>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="change_password">
        <label class="form-label small fw-semibold">Current Password</label>
        <input class="form-control-fy w-100 mb-3" type="password" name="current_password" required>
        <label class="form-label small fw-semibold">New Password</label>
        <input class="form-control-fy w-100 mb-3" type="password" name="new_password" minlength="8" required>
        <label class="form-label small fw-semibold">Confirm New Password</label>
        <input class="form-control-fy w-100 mb-3" type="password" name="confirm_new_password" minlength="8" required>
        <button class="btn-fy w-100">Update Password</button>
      </form>
    </div>
  </div>
</div>
