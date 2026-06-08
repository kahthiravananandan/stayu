<?php
$pageTitle = 'Notifikasi Admin';
require BASE_PATH . '/app/views/layouts/header.php';
$typeIcons = [
    'document_upload'   => '&#128196;',
    'document_approved' => '&#127881;',
    'document_rejected' => '&#128308;',
    'complaint'         => '&#9888;',
    'complaint_resolved'=> '&#10003;',
    'complaint_closed'  => '&#128221;',
    'account_suspended' => '&#9940;',
    'listing_suspended' => '&#128683;',
    'system'            => '&#8505;',
];
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container container--narrow">
    <div class="card">
        <div class="card-header">
            <h1>Notifikasi</h1>
        </div>
        <div class="card-body">
            <?php if (empty($notifications)): ?>
            <p class="empty-state">Tiada notifikasi.</p>
            <?php else: ?>
            <ul class="notif-list notif-list--full">
            <?php foreach ($notifications as $n): ?>
            <?php $icon = $typeIcons[$n['type']] ?? '&#8226;'; ?>
            <li class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                <span class="notif-icon"><?= $icon ?></span>
                <div class="notif-body">
                    <p><?= htmlspecialchars($n['message']) ?></p>
                    <small class="text-muted"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></small>
                </div>
                <?php if (!$n['is_read']): ?>
                <span class="notif-dot" aria-label="Belum dibaca"></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
