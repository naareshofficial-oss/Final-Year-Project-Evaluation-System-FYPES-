<?php
$requests = safeRun($pdo, "SELECT sr.*, s.matric_number, s.programme_type, s.project_title, u.full_name, u.email
    FROM supervisor_requests sr JOIN students s ON s.id=sr.student_id JOIN users u ON u.id=s.user_id
    WHERE sr.supervisor_id=? AND sr.semester_id=? AND s.semester_id=? AND sr.status='pending' ORDER BY sr.created_at DESC", [$lecturerId,$currentSemesterId,$currentSemesterId])->fetchAll();
$history = safeRun($pdo, "SELECT sr.*, s.matric_number, u.full_name FROM supervisor_requests sr
    JOIN students s ON s.id=sr.student_id JOIN users u ON u.id=s.user_id
    WHERE sr.supervisor_id=? AND sr.semester_id=? AND s.semester_id=? AND sr.status != 'pending' ORDER BY sr.responded_at DESC LIMIT 10", [$lecturerId,$currentSemesterId,$currentSemesterId])->fetchAll();
?>
<div class="card-fy mb-3">
  <div class="section-title">Pending Supervision Requests (<?= count($requests) ?>)</div>
  <?php foreach ($requests as $r): ?>
    <div class="py-3 border-bottom">
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
        <div class="d-flex align-items-center gap-3">
          <div class="user-avatar" style="background:var(--unisel-orange-soft);color:var(--unisel-orange);width:44px;height:44px;"><?= strtoupper(substr($r['full_name'],0,1)) ?></div>
          <div>
            <div class="fw-semibold small"><?= e($r['full_name']) ?> <span class="text-muted">(<?= e($r['matric_number']) ?>)</span></div>
            <div class="small text-muted"><?= e($r['project_title']) ?></div>
            <span class="badge-fy badge-info mt-1"><?= programmeTypeLabel($r['programme_type']) ?></span>
          </div>
        </div>
      </div>
      <form method="POST" class="mt-3">
        <?= csrfField() ?><input type="hidden" name="action" value="respond_request"><input type="hidden" name="request_id" value="<?= $r['id'] ?>">
        <textarea name="response_note" class="form-control-fy w-100 mb-2" rows="2" placeholder="Optional note (e.g. reason for decision)..."></textarea>
        <div class="d-flex gap-2">
          <button type="submit" name="decision" value="accepted" class="btn-fy-orange btn-sm">Accept</button>
          <button type="submit" name="decision" value="rejected" class="btn-fy-outline btn-sm">Decline</button>
        </div>
      </form>
    </div>
  <?php endforeach; ?>
  <?php if (empty($requests)): ?><p class="text-muted small mb-0 py-3">No pending requests.</p><?php endif; ?>
</div>

<div class="card-fy">
  <div class="section-title">Recent Decisions</div>
  <?php foreach ($history as $h): ?>
    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
      <div><span class="small fw-semibold"><?= e($h['full_name']) ?></span> <span class="text-muted small">(<?= e($h['matric_number']) ?>)</span>
        <?php if ($h['response_note']): ?><div class="small text-muted">Note: <?= e($h['response_note']) ?></div><?php endif; ?>
      </div>
      <span class="badge-fy badge-<?= statusBadgeColor($h['status']) ?>"><?= ucfirst($h['status']) ?></span>
    </div>
  <?php endforeach; ?>
  <?php if (empty($history)): ?><p class="text-muted small mb-0 py-3">No decisions yet.</p><?php endif; ?>
</div>
