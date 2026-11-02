<?php
$myPts = array_unique(array_column(array_filter($roles, fn($r)=>$r['role_type']==='coordinator'), 'programme_type'));
$placeholders = implode(',', array_fill(0,count($myPts),'?'));
$cats = count($myPts) ? safeRun($pdo, "SELECT sc.*,(SELECT COUNT(*) FROM submissions sub WHERE sub.category_id=sc.id) submission_count FROM submission_categories sc WHERE sc.semester_id=? AND sc.programme_type IN ($placeholders) ORDER BY sc.programme_type,sc.sort_order", array_merge([$currentSemesterId], $myPts))->fetchAll() : [];
?>
<?php if(($_GET['reopened']??'')==='1'): ?><div class="alert alert-success small">Expired category reopened. Existing submissions were preserved and the new deadline applies to all eligible students.</div><?php endif; ?>
<?php if(($_GET['catmsg']??'')==='updated'): ?><div class="alert alert-success small">Submission category updated successfully.</div><?php elseif(($_GET['catmsg']??'')==='deleted'): ?><div class="alert alert-success small">Submission category deleted.</div><?php elseif(($_GET['catmsg']??'')==='used'): ?><div class="alert alert-warning small">This category cannot be deleted because student submissions already depend on it.</div><?php endif; ?>
<div class="row g-3">
  <div class="col-lg-5">
    <div class="card-fy">
      <div class="section-title">Add Submission Category</div>
      <form method="POST" enctype="multipart/form-data">
        <?= csrfField() ?><input type="hidden" name="action" value="add_category">
        <label class="form-label small fw-semibold">Programme</label>
        <select name="programme_type" class="form-control-fy w-100 mb-3"><?php foreach ($myPts as $pt): ?><option value="<?= e($pt) ?>"><?= e(programmeTypeLabel($pt)) ?></option><?php endforeach; ?></select>
        <label class="form-label small fw-semibold">Category Name</label><input class="form-control-fy w-100 mb-3" name="name" required maxlength="150">
        <label class="form-label small fw-semibold">Description</label><textarea class="form-control-fy w-100 mb-3" name="description" rows="3"></textarea>
        <label class="form-label small fw-semibold">Deadline</label><input type="date" class="form-control-fy w-100 mb-3" name="deadline">
        <label class="form-label small fw-semibold">Chapter Info File (PDF/DOC, optional)</label><input type="file" class="form-control-fy w-100 mb-3" name="info_file" accept=".pdf,.doc,.docx">
        <button class="btn-fy-orange w-100">Add Category</button>
      </form>
    </div>
  </div>
  <div class="col-lg-7">
    <div class="card-fy">
      <div class="section-title">Existing Categories</div>
      <?php foreach ($cats as $c): ?>
        <div class="py-3 border-bottom">
          <div class="d-flex justify-content-between gap-2 align-items-start">
            <div><div class="fw-semibold small"><?= e($c['name']) ?></div><div class="small text-muted"><?= e(programmeTypeLabel($c['programme_type'])) ?><?php if($c['deadline']): ?> · Deadline: <?= e(date('M j, Y',strtotime($c['deadline']))) ?><?php endif; ?> · <?= (int)$c['submission_count'] ?> submission(s)</div></div>
          </div>
          <?php if($c['description']): ?><div class="small text-muted mt-1"><?= e($c['description']) ?></div><?php endif; ?>
          <div class="d-flex gap-2 align-items-center flex-wrap mt-2">
            <?php if ($c['info_file_path']): ?><a href="<?= e(secureFileUrl((int)$c['id'],'category_info')) ?>" target="_blank" class="btn-fy-outline btn-sm"><i class="fa-solid fa-file-pdf me-1"></i>View info</a><?php endif; ?>
            <button class="btn-fy-outline btn-sm" data-bs-toggle="modal" data-bs-target="#editCat<?= (int)$c['id'] ?>"><i class="fa-solid fa-pen me-1"></i>Edit</button>
            <form method="post" class="d-inline" onsubmit="return confirm('Delete this category? This is allowed only when it has no student submissions.')"><?= csrfField() ?><input type="hidden" name="action" value="delete_category"><input type="hidden" name="category_id" value="<?= (int)$c['id'] ?>"><button class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash me-1"></i>Delete</button></form>
            <?php if($c['deadline']&&$c['deadline']<date('Y-m-d')): ?><button class="btn-fy-orange btn-sm" data-bs-toggle="modal" data-bs-target="#reopenCat<?= (int)$c['id'] ?>">Reopen with new deadline</button><?php endif; ?>
          </div>
        </div>
        <div class="modal fade" id="editCat<?= (int)$c['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="post" enctype="multipart/form-data"><?= csrfField() ?><input type="hidden" name="action" value="edit_category"><input type="hidden" name="category_id" value="<?= (int)$c['id'] ?>"><div class="modal-header"><h6 class="modal-title">Edit Submission Category</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label small fw-semibold">Category Name</label><input class="form-control-fy w-100 mb-3" name="name" value="<?= e($c['name']) ?>" required maxlength="150"><label class="form-label small fw-semibold">Description</label><textarea class="form-control-fy w-100 mb-3" name="description" rows="3"><?= e($c['description']) ?></textarea><label class="form-label small fw-semibold">Deadline</label><input type="date" class="form-control-fy w-100 mb-3" name="deadline" value="<?= e($c['deadline']) ?>"><label class="form-label small fw-semibold">Replace Info File (optional)</label><input type="file" class="form-control-fy w-100" name="info_file" accept=".pdf,.doc,.docx"></div><div class="modal-footer"><button class="btn-fy-orange w-100">Save Changes</button></div></form></div></div></div>
        <?php if($c['deadline']&&$c['deadline']<date('Y-m-d')): ?><div class="modal fade" id="reopenCat<?= (int)$c['id'] ?>" tabindex="-1"><div class="modal-dialog"><div class="modal-content"><form method="post"><?= csrfField() ?><input type="hidden" name="action" value="reopen_category"><input type="hidden" name="category_id" value="<?= (int)$c['id'] ?>"><div class="modal-header"><h6 class="modal-title">Reopen <?= e($c['name']) ?></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><label class="form-label small fw-semibold">New future deadline</label><input type="date" min="<?= e(date('Y-m-d',strtotime('+1 day'))) ?>" name="new_deadline" class="form-control-fy w-100" required><p class="small text-muted mt-2 mb-0">All existing submissions remain unchanged. The category reopens for all students in this programme.</p></div><div class="modal-footer"><button class="btn-fy-orange w-100">Reopen Category</button></div></form></div></div></div><?php endif; ?>
      <?php endforeach; ?>
      <?php if (empty($cats)): ?><p class="text-muted small mb-0 py-3">No categories yet.</p><?php endif; ?>
    </div>
  </div>
</div>
