<?php
$myPts = array_unique(array_column(array_filter($roles, fn($r)=>$r['role_type']==='coordinator'), 'programme_type'));
$placeholders = implode(',', array_fill(0,count($myPts),'?'));
$timelines = count($myPts) ? safeRun($pdo, "SELECT * FROM timelines WHERE semester_id=? AND programme_type IN ($placeholders) ORDER BY start_date", array_merge([$currentSemesterId], $myPts))->fetchAll() : [];
?>
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card-fy">
      <div class="section-title">Add Timeline Event</div>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_timeline">
        <label class="form-label small fw-semibold">Programme</label>
        <select name="programme_type" class="form-control-fy w-100 mb-3">
          <?php foreach ($myPts as $pt): ?><option value="<?= $pt ?>"><?= programmeTypeLabel($pt) ?></option><?php endforeach; ?>
        </select>
        <label class="form-label small fw-semibold">Event Name</label>
        <input class="form-control-fy w-100 mb-3" name="event_name" required>
        <label class="form-label small fw-semibold">Event Type</label>
        <select name="event_type" class="form-control-fy w-100 mb-3">
          <option value="submission">Submission</option>
          <option value="evaluation">Evaluation</option>
          <option value="presentation">Presentation</option>
          <option value="result_release">Result Release</option>
        </select>
        <div class="row g-2 mb-3">
          <div class="col-6"><label class="form-label small fw-semibold">Start Date</label><input type="date" class="form-control-fy w-100" name="start_date" required></div>
          <div class="col-6"><label class="form-label small fw-semibold">End Date</label><input type="date" class="form-control-fy w-100" name="end_date" required></div>
        </div>
        <button class="btn-fy-orange w-100">Add Event</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card-fy">
      <div class="section-title">Your Programme Timelines</div>
      <?php foreach ($timelines as $t): ?>
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
          <div>
            <div class="fw-semibold small"><?= e($t['event_name']) ?></div>
            <div class="small text-muted"><?= programmeTypeLabel($t['programme_type']) ?> · <?= date('M j', strtotime($t['start_date'])) ?> – <?= date('M j, Y', strtotime($t['end_date'])) ?></div>
          </div>
          <form method="POST" onsubmit="return confirm('Delete this event?')">
            <?= csrfField() ?><input type="hidden" name="action" value="delete_timeline"><input type="hidden" name="timeline_id" value="<?= $t['id'] ?>">
            <button class="btn btn-sm text-danger border-0"><i class="fa-solid fa-trash"></i></button>
          </form>
        </div>
      <?php endforeach; ?>
      <?php if (empty($timelines)): ?><p class="text-muted small mb-0 py-3">No timeline events yet.</p><?php endif; ?>
    </div>
  </div>
</div>
