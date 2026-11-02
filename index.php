<?php require_once __DIR__ . '/includes/config.php';
if (isLoggedIn()) {
    redirect(BASE_URL . '/dashboard/' . $_SESSION['role'] . '/index.php');
}
$currentSemesterId = currentSemesterId($pdo);
$systemName = getSetting($pdo, 'system_name', APP_NAME) ?: APP_NAME;
$institutionName = getSetting($pdo, 'institution_name', APP_INSTITUTION) ?: APP_INSTITUTION;
$homepageFaqs = safeRun($pdo, "SELECT id,category,question,answer FROM chatbot_faqs WHERE audience='all' ORDER BY id DESC")->fetchAll();
$stats = [
    'students' => safeRun($pdo, "SELECT COUNT(*) c FROM students WHERE semester_id<=>?", [$currentSemesterId])->fetch()['c'],
    'lecturers' => safeRun($pdo, "SELECT COUNT(*) c FROM lecturers l JOIN users u ON u.id=l.user_id WHERE u.status='active'")->fetch()['c'],
    'submissions' => safeRun($pdo, "SELECT COUNT(*) c FROM submissions WHERE semester_id<=>?", [$currentSemesterId])->fetch()['c'],
    'completed_eval' => safeRun($pdo, "SELECT COUNT(*) c FROM evaluations WHERE semester_id<=>? AND status='completed'", [$currentSemesterId])->fetch()['c'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($systemName) ?> — <?= e(APP_FULL_NAME) ?> | <?= e($institutionName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@600;700;800&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link rel="stylesheet" href="assets/css/style.css">
<link rel="stylesheet" href="assets/css/landing.css">
</head>
<body class="landing-body">

<nav class="landing-nav" id="landingNav">
  <div class="nav-inner">
    <a href="#" class="site-logo" aria-label="FYPES home" title="<?= e($systemName) ?>">
      <span class="site-logo-icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
      <span class="site-logo-word"><span>FY</span>PES</span>
    </a>
    <button class="nav-burger" id="navBurger" type="button" aria-label="Open navigation" aria-controls="landingMenu" aria-expanded="false">
      <i class="fa-solid fa-bars"></i>
    </button>
    <div class="nav-menu" id="landingMenu">
      <div class="nav-links">
        <a href="#about">About</a>
        <a href="#features">Features</a>
        <a href="#workflow">Workflow</a>
        <a href="#roles">Roles</a>
        <a href="#faq">FAQ</a>
        <a href="#contact">Contact</a>
      </div>
      <div class="nav-actions">
        <a href="auth/login.php" class="btn-nav-outline">Login</a>
        <a href="auth/register.php" class="btn-nav-solid">Create Account</a>
      </div>
    </div>
  </div>
</nav>

<!-- HERO -->
<header class="hero">
  <div class="hero-video-wrap">
    <video autoplay muted loop playsinline class="hero-video" poster="">
      <source src="assets/videos/homepage_merged.mp4" type="video/mp4">
    </video>
    <div class="hero-overlay"></div>
  </div>
  <div class="hero-content">
    <span class="hero-badge"><i class="fa-solid fa-sparkles me-1"></i> Structured Academic Workflow Platform</span>
    <h1 class="hero-title">Final Year Project<br>Evaluation, <span style="color:#F58220;">Reimagined.</span></h1>
    <p class="hero-sub"><?= e($systemName) ?> streamlines every stage of the FYP journey at <?= e($institutionName) ?> — from supervision and submissions to independent panel evaluation, results, and student completion certificates.</p>
    <div class="hero-cta">
      <a href="auth/register.php" class="btn-hero-primary">Create Account <i class="fa-solid fa-arrow-right ms-2"></i></a>
      <a href="auth/login.php" class="btn-hero-ghost">Login</a>
      <a href="#about" class="btn-hero-text">Learn More <i class="fa-solid fa-chevron-down ms-1"></i></a>
    </div>
  </div>
</header>

<!-- STATS STRIP -->
<section class="stats-strip">
  <div class="stats-inner">
    <div class="stat-item"><div class="stat-num"><?= (int)$stats['students'] ?>+</div><div class="stat-lbl">Active Students</div></div>
    <div class="stat-item"><div class="stat-num"><?= (int)$stats['lecturers'] ?>+</div><div class="stat-lbl">Faculty Members</div></div>
    <div class="stat-item"><div class="stat-num"><?= (int)$stats['submissions'] ?>+</div><div class="stat-lbl">Documents Submitted</div></div>
    <div class="stat-item"><div class="stat-num"><?= (int)$stats['completed_eval'] ?>+</div><div class="stat-lbl">Evaluations Completed</div></div>
  </div>
</section>

<!-- ABOUT -->
<section class="section-pad" id="about">
  <div class="container-fy">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="eyebrow">About The System</span>
        <h2 class="section-h2">One platform for the entire FYP lifecycle</h2>
        <p class="section-p"><?= e($systemName) ?> replaces scattered emails, spreadsheets, and paper forms with a single structured system for Diploma and Degree Final Year Projects at <?= e($institutionName) ?>. Every submission, evaluation, and approval is tracked, timestamped, and auditable.</p>
        <div class="about-points">
          <div class="about-point"><i class="fa-solid fa-check"></i> Role-based dashboards for Admin, Coordinator, Supervisor, Panel & Student</div>
          <div class="about-point"><i class="fa-solid fa-check"></i> Structured rubric-based evaluation with automated scoring</div>
          <div class="about-point"><i class="fa-solid fa-check"></i> Built-in role-aware FAQ assistant curated by the system administrator</div>
        </div>
      </div>
      <div class="col-lg-6">
        <video autoplay muted loop playsinline class="rounded-video">
          <source src="assets/videos/student_dashboard_demo.mp4" type="video/mp4">
        </video>
        <p class="text-center text-muted small mt-2">Live preview — Student Dashboard</p>
      </div>
    </div>
  </div>
</section>

<!-- FEATURES -->
<section class="section-pad bg-soft-section" id="features">
  <div class="container-fy">
    <div class="text-center mb-5">
      <span class="eyebrow">Features</span>
      <h2 class="section-h2">Everything your FYP journey needs</h2>
    </div>
    <div class="row g-4">
      <?php
      $features = [
        ['upload','Document Submission','Upload reports, slides, and posters into structured categories set by your coordinator, with full version history.'],
        ['user-check','Supervisor Matching','Browse supervisor profiles by faculty and research interest, then send a supervision request in one click.'],
        ['clipboard-check','Rubric-Based Evaluation','Supervisors and panels score work against transparent, weighted rubrics for consistent, fair grading.'],
        ['calendar-days','Timeline Management','Coordinators define submission and evaluation windows that automatically govern system access.'],
        ['robot','AI FYP Assistant','A floating chatbot answers questions about submissions, deadlines, and evaluation procedures 24/7.'],
        ['certificate','Student Certificates & Lecturer Letters','Auto-generated completion certificates, appointment letters, and invitation letters.'],
      ];
      foreach ($features as [$icon,$title,$desc]): ?>
      <div class="col-md-6 col-lg-4">
        <div class="feature-card">
          <div class="feature-icon"><i class="fa-solid fa-<?= $icon ?>"></i></div>
          <h5><?= e($title) ?></h5>
          <p><?= e($desc) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- WORKFLOW -->
<section class="section-pad" id="workflow">
  <div class="container-fy">
    <div class="text-center mb-5">
      <span class="eyebrow">How It Works</span>
      <h2 class="section-h2">System Workflow</h2>
    </div>
    <div class="workflow-row">
      <?php
      $steps = [
        ['1','Register & Get Matched','Students register with their official UNISEL email and request a supervisor.'],
        ['2','Submit Your Work','Upload chapters, slides, and posters into coordinator-defined categories.'],
        ['3','Get Evaluated','Supervisors and panels score submissions using standardized rubrics.'],
        ['4','Receive Results','View marks, feedback, and download your verified completion certificate.'],
      ];
      foreach ($steps as [$num,$title,$desc]): ?>
      <div class="workflow-step">
        <div class="workflow-num"><?= $num ?></div>
        <h6><?= e($title) ?></h6>
        <p><?= e($desc) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- AI SHOWCASE -->
<section class="section-pad ai-showcase">
  <div class="container-fy">
    <div class="row align-items-center g-5">
      <div class="col-lg-6">
        <span class="eyebrow" style="color:#FFD9AE;">FAQ Assistant</span>
        <h2 class="section-h2" style="color:#fff;">Meet the <?= e(APP_NAME) ?> FAQ Assistant</h2>
        <p class="section-p" style="color:rgba(255,255,255,.75);">Available on every dashboard, the role-aware FAQ assistant matches questions against a knowledge base curated by the system administrator. It does not generate answers from the public internet.</p>
        <div class="about-points">
          <div class="about-point" style="color:#fff;"><i class="fa-solid fa-check" style="color:#F58220;"></i> Instant answers on submissions & deadlines</div>
          <div class="about-point" style="color:#fff;"><i class="fa-solid fa-check" style="color:#F58220;"></i> Explains rubrics and evaluation processes</div>
          <div class="about-point" style="color:#fff;"><i class="fa-solid fa-check" style="color:#F58220;"></i> Remembers context during your session</div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="chat-mockup">
          <div class="chat-mockup-header"><i class="fa-solid fa-robot"></i> <?= e(APP_NAME) ?> FAQ Assistant</div>
          <div class="chat-mockup-body">
            <div class="mock-msg user">How do I request a supervisor?</div>
            <div class="mock-msg bot">Go to 'My Supervisor' in your dashboard, browse available supervisors, then click 'Send Request'. You'll be notified once they respond!</div>
            <div class="mock-msg user">When are results released?</div>
            <div class="mock-msg bot">Check the Timeline page — results follow the schedule set by your Coordinator for this semester.</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ROLES -->
<section class="section-pad" id="roles">
  <div class="container-fy">
    <div class="text-center mb-5">
      <span class="eyebrow">Who Uses FYPES</span>
      <h2 class="section-h2">Built for every role in the FYP process</h2>
    </div>
    <div class="row g-4">
      <?php
      $roles = [
        ['shield-halved','Admin','Manages accounts, role assignments, and system-wide settings.'],
        ['layer-group','Coordinator','Owns rubrics, timelines, panel assignments and academic results.'],
        ['user-tie','Supervisor','Guides and evaluates assigned supervisees throughout the project.'],
        ['users-viewfinder','Panel','Independently evaluates report and presentation quality.'],
        ['graduation-cap','Student','Submits work, tracks progress, and receives results & certificates.'],
      ];
      foreach ($roles as [$icon,$title,$desc]): ?>
      <div class="col-6 col-lg">
        <div class="role-card">
          <div class="role-icon"><i class="fa-solid fa-<?= $icon ?>"></i></div>
          <h6><?= e($title) ?></h6>
          <p><?= e($desc) ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- FAQ -->
<section class="section-pad bg-soft-section" id="faq">
  <div class="container-fy" style="max-width:800px;">
    <div class="text-center mb-5">
      <span class="eyebrow">FAQ</span>
      <h2 class="section-h2">Frequently Asked Questions</h2>
    </div>
    <?php if(!$homepageFaqs): ?>
      <div class="text-center text-muted">No FAQ questions have been published yet.</div>
    <?php else: ?>
    <div class="accordion" id="faqAccordion">
      <?php foreach(array_slice($homepageFaqs,0,5) as $i=>$faq): ?>
      <div class="accordion-item faq-item">
        <h2 class="accordion-header"><button class="accordion-button <?= $i>0?'collapsed':'' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= (int)$faq['id'] ?>"><?= e($faq['question']) ?></button></h2>
        <div id="faq<?= (int)$faq['id'] ?>" class="accordion-collapse collapse <?= $i===0?'show':'' ?>" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted"><?= nl2br(e($faq['answer'])) ?></div></div>
      </div>
      <?php endforeach; ?>
      <?php if(count($homepageFaqs)>5): ?>
      <div class="text-center py-3"><button type="button" class="btn-fy-outline" id="viewAllFaqBtn">View All</button></div>
      <div id="allFaqQuestions" class="d-none" style="max-height:440px;overflow-y:auto;padding-right:4px;">
        <?php foreach(array_slice($homepageFaqs,5) as $faq): ?>
        <div class="accordion-item faq-item">
          <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq<?= (int)$faq['id'] ?>"><?= e($faq['question']) ?></button></h2>
          <div id="faq<?= (int)$faq['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body text-muted"><?= nl2br(e($faq['answer'])) ?></div></div>
        </div>
        <?php endforeach; ?>
      </div>
      <script>
      document.addEventListener('DOMContentLoaded',function(){
        const button=document.getElementById('viewAllFaqBtn'),all=document.getElementById('allFaqQuestions');
        if(button&&all)button.addEventListener('click',function(){const opening=all.classList.contains('d-none');all.classList.toggle('d-none');button.textContent=opening?'Show Fewer':'View All';if(opening)all.scrollIntoView({behavior:'smooth',block:'nearest'});});
      });
      </script>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- CONTACT -->
<section class="section-pad" id="contact">
  <div class="container-fy" style="max-width:640px;">
    <div class="text-center mb-4">
      <span class="eyebrow">Contact</span>
      <h2 class="section-h2">Need help getting started?</h2>
      <p class="section-p">Reach out to the FYP Coordination Office and we'll get back to you shortly.</p>
    </div>
    <div class="contact-card">
      <div class="row g-3">
        <div class="col-md-6"><input class="form-control-fy w-100" placeholder="Full Name"></div>
        <div class="col-md-6"><input class="form-control-fy w-100" placeholder="Email Address"></div>
        <div class="col-12"><textarea class="form-control-fy w-100" rows="4" placeholder="Your message..."></textarea></div>
        <div class="col-12"><button class="btn-fy-orange w-100" type="button">Send Message</button></div>
      </div>
    </div>
  </div>
</section>

<footer class="landing-footer">
  <div class="container-fy d-flex flex-wrap justify-content-between align-items-center gap-3">
    <a href="#" class="site-logo site-logo-footer" aria-label="FYPES home">
      <span class="site-logo-icon" aria-hidden="true"><i class="fa-solid fa-graduation-cap"></i></span>
      <span class="site-logo-word"><span>FY</span>PES</span>
    </a>
    <p class="mb-0 small" style="color:rgba(255,255,255,.55);">© 2026 <?= e($systemName) ?> — <?= e(APP_FULL_NAME) ?>, <?= e($institutionName) ?>. All rights reserved.</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const landingNav = document.getElementById('landingNav');
const navBurger = document.getElementById('navBurger');
const landingMenu = document.getElementById('landingMenu');

function syncLandingNav() {
  landingNav.classList.toggle('scrolled', window.scrollY > 20 || landingMenu.classList.contains('open'));
}

navBurger.addEventListener('click', () => {
  const open = landingMenu.classList.toggle('open');
  navBurger.setAttribute('aria-expanded', open ? 'true' : 'false');
  navBurger.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
  navBurger.querySelector('i').className = open ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
  syncLandingNav();
});

document.querySelectorAll('#landingMenu a').forEach(link => {
  link.addEventListener('click', () => {
    landingMenu.classList.remove('open');
    navBurger.setAttribute('aria-expanded', 'false');
    navBurger.setAttribute('aria-label', 'Open navigation');
    navBurger.querySelector('i').className = 'fa-solid fa-bars';
    syncLandingNav();
  });
});

window.addEventListener('scroll', syncLandingNav, {passive:true});
window.addEventListener('resize', () => {
  if (window.innerWidth >= 1100) {
    landingMenu.classList.remove('open');
    navBurger.setAttribute('aria-expanded', 'false');
    navBurger.querySelector('i').className = 'fa-solid fa-bars';
  }
  syncLandingNav();
});
syncLandingNav();
</script>
</body>
</html>
