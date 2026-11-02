<?php
$timelines = safeRun($pdo, "SELECT * FROM timelines WHERE programme_type=? AND (semester_id IS NULL OR semester_id=?) ORDER BY start_date", [$pt,$currentSemesterId])->fetchAll();
$today = date('Y-m-d');

// Overall progress percentage across the full span (first start -> last end)
$overallPct = 0;
if (!empty($timelines)) {
    $firstStart = strtotime($timelines[0]['start_date']);
    $lastEnd = strtotime($timelines[count($timelines)-1]['end_date']);
    $now = time();
    $span = max(1, $lastEnd - $firstStart);
    $elapsed = min(max(0, $now - $firstStart), $span);
    $overallPct = round($elapsed / $span * 100);
}
$typeIcons = ['submission'=>'upload','evaluation'=>'clipboard-check','result_release'=>'award','presentation'=>'person-chalkboard'];
?>
<div class="card-fy mb-3">
  <div class="section-title"><?= programmeTypeLabel($pt) ?> — Semester Progress</div>
  <div class="gtimeline">
    <div class="gtimeline-track"><div class="gtimeline-fill" style="width:<?= $overallPct ?>%"></div></div>
    <?php if (!empty($timelines)): ?>
    <div class="gtimeline-dates">
      <span><?= date('M j', strtotime($timelines[0]['start_date'])) ?></span>
      <span><?= $overallPct ?>% through semester</span>
      <span><?= date('M j', strtotime($timelines[count($timelines)-1]['end_date'])) ?></span>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="card-fy">
  <div class="section-title">Milestones</div>
  <div class="gtimeline-events">
  <?php foreach ($timelines as $t):
    $past = $today > $t['end_date'];
    $active = $today >= $t['start_date'] && $today <= $t['end_date'];
    $nodeClass = $past ? 'past-due' : ($active ? 'active-now' : '');
    $icon = $past ? 'clock' : ($typeIcons[$t['event_type']] ?? 'calendar');
  ?>
    <div class="gtimeline-event">
      <div class="gtimeline-node <?= $nodeClass ?>"><i class="fa-solid fa-<?= $icon ?>"></i></div>
      <div class="flex-grow-1">
        <div class="d-flex justify-content-between">
          <span class="fw-semibold small"><?= e($t['event_name']) ?></span>
          <?php if ($active): ?><span class="badge-fy badge-warning">Active Now</span>
          <?php elseif ($past): ?><span class="badge-fy badge-danger">Past Due</span>
          <?php else: ?><span class="badge-fy badge-secondary">Upcoming</span><?php endif; ?>
        </div>
        <div class="small text-muted"><?= date('M j', strtotime($t['start_date'])) ?> — <?= date('M j, Y', strtotime($t['end_date'])) ?></div>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
  <?php if (empty($timelines)): ?><p class="text-muted small mb-0 py-3">No timeline published yet for your programme.</p><?php endif; ?>
</div>
