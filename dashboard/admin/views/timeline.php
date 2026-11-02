<?php
$timelines = safeRun($pdo, "SELECT * FROM timelines WHERE semester_id=? ORDER BY programme_type, start_date",[$currentSemesterId])->fetchAll();
$grouped = [];
foreach ($timelines as $t) $grouped[$t['programme_type']][] = $t;
$typeIcons = ['submission'=>'upload','evaluation'=>'clipboard-check','result_release'=>'award','presentation'=>'person-chalkboard'];
$today = date('Y-m-d');
?>
<div class="row g-3">
<?php foreach ($grouped as $pt => $events): ?>
  <div class="col-lg-6">
    <div class="card-fy mb-3">
      <div class="section-title"><?= programmeTypeLabel($pt) ?></div>
      <div class="gtimeline-events">
      <?php foreach ($events as $ev):
        $pastDue = $today > $ev['end_date'];
        $active = $today >= $ev['start_date'] && $today <= $ev['end_date'];
        $nodeClass = $active ? 'active-now' : ($pastDue ? 'past-due' : '');
        $icon = $pastDue ? 'triangle-exclamation' : ($typeIcons[$ev['event_type']] ?? 'calendar');
      ?>
        <div class="gtimeline-event">
          <div class="gtimeline-node <?= $nodeClass ?>"><i class="fa-solid fa-<?= $icon ?>"></i></div>
          <div class="flex-grow-1">
            <div class="d-flex justify-content-between">
              <span class="fw-semibold small"><?= e($ev['event_name']) ?></span>
              <?php if ($active): ?><span class="badge-fy badge-warning">Active</span>
              <?php elseif ($pastDue): ?><span class="badge-fy badge-danger">Past Due</span>
              <?php else: ?><span class="badge-fy badge-secondary">Upcoming</span><?php endif; ?>
            </div>
            <div class="text-muted small"><?= date('M j', strtotime($ev['start_date'])) ?> — <?= date('M j, Y', strtotime($ev['end_date'])) ?></div>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>
