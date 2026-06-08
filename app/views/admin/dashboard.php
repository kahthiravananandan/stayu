<?php
$pageTitle = 'Dashboard Admin';
require BASE_PATH . '/app/views/layouts/header.php';
$successFlash = getFlash('success');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="dashboard-page">
<div class="container">
    <div class="page-header">
        <h1>Dashboard Admin — PPP UKM</h1>
        <p class="text-muted">Pantau sistem, semak dokumen dan selesaikan aduan.</p>
    </div>

    <?php if ($successFlash): ?><div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div><?php endif; ?>

    <!-- Stats Grid -->
    <div class="stats-grid stats-grid--wide">
        <div class="stat-card stat-card--blue">
            <div class="stat-icon">&#127979;</div>
            <div><div class="stat-num"><?= $stats['total_students'] ?></div><div class="stat-label">Pelajar</div></div>
        </div>
        <div class="stat-card stat-card--purple">
            <div class="stat-icon">&#127968;</div>
            <div><div class="stat-num"><?= $stats['total_owners'] ?></div><div class="stat-label">Pemilik</div></div>
        </div>
        <div class="stat-card stat-card--green">
            <div class="stat-icon">&#10003;</div>
            <div><div class="stat-num"><?= $stats['active_listings'] ?></div><div class="stat-label">Iklan Aktif</div></div>
        </div>
        <div class="stat-card stat-card--orange">
            <div class="stat-icon">&#128337;</div>
            <div><div class="stat-num"><?= $stats['in_review'] ?></div><div class="stat-label">Dalam Semakan</div></div>
        </div>
        <div class="stat-card stat-card--red">
            <div class="stat-icon">&#128196;</div>
            <div>
                <div class="stat-num"><?= $stats['pending_docs'] ?></div>
                <div class="stat-label">Dok. Menunggu</div>
            </div>
            <?php if ($stats['pending_docs'] > 0): ?>
            <a href="<?= BASE_URL ?>/admin/document_review" class="btn btn-sm btn-outline mt-1">Semak</a>
            <?php endif; ?>
        </div>
        <div class="stat-card stat-card--yellow">
            <div class="stat-icon">&#9888;</div>
            <div>
                <div class="stat-num"><?= $stats['open_complaints'] ?></div>
                <div class="stat-label">Aduan Terbuka</div>
            </div>
            <?php if ($stats['open_complaints'] > 0): ?>
            <a href="<?= BASE_URL ?>/admin/complaint_panel" class="btn btn-sm btn-outline mt-1">Urus</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Quick links -->
        <section class="card">
            <div class="card-header"><h2>Tindakan Pantas</h2></div>
            <div class="quick-actions">
                <a href="<?= BASE_URL ?>/admin/document_review" class="quick-action-card">
                    <span class="qa-icon">&#128196;</span>
                    <span>Semak Dokumen</span>
                    <?php if ($stats['pending_docs'] > 0): ?>
                    <span class="qa-badge"><?= $stats['pending_docs'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/admin/complaint_panel" class="quick-action-card">
                    <span class="qa-icon">&#9888;</span>
                    <span>Panel Aduan</span>
                    <?php if ($stats['open_complaints'] > 0): ?>
                    <span class="qa-badge"><?= $stats['open_complaints'] ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?= BASE_URL ?>/admin/register_corporate" class="quick-action-card">
                    <span class="qa-icon">&#127970;</span>
                    <span>Daftar Korporat</span>
                </a>
                <a href="<?= BASE_URL ?>/admin/system_monitor" class="quick-action-card">
                    <span class="qa-icon">&#128202;</span>
                    <span>Monitor Sistem</span>
                </a>
            </div>
        </section>

        <!-- Pending Documents -->
        <section class="card">
            <div class="card-header">
                <h2>Dokumen Menunggu Semakan</h2>
                <a href="<?= BASE_URL ?>/admin/document_review" class="btn btn-sm btn-outline">Semua</a>
            </div>
            <?php if (empty($pendingDocs)): ?>
            <p class="empty-state">Tiada dokumen menunggu semakan.</p>
            <?php else: ?>
            <ul class="doc-list">
            <?php foreach (array_slice($pendingDocs, 0, 5) as $d): ?>
            <li class="doc-item">
                <div class="doc-info">
                    <strong><?= htmlspecialchars($d['listing_title']) ?></strong>
                    <small><?= htmlspecialchars($d['owner_name']) ?> &bull; <?= date('d M Y', strtotime($d['submitted_at'])) ?></small>
                </div>
                <a href="<?= BASE_URL ?>/admin/document_review/<?= $d['document_id'] ?>" class="btn btn-sm btn-primary">Semak</a>
            </li>
            <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>

        <!-- Open Complaints -->
        <section class="card">
            <div class="card-header">
                <h2>Aduan Terbuka</h2>
                <a href="<?= BASE_URL ?>/admin/complaint_panel" class="btn btn-sm btn-outline">Semua</a>
            </div>
            <?php $open = array_filter($openComplaints, fn($c) => $c['status'] === 'open'); ?>
            <?php if (empty($open)): ?>
            <p class="empty-state">Tiada aduan terbuka.</p>
            <?php else: ?>
            <ul class="doc-list">
            <?php foreach (array_slice($open, 0, 5) as $c): ?>
            <li class="doc-item">
                <div class="doc-info">
                    <strong><?= htmlspecialchars($c['category']) ?></strong>
                    <small><?= htmlspecialchars($c['student_name']) ?> terhadap <?= htmlspecialchars($c['owner_name']) ?></small>
                </div>
                <a href="<?= BASE_URL ?>/admin/complaint_panel/<?= $c['complaint_id'] ?>" class="btn btn-sm btn-primary">Urus</a>
            </li>
            <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>
    </div>
</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
