<?php
$pageTitle = 'Dashboard Pemilik';
require BASE_PATH . '/app/views/layouts/header.php';
$user = getSessionUser();
$successFlash = getFlash('success');
$errorFlash   = getFlash('error');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="dashboard-page">
<div class="container">
    <div class="page-header">
        <h1>Dashboard Pemilik
            <?php if (($user['owner_type'] ?? '') === 'korporat'): ?>
            <span class="badge badge-corporate" title="Pemilik korporat yang telah disahkan">
                &#11088; Verified — UKM Real Estate
            </span>
            <?php endif; ?>
        </h1>
        <p class="text-muted">Urus iklan, permintaan tontonan dan aduan anda.</p>
        <a href="<?= BASE_URL ?>/owner/listing_form" class="btn btn-primary">+ Tambah Iklan Baharu</a>
    </div>

    <?php if ($successFlash): ?><div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div><?php endif; ?>
    <?php if ($errorFlash):   ?><div class="alert alert-error"><?= htmlspecialchars($errorFlash) ?></div><?php endif; ?>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon stat-blue">&#127968;</div>
            <div><div class="stat-num"><?= count($listings) ?></div><div class="stat-label">Jumlah Iklan</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-green">&#10003;</div>
            <div><div class="stat-num"><?= count(array_filter($listings, fn($l) => $l['status'] === 'active')) ?></div><div class="stat-label">Aktif</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-orange">&#128337;</div>
            <div><div class="stat-num"><?= count(array_filter($viewings, fn($v) => $v['status'] === 'pending')) ?></div><div class="stat-label">Tontonan Baharu</div></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon stat-purple">&#128172;</div>
            <div><div class="stat-num"><?= count($conversations) ?></div><div class="stat-label">Perbualan</div></div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Listings Summary -->
        <section class="card card--full">
            <div class="card-header">
                <h2>Iklan Saya</h2>
                <a href="<?= BASE_URL ?>/owner/listing_manage" class="btn btn-sm btn-outline">Semua Iklan</a>
            </div>
            <?php if (empty($listings)): ?>
            <p class="empty-state">Tiada iklan lagi. <a href="<?= BASE_URL ?>/owner/listing_form">Tambah sekarang</a>.</p>
            <?php else: ?>
            <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Tajuk</th><th>Sewa</th><th>Status</th><th>Tindakan</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($listings, 0, 5) as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['title']) ?></td>
                    <td>RM <?= number_format($l['monthly_rent'], 0) ?></td>
                    <td><span class="badge badge-<?= $l['status'] ?>"><?= ucfirst(str_replace('_', ' ', $l['status'])) ?></span></td>
                    <td>
                        <a href="<?= BASE_URL ?>/owner/listing_form/<?= $l['listing_id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </section>

        <!-- Pending Viewings -->
        <section class="card">
            <div class="card-header">
                <h2>Permintaan Tontonan</h2>
                <a href="<?= BASE_URL ?>/owner/viewing_manage" class="btn btn-sm btn-outline">Semua</a>
            </div>
            <?php $pending = array_filter($viewings, fn($v) => $v['status'] === 'pending'); ?>
            <?php if (empty($pending)): ?>
            <p class="empty-state">Tiada permintaan baharu.</p>
            <?php else: ?>
            <ul class="viewing-list">
            <?php foreach (array_slice($pending, 0, 4) as $v): ?>
            <li class="viewing-item">
                <div class="viewing-info">
                    <strong><?= htmlspecialchars($v['student_name']) ?></strong>
                    <small><?= htmlspecialchars($v['listing_title']) ?></small>
                    <small>&#128197; <?= date('d M Y', strtotime($v['proposed_date'])) ?> &#128336; <?= date('H:i', strtotime($v['proposed_time'])) ?></small>
                </div>
                <div class="viewing-actions">
                    <form method="POST" action="<?= BASE_URL ?>/owner/viewing_action/<?= $v['request_id'] ?>" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="confirmed">
                        <button class="btn btn-sm btn-green">Sahkan</button>
                    </form>
                    <form method="POST" action="<?= BASE_URL ?>/owner/viewing_action/<?= $v['request_id'] ?>" style="display:inline">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="rejected">
                        <button class="btn btn-sm btn-red">Tolak</button>
                    </form>
                </div>
            </li>
            <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>

        <!-- Conversations -->
        <section class="card">
            <div class="card-header">
                <h2>Peti Masuk</h2>
                <a href="<?= BASE_URL ?>/owner/chat_inbox" class="btn btn-sm btn-outline">Semua</a>
            </div>
            <?php if (empty($conversations)): ?>
            <p class="empty-state">Tiada perbualan.</p>
            <?php else: ?>
            <ul class="conv-list">
            <?php foreach (array_slice($conversations, 0, 4) as $c): ?>
            <li class="conv-item">
                <div class="conv-avatar"><?= strtoupper(substr($c['student_name'], 0, 1)) ?></div>
                <div class="conv-info">
                    <strong><?= htmlspecialchars($c['student_name']) ?></strong>
                    <small><?= htmlspecialchars($c['listing_title']) ?></small>
                </div>
                <a href="<?= BASE_URL ?>/owner/chat_inbox/<?= $c['conversation_id'] ?>" class="btn btn-sm btn-outline">Buka</a>
            </li>
            <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>

        <!-- Complaints against this owner -->
        <section class="card">
            <div class="card-header"><h2>Aduan Terhadap Saya</h2></div>
            <?php $openComplaints = array_filter($complaints, fn($c) => $c['status'] !== 'resolved'); ?>
            <?php if (empty($complaints)): ?>
            <p class="empty-state">Tiada aduan.</p>
            <?php else: ?>
            <div class="table-responsive">
            <table class="data-table">
                <thead><tr><th>Kategori</th><th>Status</th><th>Tarikh</th><th>Tindakan</th></tr></thead>
                <tbody>
                <?php foreach (array_slice($complaints, 0, 5) as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['category']) ?></td>
                    <td><span class="badge badge-<?= $c['status'] ?>"><?= ucfirst(str_replace('_',' ',$c['status'])) ?></span></td>
                    <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
                    <td>
                        <?php if (!$c['owner_defense'] && $c['status'] !== 'resolved'): ?>
                        <a href="<?= BASE_URL ?>/owner/complaint_response/<?= $c['complaint_id'] ?>"
                           class="btn btn-sm btn-primary">Balas</a>
                        <?php else: ?>
                        <span class="text-muted">Dibalas</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
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
    </div>
</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
