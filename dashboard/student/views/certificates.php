<?php
$myCerts = safeRun($pdo, "SELECT * FROM certificates WHERE user_id=? ORDER BY issued_at DESC", [$user['id']])->fetchAll();
?>
<div class="card-fy">
  <div class="section-title">My Certificates</div>
  <?php if (empty($myCerts)): ?>
    <div class="text-center py-5 text-muted">
      <i class="fa-solid fa-certificate fa-2x mb-3" style="color:#EEECE7;"></i>
      <p class="mb-0">No certificates yet. Your completion certificate will appear here once your FYP evaluation is finalized.</p>
    </div>
  <?php else: ?>
    <div class="row g-3">
    <?php foreach ($myCerts as $c): ?>
      <div class="col-md-6">
        <div class="card-fy" style="border:1.5px dashed var(--unisel-orange);">
          <div class="d-flex align-items-center gap-3 mb-2">
            <i class="fa-solid fa-certificate fa-lg" style="color:var(--unisel-orange);"></i>
            <div>
              <div class="fw-bold small"><?= e($c['title']) ?></div>
              <div class="text-muted small"><code><?= e($c['cert_code']) ?></code><?= $c['grade'] ? ' · Grade: '.e($c['grade']) : '' ?></div>
            </div>
          </div>
          <p class="small text-muted mb-2">Issued <?= date('F j, Y', strtotime($c['issued_at'])) ?></p>
          <a href="<?= BASE_URL ?>/dashboard/certificate_download.php?type=certificate&id=<?= $c['id'] ?>" target="_blank" class="btn-fy-orange w-100 btn-sm"><i class="fa-solid fa-download me-1"></i> View PDF</a>
        </div>
      </div>
    <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
