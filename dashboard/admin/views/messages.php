<?php
$messages = safeRun($pdo, "SELECT * FROM admin_messages ORDER BY created_at DESC")->fetchAll();
?>
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card-fy">
      <div class="section-title">Send Announcement</div>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="send_message">
        <label class="form-label small fw-semibold">Audience</label>
        <select name="audience" class="form-control-fy w-100 mb-3">
          <option value="all">Everyone</option>
          <option value="students">Students Only</option>
          <option value="lecturers">Lecturers Only</option>
          <option value="coordinators">Coordinators Only</option>
        </select>
        <label class="form-label small fw-semibold">Subject</label>
        <input class="form-control-fy w-100 mb-3" name="subject" required>
        <label class="form-label small fw-semibold">Message</label>
        <textarea class="form-control-fy w-100 mb-3" name="body" rows="4" required></textarea>
        <button class="btn-fy-orange w-100">Send to Notifications</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card-fy">
      <div class="section-title">Sent Announcements</div>
      <?php foreach ($messages as $m): ?>
        <div class="py-3 border-bottom">
          <div class="d-flex justify-content-between">
            <span class="fw-semibold small"><?= e($m['subject']) ?></span>
            <span class="badge-fy badge-info"><?= ucfirst($m['audience']) ?></span>
          </div>
          <div class="small text-muted mt-1"><?= e($m['body']) ?></div>
          <div class="small text-muted mt-1"><?= timeAgo($m['created_at']) ?></div>
        </div>
      <?php endforeach; ?>
      <?php if (empty($messages)): ?><p class="text-muted small mb-0 py-3">No announcements sent yet.</p><?php endif; ?>
    </div>
  </div>
</div>
