<?php
/**
 * Shared dashboard chrome - include AFTER setting these variables:
 * $pageTitle (string), $navGroups (array of ['label'=>, 'items'=>[['icon','label','view','badge'?]]])
 * $activeView (string), $themeColor (hex), $themeColorSoft (hex), $roleLabelText (string)
 * $user = currentUser();
 */
$chatbotEnabled = getSetting($pdo, 'chatbot_enabled', '1') === '1';
$systemName = getSetting($pdo, 'system_name', APP_NAME) ?: APP_NAME;
$institutionName = getSetting($pdo, 'institution_name', APP_INSTITUTION) ?: APP_INSTITUTION;
$darkMode = getSetting($pdo, 'dark_mode', '0') === '1';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCSRFToken($_POST['csrf_token'] ?? null)) {
    if (($_POST['notification_action'] ?? '') === 'open') {
        $nid=(int)($_POST['notification_id'] ?? 0);
        $n=safeRun($pdo,'SELECT link FROM notifications WHERE id=? AND user_id=?',[$nid,$user['id']])->fetch();
        if($n){safeRun($pdo,'UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?',[$nid,$user['id']]);$dest=preg_match('/^[a-z0-9-]+$/i',$n['link']??'')?$n['link']:'overview';redirect('?view='.$dest);}
    }
    if (($_POST['notification_action'] ?? '') === 'mark_all') {safeRun($pdo,'UPDATE notifications SET is_read=1 WHERE user_id=?',[$user['id']]);redirect('?view='.urlencode($activeView));}
}
$unreadCount = unreadNotificationCount($pdo);
$notifStmt = safeRun($pdo, "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 8", [$user['id']]);
$recentNotifs = $notifStmt ? $notifStmt->fetchAll() : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e($systemName) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Fraunces:wght@600;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
<style>
  :root{
    --theme: <?= $themeColor ?>;
    --theme-soft: <?= $themeColorSoft ?>;
  }
</style>
</head>
<body class="dash-body <?= $darkMode ? 'dark-mode' : '' ?>">
<div class="dash-shell">
  <aside class="dash-sidebar" id="dashSidebar">
    <div class="sidebar-brand" title="<?= e($systemName) ?> — <?= e($institutionName) ?>">
      <div class="brand-icon" style="background:var(--theme)"><i class="fa-solid fa-graduation-cap"></i></div>
      <div class="brand-copy">
        <div class="brand-name" title="<?= e($systemName) ?>"><?= e($systemName) ?></div>
        <div class="brand-sub" title="<?= e($institutionName) ?>"><?= e($institutionName) ?></div>
      </div>
      <button type="button" class="sidebar-collapse-btn d-lg-none" onclick="toggleDashboardSidebar(false)" aria-label="Close sidebar"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="sidebar-user">
      <div class="user-avatar" style="background:var(--theme-soft);color:var(--theme)">
        <?= strtoupper(substr($user['name'],0,1)) ?>
      </div>
      <div class="user-meta">
        <div class="user-name"><?= e($user['name']) ?></div>
        <div class="user-role"><?= e($roleLabelText) ?></div>
        <?php if (isset($levelBadge)): ?><span class="badge-fy badge-info mt-1" style="display:inline-block;"><?= e($levelBadge) ?></span><?php endif; ?>
      </div>
    </div>

    <nav class="sidebar-nav">
      <?php foreach ($navGroups as $group): ?>
        <?php if (!empty($group['label'])): ?><div class="nav-group-label"><?= e($group['label']) ?></div><?php endif; ?>
        <?php foreach ($group['items'] as $item): ?>
          <a href="?view=<?= e($item['view']) ?>" class="nav-item <?= $activeView === $item['view'] ? 'active' : '' ?>">
            <i class="fa-solid fa-<?= e($item['icon']) ?>"></i>
            <span><?= e($item['label']) ?></span>
            <?php if (!empty($item['badge'])): ?><span class="nav-badge"><?= (int)$item['badge'] ?></span><?php endif; ?>
          </a>
        <?php endforeach; ?>
      <?php endforeach; ?>
    </nav>

    <div class="sidebar-footer">
      <a href="<?= BASE_URL ?>/auth/logout.php" class="nav-item logout-item">
        <i class="fa-solid fa-right-from-bracket"></i><span>Logout</span>
      </a>
    </div>
  </aside>
  <button type="button" class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleDashboardSidebar(false)" aria-label="Close sidebar"></button>

  <div class="dash-main">
    <header class="dash-topbar">
      <button type="button" class="mobile-menu-btn d-lg-none" onclick="toggleDashboardSidebar(true)" aria-label="Open sidebar"><i class="fa-solid fa-bars"></i></button>
      <h1 class="topbar-title"><?= e($pageTitle) ?></h1>
      <div class="topbar-actions">
        <div class="dropdown">
          <button class="icon-btn" data-bs-toggle="dropdown"><i class="fa-regular fa-bell"></i><?php if ($unreadCount>0): ?><span class="notif-dot"></span><?php endif; ?></button>
          <div class="dropdown-menu dropdown-menu-end notif-dropdown">
            <div class="notif-header">Notifications <?php if($unreadCount>0): ?><span class="text-muted small">(<?= $unreadCount ?> new)</span><?php endif; ?></div>
            <?php if ($unreadCount>0): ?><form method="post" class="px-3 pb-2"><?= csrfField() ?><input type="hidden" name="notification_action" value="mark_all"><button class="btn btn-link btn-sm p-0">Mark all as read</button></form><?php endif; ?>
            <?php if (empty($recentNotifs)): ?><div class="p-3 text-center text-muted small">No notifications yet</div>
            <?php else: foreach ($recentNotifs as $n): ?><form method="post" class="m-0"><?= csrfField() ?><input type="hidden" name="notification_action" value="open"><input type="hidden" name="notification_id" value="<?= $n['id'] ?>"><button class="notif-item w-100 text-start border-0 <?= !$n['is_read'] ? 'unread' : '' ?>" style="background:inherit"><div class="fw-semibold small"><?= e($n['title']) ?></div><div class="small text-muted"><?= e($n['message']) ?></div><div class="small text-muted mt-1"><?= timeAgo($n['created_at']) ?></div></button></form><?php endforeach; endif; ?>
          </div>
        </div>
        <div class="user-avatar-sm" style="background:var(--theme-soft);color:var(--theme)"><?= strtoupper(substr($user['name'],0,1)) ?></div>
      </div>
    </header>
    <main class="dash-content">
