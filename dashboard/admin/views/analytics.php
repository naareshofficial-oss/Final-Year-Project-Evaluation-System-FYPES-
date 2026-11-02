<?php
$counts = [];
foreach (['diploma_fyp1','diploma_fyp2','degree_fyp1','degree_fyp2'] as $pt) {
    $counts[$pt] = (int) safeRun($pdo, "SELECT COUNT(*) c FROM students WHERE programme_type=? AND semester_id=?", [$pt,$currentSemesterId])->fetch()['c'];
}
?>
<div class="card-fy">
  <div class="section-title">Students by Programme Type</div>
  <canvas id="programmeChart" height="110"></canvas>
</div>
<script>
new Chart(document.getElementById('programmeChart'), {
  type: 'bar',
  data: {
    labels: ['FYP1 Diploma','FYP2 Diploma','FYP1 Degree','FYP2 Degree'],
    datasets: [{
      data: [<?= $counts['diploma_fyp1'] ?>, <?= $counts['diploma_fyp2'] ?>, <?= $counts['degree_fyp1'] ?>, <?= $counts['degree_fyp2'] ?>],
      backgroundColor: ['#F58220','#F8A055','#FBC28A','#FDDBB5'],
      borderRadius: 8,
    }]
  },
  options: { plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true, grid:{color:'#F0EEE9'}}, x:{grid:{display:false}}} }
});
</script>
