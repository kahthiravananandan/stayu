<?php
$pageTitle = 'Notifikasi';
require BASE_PATH . '/app/views/layouts/header.php';
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container container--narrow">
    <div class="page-header">
        <h1>&#128276; Notifikasi</h1>
        <p class="text-muted"><?= count($notifications) ?> notifikasi</p>
    </div>

    <?php if (empty($notifications)): ?>
    <div class="card card-body">
        <p class="empty-state">Tiada notifikasi lagi.</p>
    </div>
    <?php else: ?>
    <div class="card">
        <ul class="notif-list" style="margin:0;padding:0;list-style:none">
        <?php foreach ($notifications as $n): ?>
        <li class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>"
            style="display:flex;align-items:flex-start;gap:12px;padding:14px 20px;border-bottom:1px solid #f0f0f0">
            <span style="font-size:1.2rem;flex-shrink:0"><?= match($n['type']) {
                'viewing_request'     => '&#128197;',
                'viewing_confirmed'   => '&#10003;',
                'viewing_rejected'    => '&#10007;',
                'viewing_action'      => '&#128197;',
                'complaint'           => '&#9888;',
                'complaint_resolved'  => '&#9989;',
                default               => '&#128276;',
            } ?></span>
            <div style="flex:1;min-width:0">
                <p style="margin:0 0 4px"><?= htmlspecialchars($n['message']) ?></p>
                <small class="text-muted"><?= date('d M Y, H:i', strtotime($n['created_at'])) ?></small>
            </div>
            <?php if (!$n['is_read']): ?>
            <span style="width:8px;height:8px;border-radius:50%;background:var(--primary,#4f46e5);flex-shrink:0;margin-top:6px"></span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <div style="margin-top:16px">
        <a href="<?= BASE_URL ?>/student/dashboard" class="btn btn-ghost">&#8592; Kembali ke Dashboard</a>
    </div>
</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
