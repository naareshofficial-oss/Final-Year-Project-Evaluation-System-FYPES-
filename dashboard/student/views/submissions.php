<?php
$error=$_GET['error']??null;$subByCategory=[];
foreach($mySubmissions as $s){if(!isset($subByCategory[$s['category_id']])||$s['version']>$subByCategory[$s['category_id']]['version'])$subByCategory[$s['category_id']]=$s;}
?>
<?php if($error): ?><div class="alert alert-danger small">Submission failed: <?= e(str_replace('_',' ',$error)) ?>.</div><?php endif; ?>
<div class="row g-3">
<?php foreach($categories as $cat): $latest=$subByCategory[$cat['id']]??null;$closed=$cat['deadline']&&date('Y-m-d')>$cat['deadline'];$reviews=$latest?safeRun($pdo,"SELECT reviewer_role,status,remarks FROM submission_reviews WHERE submission_id=? AND reviewer_role='supervisor'",[$latest['id']])->fetchAll():[]; ?>
<div class="col-md-6"><div class="card-fy h-100"><div class="d-flex justify-content-between align-items-start mb-2"><h6 class="fw-bold mb-0"><?= e($cat['name']) ?></h6><?php if($closed): ?><span class="badge-fy badge-danger">Closed</span><?php elseif($latest): ?><span class="badge-fy badge-<?= statusBadgeColor($latest['status']) ?>"><?= e(submissionStatusLabel($latest['status'])) ?></span><?php endif; ?></div><p class="small text-muted"><?= e($cat['description']) ?></p>
<?php if($cat['deadline']): ?><p class="small text-muted"><i class="fa-solid fa-calendar me-1"></i>Deadline: <?= date('M j, Y',strtotime($cat['deadline'])) ?></p><?php endif; ?>
<?php if($cat['info_file_path']): ?><a target="_blank" class="small d-inline-block mb-2" style="color:var(--unisel-orange)" href="<?= secureFileUrl((int)$cat['id'],'category_info') ?>"><i class="fa-solid fa-file-arrow-down me-1"></i>Guidance file</a><?php endif; ?>
<?php if($latest): ?><div class="d-flex justify-content-between align-items-center p-2 mb-2 rounded-3" style="background:var(--bg-soft)"><div class="small"><?= e($latest['file_name']) ?></div><div class="d-flex gap-2"><?php if($latest['file_path']): ?><a target="_blank" class="btn-fy-outline btn-sm" href="<?= secureFileUrl((int)$latest['id'],'submission') ?>"><i class="fa-solid fa-eye"></i></a><?php endif; ?><?php if($latest['google_doc_link']): ?><a target="_blank" rel="noopener" class="btn-fy-outline btn-sm" href="<?= e($latest['google_doc_link']) ?>"><i class="fa-brands fa-google-drive"></i></a><?php endif; ?></div></div><?php endif; ?>
<?php foreach($reviews as $r): ?><div class="small mb-1"><strong><?= roleLabel($r['reviewer_role']) ?>:</strong> <span class="badge-fy badge-<?= statusBadgeColor($r['status']) ?>"><?= e(submissionStatusLabel($r['status'])) ?></span> <?= e($r['remarks']?:'') ?></div><?php endforeach; ?>
<?php if(!$closed): ?><form method="POST" enctype="multipart/form-data" class="mt-3"><?= csrfField() ?><input type="hidden" name="action" value="upload_submission"><input type="hidden" name="category_id" value="<?= $cat['id'] ?>"><div class="d-flex gap-2 mb-2"><input type="file" name="file" class="form-control-fy flex-grow-1"><button class="btn-fy-orange"><i class="fa-solid fa-upload"></i></button></div><input type="url" name="google_doc_link" class="form-control-fy w-100" placeholder="Or a Google Docs/Drive link"></form><?php endif; ?>
</div></div>
<?php endforeach; ?>
<?php if(empty($categories)): ?><div class="col-12"><div class="card-fy text-center py-5 text-muted">No submission categories are open for <?= programmeTypeLabel($pt) ?>.</div></div><?php endif; ?>
</div>
