<?php
$faqs = safeRun($pdo, "SELECT * FROM chatbot_faqs ORDER BY category, id DESC")->fetchAll();
?>
<?php if(($_GET['faqmsg']??'')==='added'): ?><div class="alert alert-success small">FAQ entry added. It is now available to all users and on the homepage.</div><?php elseif(($_GET['faqmsg']??'')==='deleted'): ?><div class="alert alert-success small">FAQ entry deleted.</div><?php endif; ?>
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card-fy">
      <div class="section-title">Add FAQ Entry</div>
      <p class="small text-muted">Entries created here are available to every user and are displayed in the homepage FAQ section.</p>
      <form method="POST">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="add_faq">
        <label class="form-label small fw-semibold">Category</label>
        <input class="form-control-fy w-100 mb-3" name="category" placeholder="e.g. Submission" required maxlength="100">
        <label class="form-label small fw-semibold">Question</label>
        <input class="form-control-fy w-100 mb-3" name="question" required maxlength="255">
        <label class="form-label small fw-semibold">Answer</label>
        <textarea class="form-control-fy w-100 mb-3" name="answer" rows="4" required></textarea>
        <label class="form-label small fw-semibold">Keywords (comma-separated)</label>
        <input class="form-control-fy w-100 mb-3" name="keywords" placeholder="submit, upload, deadline" maxlength="255">
        <button class="btn-fy-orange w-100">Add FAQ Entry</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card-fy">
      <div class="section-title">Knowledge Base</div>
      <div style="max-height:620px;overflow-y:auto;">
      <?php foreach ($faqs as $f): ?>
        <div class="py-3 border-bottom">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div class="fw-semibold small"><?= e($f['question']) ?></div>
            <form method="POST" onsubmit="return confirm('Delete this FAQ question?')">
              <?= csrfField() ?><input type="hidden" name="action" value="delete_faq"><input type="hidden" name="faq_id" value="<?= (int)$f['id'] ?>">
              <button class="btn btn-sm text-danger border-0" title="Delete FAQ"><i class="fa-solid fa-trash"></i></button>
            </form>
          </div>
          <div class="small text-muted mt-1"><?= nl2br(e($f['answer'])) ?></div>
          <div class="mt-2"><span class="badge-fy badge-secondary"><?= e($f['category']) ?></span><?php if($f['audience']!=='all'): ?> <span class="badge-fy badge-info"><?= e(ucfirst($f['audience'])) ?></span><?php endif; ?></div>
        </div>
      <?php endforeach; ?>
      <?php if(!$faqs): ?><p class="text-muted small mb-0 py-4">No FAQ entries have been created.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>
