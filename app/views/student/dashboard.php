<?php
$pageTitle = 'Dashboard Pelajar';
require BASE_PATH . '/app/views/layouts/header.php';
$user = getSessionUser();
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="dashboard-page">
    <div class="container">
        <div class="page-header">
            <h1>Selamat datang, <?= htmlspecialchars(explode(' ', $user['full_name'])[0]) ?>!</h1>
            <p class="text-muted">Urus tontonan, perbualan dan aduan anda di sini.</p>
        </div>

        <?php $flash = getFlash('success'); if ($flash): ?>
        <div class="alert alert-success"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>
        <?php $flash = getFlash('error'); if ($flash): ?>
        <div class="alert alert-error"><?= htmlspecialchars($flash) ?></div>
        <?php endif; ?>

        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-blue">&#128337;</div>
                <div>
                    <div class="stat-num"><?= count(array_filter($viewings, fn($v) => $v['status'] === 'pending')) ?></div>
                    <div class="stat-label">Tontonan Menunggu</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-green">&#10003;</div>
                <div>
                    <div class="stat-num"><?= count(array_filter($viewings, fn($v) => $v['status'] === 'confirmed')) ?></div>
                    <div class="stat-label">Tontonan Disahkan</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-purple">&#128172;</div>
                <div>
                    <div class="stat-num"><?= count($conversations) ?></div>
                    <div class="stat-label">Perbualan Aktif</div>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-orange">&#9888;</div>
                <div>
                    <div class="stat-num"><?= count($complaints) ?></div>
                    <div class="stat-label">Aduan Dihantar</div>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <!-- Viewing Requests -->
            <section class="card">
                <div class="card-header">
                    <h2>Permintaan Tontonan</h2>
                    <a href="<?= BASE_URL ?>/student/search" class="btn btn-sm btn-outline">+ Cari Lebih</a>
                </div>
                <?php if (empty($viewings)): ?>
                <p class="empty-state">Tiada permintaan tontonan lagi.</p>
                <?php else: ?>
                <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Iklan</th><th>Tarikh</th><th>Masa</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($viewings as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['listing_title']) ?></td>
                        <td><?= date('d M Y', strtotime($v['proposed_date'])) ?></td>
                        <td><?= date('H:i', strtotime($v['proposed_time'])) ?></td>
                        <td><span class="badge badge-<?= $v['status'] ?>"><?= ucfirst($v['status']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </section>

            <!-- Conversations -->
            <section class="card">
                <div class="card-header">
                    <h2>Perbualan</h2>
                </div>
                <?php if (empty($conversations)): ?>
                <p class="empty-state">Tiada perbualan aktif.</p>
                <?php else: ?>
                <ul class="conv-list">
                <?php foreach ($conversations as $c): ?>
                <li class="conv-item">
                    <div class="conv-avatar"><?= strtoupper(substr($c['owner_name'], 0, 1)) ?></div>
                    <div class="conv-info">
                        <strong><?= htmlspecialchars($c['owner_name']) ?></strong>
                        <small><?= htmlspecialchars($c['listing_title']) ?></small>
                    </div>
                    <a href="<?= BASE_URL ?>/student/chat/<?= $c['conversation_id'] ?>" class="btn btn-sm btn-outline">Buka</a>
                </li>
                <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </section>

            <!-- Notifications -->
            <section class="card">
                <div class="card-header"><h2>Notifikasi</h2></div>
                <?php if (empty($notifications)): ?>
                <p class="empty-state">Tiada notifikasi.</p>
                <?php else: ?>
                <ul class="notif-list">
                <?php foreach (array_slice($notifications, 0, 5) as $n): ?>
                <li class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                    <p><?= htmlspecialchars($n['message']) ?></p>
                    <small><?= date('d M Y H:i', strtotime($n['created_at'])) ?></small>
                </li>
                <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </section>

            <!-- Complaints -->
            <section class="card">
                <div class="card-header">
                    <h2>Aduan Saya</h2>
                    <a href="<?= BASE_URL ?>/student/complaint_form" class="btn btn-sm btn-outline">+ Aduan Baharu</a>
                </div>
                <?php if (empty($complaints)): ?>
                <p class="empty-state">Tiada aduan dihantar.</p>
                <?php else: ?>
                <div class="table-responsive">
                <table class="data-table">
                    <thead><tr><th>Kategori</th><th>Pemilik</th><th>Status</th><th>Tarikh</th></tr></thead>
                    <tbody>
                    <?php foreach ($complaints as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['category']) ?></td>
                        <td><?= htmlspecialchars($c['owner_name']) ?></td>
                        <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst(str_replace('_', ' ', $c['status'])) ?></span></td>
                        <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
