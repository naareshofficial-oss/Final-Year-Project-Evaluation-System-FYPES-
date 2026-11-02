<?php
$saved = ($_GET['saved'] ?? '') === '1';
$settingsRows = safeRun($pdo, "SELECT setting_key, setting_value FROM system_settings WHERE setting_key IN ('semester_name','semester_start_date','semester_end_date')")->fetchAll();
$settings = array_column($settingsRows, 'setting_value', 'setting_key');
require __DIR__ . '/../../shared/profile.php';
?>
<?php if ($saved): ?><div class="alert alert-success small mt-3">Semester dates updated successfully.</div><?php endif; ?>
<div class="card-fy mt-3">
  <div class="section-title">Semester Dates</div>
  <p class="small text-muted mb-3">These dates display on the public login page for every user.</p>
  <form method="POST">
    <?= csrfField() ?>
    <input type="hidden" name="action" value="save_semester_dates">
    <div class="row g-3">
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Semester Name</label>
        <input class="form-control-fy w-100" name="semester_name" value="<?= e($settings['semester_name'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">Start Date</label>
        <input type="date" class="form-control-fy w-100" name="semester_start_date" value="<?= e($settings['semester_start_date'] ?? '') ?>" required>
      </div>
      <div class="col-md-4">
        <label class="form-label small fw-semibold">End Date</label>
        <input type="date" class="form-control-fy w-100" name="semester_end_date" value="<?= e($settings['semester_end_date'] ?? '') ?>" required>
      </div>
    </div>
    <button class="btn-fy-orange mt-4">Save Semester Dates</button>
  </form>
</div>
