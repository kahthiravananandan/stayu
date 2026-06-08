<?php
$pageTitle    = 'Panel Aduan';
require BASE_PATH . '/app/views/layouts/header.php';
$successFlash = getFlash('success');
$errorFlash   = getFlash('error');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="dashboard-page">
<div class="container">

<?php if (isset($complaint)): ?>
    <!-- Single complaint detail + resolution -->
    <nav class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/complaint_panel">Panel Aduan</a> &rsaquo;
        Aduan #<?= $complaint['complaint_id'] ?>
    </nav>

    <?php if ($successFlash): ?><div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div><?php endif; ?>
    <?php if ($errorFlash):   ?><div class="alert alert-error"><?= htmlspecialchars($errorFlash) ?></div><?php endif; ?>

    <!-- Two-column: student complaint vs owner defense -->
    <div class="doc-review-grid">
        <div class="card">
            <div class="card-header"><h2>&#9888; Aduan Pelajar</h2></div>
            <div class="card-body">
                <div class="detail-row">
                    <span class="label">Pengadu:</span>
                    <?= htmlspecialchars($complaint['student_name']) ?>
                    <?php if ($complaint['matric_number']): ?>
                    <small class="text-muted">(<?= htmlspecialchars($complaint['matric_number']) ?>)</small>
                    <?php endif; ?>
                </div>
                <div class="detail-row">
                    <span class="label">Pemilik Dilaporkan:</span>
                    <?= htmlspecialchars($complaint['owner_name']) ?>
                </div>
                <?php if ($complaint['listing_title']): ?>
                <div class="detail-row">
                    <span class="label">Iklan:</span>
                    <?= htmlspecialchars($complaint['listing_title']) ?>
                </div>
                <?php endif; ?>
                <div class="detail-row">
                    <span class="label">Kategori:</span>
                    <?= htmlspecialchars($complaint['category']) ?>
                </div>
                <div class="detail-row">
                    <span class="label">Penerangan:</span>
                    <div class="complaint-text"><?= nl2br(htmlspecialchars($complaint['description'])) ?></div>
                </div>
                <div class="detail-row">
                    <span class="label">Tarikh Aduan:</span>
                    <?= date('d M Y, H:i', strtotime($complaint['created_at'])) ?>
                </div>
                <div class="detail-row">
                    <span class="label">Status:</span>
                    <span class="badge badge-<?= $complaint['status'] ?>">
                        <?= ucfirst(str_replace('_', ' ', $complaint['status'])) ?>
                    </span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>&#128172; Pembelaan Pemilik</h2></div>
            <div class="card-body">
                <?php if ($complaint['owner_defense']): ?>
                <div class="complaint-text"><?= nl2br(htmlspecialchars($complaint['owner_defense'])) ?></div>
                <?php if ($complaint['defense_evidence']): ?>
                <div class="mt-2">
                    <a href="<?= BASE_URL ?>/admin/serve_evidence/<?= $complaint['complaint_id'] ?>"
                       target="_blank" class="btn btn-sm btn-outline">&#128196; Lihat Bukti Lampiran</a>
                </div>
                <?php endif; ?>
                <?php else: ?>
                <p class="empty-state">Pemilik belum menghantar pembelaan.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Decision panel with explicit action buttons -->
    <?php if ($complaint['status'] !== 'resolved'): ?>
    <div class="card card-body doc-decision-panel">
        <h2>Ambil Tindakan</h2>
        <p class="text-muted mb-3">
            Pilih tindakan yang sesuai. Kedua-dua pelajar dan pemilik akan dimaklumkan secara automatik.
        </p>
        <form method="POST"
              action="<?= BASE_URL ?>/admin/complaint_panel/<?= $complaint['complaint_id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">
            <div class="complaint-action-grid">
                <?php if (!empty($complaint['listing_id'])): ?>
                <button type="submit" name="action" value="suspend_listing"
                        class="btn btn-red complaint-action-btn"
                        onclick="return confirm('Gantung iklan berkaitan? Tindakan ini akan menyekat iklan daripada carian pelajar.')">
                    <span class="action-icon">&#128683;</span>
                    <span class="action-label">Gantung Iklan</span>
                    <span class="action-desc">Gantung iklan pemilik berkaitan</span>
                </button>
                <?php endif; ?>

                <button type="submit" name="action" value="deactivate_account"
                        class="btn btn-red complaint-action-btn"
                        onclick="return confirm('Gantung akaun pemilik? Mereka tidak akan dapat log masuk sehingga diaktifkan semula.')">
                    <span class="action-icon">&#9940;</span>
                    <span class="action-label">Nyahaktif Akaun</span>
                    <span class="action-desc">Gantung akaun pemilik</span>
                </button>

                <button type="submit" name="action" value="close_case"
                        class="btn btn-outline complaint-action-btn"
                        onclick="return confirm('Tutup kes ini? Tiada tindakan terhadap pemilik akan diambil.')">
                    <span class="action-icon">&#10003;</span>
                    <span class="action-label">Tutup Kes</span>
                    <span class="action-desc">Tidak disabitkan — tiada pelanggaran</span>
                </button>
            </div>
        </form>
    </div>

    <?php else: ?>
    <div class="alert alert-success">
        <strong>Aduan ini telah diselesaikan.</strong><br>
        Tindakan: <?= htmlspecialchars($complaint['action_taken'] ?? '—') ?><br>
        <?php if ($complaint['resolved_at']): ?>
        <small>Diselesaikan pada: <?= date('d M Y, H:i', strtotime($complaint['resolved_at'])) ?></small>
        <?php endif; ?>
    </div>
    <?php endif; ?>

<?php else: ?>
    <!-- List view -->
    <div class="page-header"><h1>Panel Aduan</h1></div>
    <?php if ($successFlash): ?><div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div><?php endif; ?>

    <div class="filter-tabs" id="complaintTabs">
        <button class="filter-tab active" data-filter="all">Semua</button>
        <button class="filter-tab" data-filter="open">Terbuka</button>
        <button class="filter-tab" data-filter="under_review">Dalam Semakan</button>
        <button class="filter-tab" data-filter="resolved">Selesai</button>
    </div>

    <div class="table-responsive">
    <table class="data-table" id="complaintTable">
        <thead>
            <tr>
                <th>Pengadu</th><th>Pemilik</th><th>Kategori</th>
                <th>Pembelaan</th><th>Status</th><th>Tarikh</th><th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($data['complaints'] as $c): ?>
        <tr data-status="<?= $c['status'] ?>">
            <td><?= htmlspecialchars($c['student_name']) ?></td>
            <td><?= htmlspecialchars($c['owner_name']) ?></td>
            <td><?= htmlspecialchars($c['category']) ?></td>
            <td>
                <?php if ($c['owner_defense']): ?>
                <span class="badge badge-confirmed" title="Pembelaan diterima">&#10003; Ada</span>
                <?php else: ?>
                <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
            <td>
                <span class="badge badge-<?= $c['status'] ?>">
                    <?= ucfirst(str_replace('_', ' ', $c['status'])) ?>
                </span>
            </td>
            <td><?= date('d M Y', strtotime($c['created_at'])) ?></td>
            <td>
                <a href="<?= BASE_URL ?>/admin/complaint_panel/<?= $c['complaint_id'] ?>"
                   class="btn btn-sm btn-primary">Urus</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <script>
    document.querySelectorAll('.filter-tab').forEach(btn => {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.filter-tab').forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            document.querySelectorAll('#complaintTable tbody tr').forEach(row => {
                row.style.display = (filter === 'all' || row.dataset.status === filter) ? '' : 'none';
            });
        });
    });
    </script>
<?php endif; ?>

</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
