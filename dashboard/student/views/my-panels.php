<?php
$panelData=[];
foreach (['report_panel_id'=>'Report Panel','presentation_panel_id'=>'Presentation Panel'] as $column=>$label) {
    $panel=null;
    if (!empty($student[$column])) {
        $panel=safeRun($pdo,'SELECT l.*,u.full_name,u.email,u.phone FROM lecturers l JOIN users u ON u.id=l.user_id WHERE l.id=?',[$student[$column]])->fetch();
    }
    $panelData[]=['label'=>$label,'panel'=>$panel];
}
?>
<div class="row g-3">
<?php foreach($panelData as $item): $p=$item['panel']; ?>
<div class="col-lg-6"><div class="card-fy h-100">
 <div class="d-flex justify-content-between align-items-start mb-3"><div class="section-title mb-0"><?= e($item['label']) ?></div><span class="badge-fy badge-<?= $p?'success':'warning' ?>"><?= $p?'Assigned':'Pending' ?></span></div>
 <?php if($p): ?>
 <div class="d-flex align-items-center gap-3 mb-3"><div class="user-avatar" style="width:52px;height:52px;"><?= strtoupper(substr($p['full_name'],0,1)) ?></div><div><div class="fw-bold"><?= e(drName($p['full_name'])) ?></div><div class="small text-muted"><?= e($p['department']) ?></div></div></div>
 <div class="small mb-2"><i class="fa-regular fa-envelope me-2 text-muted"></i><?= e($p['email']) ?></div>
 <div class="small mb-2"><i class="fa-solid fa-phone me-2 text-muted"></i><?= e($p['phone']?:'Not provided') ?></div>
 <div class="small"><i class="fa-solid fa-location-dot me-2 text-muted"></i><?= e($p['office']?:'Not provided') ?></div>
 <div class="alert alert-light border small mt-3 mb-0"><div><i class="fa-regular fa-calendar me-1"></i>Shared panel date: <?= $student['presentation_date']?e(date('M j, Y',strtotime($student['presentation_date']))):'Not scheduled' ?></div><div class="mt-1"><i class="fa-solid fa-location-dot me-1"></i>Venue: <?= e($student['presentation_venue']?:'Not set') ?></div><div class="mt-1">Status: <span class="badge-fy badge-<?= $student['presentation_status']==='completed'?'success':($student['presentation_status']==='scheduled'?'warning':'secondary') ?>"><?= e(ucwords(str_replace('_',' ',$student['presentation_status']))) ?></span></div></div>
 <?php else: ?><p class="text-muted small mb-0">Your coordinator has not assigned this panel yet.</p><?php endif; ?>
</div></div>
<?php endforeach; ?>
</div>
