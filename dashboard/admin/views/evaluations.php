<?php
$statusFilter = $_GET['status'] ?? 'all';
$evals = safeRun($pdo, "SELECT ev.*, s.matric_number, su.full_name AS student_name, r.name AS rubric_name,
    lu.full_name AS evaluator_name
    FROM evaluations ev
    JOIN students s ON s.id=ev.student_id
    JOIN users su ON su.id=s.user_id
    JOIN rubrics r ON r.id=ev.rubric_id
    JOIN lecturers l ON l.id=ev.evaluator_id
    JOIN users lu ON lu.id=l.user_id
    WHERE ev.semester_id=?
    ORDER BY ev.created_at DESC",[$currentSemesterId])->fetchAll();

$byStatus = ['pending'=>[], 'reopened'=>[], 'completed'=>[]];
foreach ($evals as $e) { $key=array_key_exists($e['status'],$byStatus)?$e['status']:'pending'; $byStatus[$key][]=$e; }
$statuses = ['all'=>'All','pending'=>'Pending','reopened'=>'Reopened','completed'=>'Completed'];
if (!array_key_exists($statusFilter, $statuses)) $statusFilter = 'all';
$shown = $statusFilter === 'all' ? $evals : $byStatus[$statusFilter];
?>
<div class="row g-3 mb-4">
  <div class="col-6 col-lg-3"><div class="stat-card"><div class="stat-value"><?= count($evals) ?></div><div class="stat-label">Total Evaluations</div></div></div>
  <div class="col-6 col-lg-3"><a href="?view=evaluations&status=completed" class="text-decoration-none"><div class="stat-card"><div class="stat-value"><?= count($byStatus['completed']) ?></div><div class="stat-label">Completed</div></div></a></div>
  <div class="col-6 col-lg-3"><a href="?view=evaluations&status=pending" class="text-decoration-none"><div class="stat-card"><div class="stat-value"><?= count($byStatus['pending']) ?></div><div class="stat-label">Pending</div></div></a></div>
  <div class="col-6 col-lg-3"><a href="?view=evaluations&status=reopened" class="text-decoration-none"><div class="stat-card"><div class="stat-value"><?= count($byStatus['reopened']) ?></div><div class="stat-label">Reopened</div></div></a></div>
</div>

<div class="d-flex gap-2 mb-3 flex-wrap">
  <?php foreach ($statuses as $key => $label): ?>
    <a href="?view=evaluations&status=<?= $key ?>" class="filter-pill <?= $statusFilter===$key?'active':'' ?>"><?= $label ?> <?= $key!=='all' ? '('.count($byStatus[$key]).')' : '('.count($evals).')' ?></a>
  <?php endforeach; ?>
</div>

<div class="card-fy">
  <div class="section-title"><?= $statuses[$statusFilter] ?> Evaluations</div>
  <div style="max-height:560px;overflow-y:auto;">
  <table class="table-fy">
    <thead><tr><th>Student</th><th>Evaluator</th><th>Role</th><th>Rubric</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($shown as $ev): ?>
      <tr>
        <td><?= e($ev['student_name']) ?><div class="text-muted small"><?= e($ev['matric_number']) ?></div></td>
        <td class="small"><?= e(drName($ev['evaluator_name'])) ?></td>
        <td><span class="badge-fy badge-secondary"><?= roleLabel($ev['evaluator_role']) ?></span></td>
        <td class="small"><?= e($ev['rubric_name']) ?></td>
        <td><span class="badge-fy badge-<?= statusBadgeColor($ev['status']) ?>"><?= ucfirst($ev['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if (empty($shown)): ?><tr><td colspan="5" class="text-center text-muted py-4">No evaluations in this category.</td></tr><?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
