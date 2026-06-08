<?php
$pageTitle = 'Monitor Sistem';
require BASE_PATH . '/app/views/layouts/header.php';
$successFlash = getFlash('success');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="dashboard-page">
<div class="container">
    <div class="page-header"><h1>Monitor Sistem</h1></div>

    <?php if ($successFlash): ?><div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div><?php endif; ?>

    <!-- Stats overview -->
    <div class="stats-grid stats-grid--wide">
        <?php foreach ([
            ['Pelajar',         $stats['total_students'],    'stat-blue'],
            ['Pemilik',         $stats['total_owners'],      'stat-purple'],
            ['Iklan Aktif',     $stats['active_listings'],   'stat-green'],
            ['Dalam Semakan',   $stats['in_review'],         'stat-orange'],
            ['Dok. Menunggu',   $stats['pending_docs'],      'stat-red'],
            ['Aduan Terbuka',   $stats['open_complaints'],   'stat-yellow'],
            ['Iklan Digantung', $stats['suspended_listings'],'stat-gray'],
        ] as [$label, $val, $cls]): ?>
        <div class="stat-card">
            <div class="stat-icon <?= $cls ?>"><?= $val ?></div>
            <div class="stat-label"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Tabbed management panels -->
    <div class="tab-panel" id="monitorTabs">
        <div class="tabs">
            <button class="tab active" data-panel="owners-panel">
                Pemilik (<?= count($allOwners) ?>)
            </button>
            <button class="tab" data-panel="students-panel">
                Pelajar (<?= count($allStudents) ?>)
            </button>
            <button class="tab" data-panel="listings-panel">
                Semua Iklan (<?= count($allListings) ?>)
            </button>
        </div>

        <!-- Owners tab -->
        <div id="owners-panel" class="tab-content">
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Nama</th><th>Jenis</th><th>E-mel</th><th>Telefon</th><th>Status</th><th>Tindakan</th></tr>
                </thead>
                <tbody>
                <?php foreach ($allOwners as $o): ?>
                <tr>
                    <td><?= htmlspecialchars($o['full_name']) ?></td>
                    <td>
                        <?php if ($o['owner_type'] === 'korporat'): ?>
                        <span class="badge badge-corporate" style="font-size:.72rem">&#11088; Korporat</span>
                        <?php else: ?>
                        <span class="badge badge-individu">Individu</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($o['email'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($o['phone_number'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= $o['status'] ?>"><?= ucfirst($o['status']) ?></span></td>
                    <td>
                        <form method="POST"
                              action="<?= BASE_URL ?>/admin/suspend_user/<?= $o['user_id'] ?>"
                              style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <?php if ($o['status'] === 'active'): ?>
                            <input type="hidden" name="action" value="suspend">
                            <button class="btn btn-sm btn-red"
                                    onclick="return confirm('Gantung akaun <?= htmlspecialchars(addslashes($o['full_name'])) ?>?')">
                                Gantung
                            </button>
                            <?php else: ?>
                            <input type="hidden" name="action" value="activate">
                            <button class="btn btn-sm btn-green"
                                    onclick="return confirm('Aktifkan semula akaun ini?')">
                                Aktifkan
                            </button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- Students tab -->
        <div id="students-panel" class="tab-content hidden">
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Nama</th><th>Matrik</th><th>Telefon</th><th>Status</th><th>Tindakan</th></tr>
                </thead>
                <tbody>
                <?php foreach ($allStudents as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['full_name']) ?></td>
                    <td><?= htmlspecialchars($s['matric_number'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($s['phone_number'] ?? '—') ?></td>
                    <td><span class="badge badge-<?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
                    <td>
                        <form method="POST"
                              action="<?= BASE_URL ?>/admin/suspend_user/<?= $s['user_id'] ?>"
                              style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <?php if ($s['status'] === 'active'): ?>
                            <input type="hidden" name="action" value="suspend">
                            <button class="btn btn-sm btn-red"
                                    onclick="return confirm('Gantung akaun <?= htmlspecialchars(addslashes($s['full_name'])) ?>?')">
                                Gantung
                            </button>
                            <?php else: ?>
                            <input type="hidden" name="action" value="activate">
                            <button class="btn btn-sm btn-green"
                                    onclick="return confirm('Aktifkan semula akaun ini?')">
                                Aktifkan
                            </button>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </div>

        <!-- All listings tab -->
        <div id="listings-panel" class="tab-content hidden">
            <?php if (empty($allListings)): ?>
            <p class="empty-state">Tiada iklan dalam sistem.</p>
            <?php else: ?>
            <div class="table-responsive">
            <table class="data-table" id="listingsTable">
                <thead>
                    <tr>
                        <th>Tajuk</th><th>Pemilik</th><th>Sewa (RM)</th>
                        <th>Status</th><th>Tindakan</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($allListings as $l): ?>
                <tr>
                    <td><?= htmlspecialchars($l['title']) ?></td>
                    <td><?= htmlspecialchars($l['owner_name']) ?></td>
                    <td><?= number_format($l['monthly_rent'], 0) ?></td>
                    <td>
                        <span class="badge badge-<?= $l['status'] ?>">
                            <?= ucfirst(str_replace('_', ' ', $l['status'])) ?>
                        </span>
                    </td>
                    <td>
                        <form method="POST"
                              action="<?= BASE_URL ?>/admin/suspend_listing/<?= $l['listing_id'] ?>"
                              style="display:inline">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <?php if ($l['status'] === 'suspended'): ?>
                            <input type="hidden" name="action" value="activate">
                            <button class="btn btn-sm btn-green"
                                    onclick="return confirm('Aktifkan semula iklan ini?')">
                                Aktifkan
                            </button>
                            <?php elseif (in_array($l['status'], ['active', 'in_negotiation', 'in_review'])): ?>
                            <input type="hidden" name="action" value="suspend">
                            <button class="btn btn-sm btn-red"
                                    onclick="return confirm('Gantung iklan &quot;<?= htmlspecialchars(addslashes($l['title'])) ?>&quot;?')">
                                Gantung
                            </button>
                            <?php else: ?>
                            <span class="text-muted">—</span>
                            <?php endif; ?>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
<script>
document.querySelectorAll('#monitorTabs .tab').forEach(btn => {
    btn.addEventListener('click', function () {
        document.querySelectorAll('#monitorTabs .tab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.tab-content').forEach(p => p.classList.add('hidden'));
        document.getElementById(this.dataset.panel).classList.remove('hidden');
    });
});
</script>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
