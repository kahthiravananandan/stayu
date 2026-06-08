<?php
$role        = getSessionRole();
$currentUser = isLoggedIn() ? getSessionUser() : null;
$unreadCount  = 0;
$recentNotifs = [];
if ($currentUser) {
    $notifModel   = new Notification();
    $unreadCount  = $notifModel->countUnread((int)$currentUser['user_id']);
    $recentNotifs = $notifModel->getRecent((int)$currentUser['user_id'], 10);
}
$currentUrl = $_SERVER['REQUEST_URI'] ?? '';
function navActive(string $path): string {
    global $currentUrl;
    return str_contains($currentUrl, $path) ? 'active' : '';
}
?>
<nav class="navbar" id="mainNav">
    <div class="container nav-container">
        <a href="<?= BASE_URL ?>/student/search" class="nav-brand">
            <span class="brand-stay">Stay</span><span class="brand-u">U</span>
        </a>

        <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
            <span></span><span></span><span></span>
        </button>

        <div class="nav-menu" id="navMenu">
            <ul class="nav-links">
                <li><a href="<?= BASE_URL ?>/student/search" class="<?= navActive('/student/search') ?>">Cari</a></li>
                <li><a href="<?= BASE_URL ?>/student/map" class="<?= navActive('/student/map') ?>">Peta</a></li>
                <?php if ($role === 'pelajar'): ?>
                <li><a href="<?= BASE_URL ?>/student/dashboard" class="<?= navActive('/student/dashboard') ?>">Dashboard</a></li>
                <?php elseif ($role === 'pemilik'): ?>
                <li><a href="<?= BASE_URL ?>/owner/dashboard" class="<?= navActive('/owner/dashboard') ?>">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/owner/listing_form" class="<?= navActive('/owner/listing_form') ?>">+ Iklan</a></li>
                <?php elseif ($role === 'admin'): ?>
                <li><a href="<?= BASE_URL ?>/admin/dashboard" class="<?= navActive('/admin/dashboard') ?>">Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>/admin/document_review" class="<?= navActive('/document_review') ?>">Semakan</a></li>
                <li><a href="<?= BASE_URL ?>/admin/complaint_panel" class="<?= navActive('/complaint_panel') ?>">Aduan</a></li>
                <li><a href="<?= BASE_URL ?>/admin/system_monitor" class="<?= navActive('/system_monitor') ?>">Monitor</a></li>
                <?php endif; ?>
            </ul>

            <div class="nav-actions">
                <?php if ($currentUser): ?>
                <?php
                $notifPageUrl = match($role) {
                    'pemilik' => BASE_URL . '/owner/notifications',
                    'admin'   => BASE_URL . '/admin/notifications',
                    default   => BASE_URL . '/student/notifications',
                };
                ?>
                <div class="notif-bell-wrap" id="notifBellWrap">
                    <button type="button" class="nav-bell" id="bellBtn" aria-label="Notifikasi">
                        <svg viewBox="0 0 24 24" width="20" height="20" stroke="currentColor" fill="none" stroke-width="2">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <?php if ($unreadCount > 0): ?>
                        <span class="notif-badge"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </button>

                    <div class="notif-dropdown" id="notifDropdown">
                        <div class="notif-drop-header">
                            <strong>Notifikasi</strong>
                            <?php if ($unreadCount > 0): ?>
                            <a href="<?= BASE_URL ?>/notifications/mark_all_read" class="notif-mark-all">Semua baca</a>
                            <?php endif; ?>
                        </div>
                        <?php if (empty($recentNotifs)): ?>
                        <p class="notif-empty">Tiada notifikasi.</p>
                        <?php else: ?>
                        <?php foreach ($recentNotifs as $n): ?>
                        <a href="<?= BASE_URL ?>/notifications/mark_read/<?= (int)$n['notification_id'] ?>"
                           class="notif-drop-item <?= $n['is_read'] ? '' : 'unread' ?>">
                            <p><?= htmlspecialchars($n['message']) ?></p>
                            <small><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></small>
                        </a>
                        <?php endforeach; ?>
                        <a href="<?= $notifPageUrl ?>" class="notif-view-all">Lihat semua notifikasi &rarr;</a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="nav-user-menu" id="userMenuToggle">
                    <button class="nav-user-btn" type="button">
                        <?php if (($role === 'pelajar' || $role === 'pemilik') && !empty($currentUser['profile_photo'])): ?>
                        <img src="<?= BASE_URL ?>/public/uploads/photos/<?= htmlspecialchars($currentUser['profile_photo']) ?>"
                             alt="" class="user-avatar-img">
                        <?php else: ?>
                        <span class="user-avatar"><?= strtoupper(substr($currentUser['full_name'], 0, 1)) ?></span>
                        <?php endif; ?>
                        <span class="user-name"><?= htmlspecialchars(explode(' ', $currentUser['full_name'])[0]) ?></span>
                        <svg width="12" height="12" viewBox="0 0 12 12"><path d="M2 4l4 4 4-4" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>
                    </button>
                    <div class="dropdown-menu" id="userDropdown">
                        <div class="dropdown-header">
                            <strong><?= htmlspecialchars($currentUser['full_name']) ?></strong>
                            <small><?= ucfirst($role) ?></small>
                        </div>
                        <hr>
                        <?php if ($role === 'pelajar'): ?>
                        <a href="<?= BASE_URL ?>/student/profile">Profil Saya</a>
                        <a href="<?= BASE_URL ?>/student/notifications">Semua Notifikasi</a>
                        <hr>
                        <?php elseif ($role === 'pemilik'): ?>
                        <a href="<?= BASE_URL ?>/owner/profile">Profil Saya</a>
                        <a href="<?= BASE_URL ?>/owner/notifications">Semua Notifikasi</a>
                        <hr>
                        <?php elseif ($role === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/admin/profile">Profil Saya</a>
                        <a href="<?= BASE_URL ?>/admin/notifications">Semua Notifikasi</a>
                        <hr>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>/auth/logout">Log Keluar</a>
                    </div>
                </div>
                <?php else: ?>
                <a href="<?= BASE_URL ?>/auth/login" class="btn btn-outline btn-sm">Log Masuk</a>
                <a href="<?= BASE_URL ?>/auth/register" class="btn btn-primary btn-sm">Daftar</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>
