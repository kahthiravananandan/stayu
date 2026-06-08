<?php
$pageTitle = 'Semakan Dokumen';
require BASE_PATH . '/app/views/layouts/header.php';
$successFlash = getFlash('success');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="dashboard-page">
<div class="container">

<?php if (isset($doc)): ?>
    <!-- Single document review -->
    <nav class="breadcrumb">
        <a href="<?= BASE_URL ?>/admin/document_review">Semakan Dokumen</a> &rsaquo;
        <?= htmlspecialchars($doc['listing_title']) ?>
    </nav>

    <?php if ($successFlash): ?><div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div><?php endif; ?>

    <div class="doc-review-grid">
        <div class="card">
            <div class="card-header"><h2>Butiran Iklan</h2></div>
            <div class="card-body">
                <div class="detail-row"><span class="label">Tajuk:</span> <?= htmlspecialchars($doc['listing_title']) ?></div>
                <div class="detail-row"><span class="label">Pemilik:</span> <?= htmlspecialchars($doc['owner_name']) ?></div>
                <div class="detail-row"><span class="label">Dihantar:</span> <?= date('d M Y H:i', strtotime($doc['submitted_at'])) ?></div>
                <div class="detail-row"><span class="label">Status:</span>
                    <span class="badge badge-<?= $doc['status'] ?>"><?= ucfirst($doc['status']) ?></span>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h2>Dokumen Dimuatkan Naik</h2></div>
            <div class="card-body">
                <div class="doc-links">
                    <div class="doc-link-item">
                        <span>&#127970; IC Pemilik</span>
                        <a href="<?= BASE_URL ?>/admin/serve_doc/<?= $doc['document_id'] ?>?type=ic"
                           target="_blank" class="btn btn-sm btn-outline">Lihat / Muat Turun</a>
                    </div>
                    <div class="doc-link-item">
                        <span>&#128196; Geran Tanah</span>
                        <a href="<?= BASE_URL ?>/admin/serve_doc/<?= $doc['document_id'] ?>?type=grant"
                           target="_blank" class="btn btn-sm btn-outline">Lihat / Muat Turun</a>
                    </div>
                </div>
                <div class="alert alert-warning">
                    <strong>Nota Keselamatan:</strong> Dokumen ini sulit. Jangan berkongsi pautan ini.
                </div>
            </div>
        </div>
    </div>

    <?php if ($doc['status'] === 'pending'): ?>
    <div class="doc-decision-panel card card-body">
        <h2>Keputusan Semakan</h2>
        <form method="POST"
              action="<?= BASE_URL ?>/admin/document_review/<?= $doc['document_id'] ?>">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">
            <div class="decision-btns">
                <button type="submit" name="action" value="approve" class="btn btn-green btn-lg"
                        onclick="return confirm('Luluskan dokumen dan aktifkan iklan ini?')">
                    &#10003; Lulus &amp; Aktifkan Iklan
                </button>
                <div class="reject-group">
                    <input type="text" name="rejection_reason" placeholder="Sebab penolakan (wajib jika tolak)"
                           class="reject-reason-input">
                    <button type="submit" name="action" value="reject" class="btn btn-red btn-lg"
                            onclick="return validateReject()">&#10007; Tolak</button>
                </div>
            </div>
        </form>
    </div>
    <script>
    function validateReject() {
        const r = document.querySelector('input[name="rejection_reason"]').value.trim();
        if (!r) { alert('Sila masukkan sebab penolakan.'); return false; }
        return confirm('Tolak dokumen ini?');
    }
    </script>
    <?php else: ?>
    <div class="alert alert-info">Dokumen ini sudah <?= $doc['status'] === 'approved' ? 'diluluskan' : 'ditolak' ?>.</div>
    <?php endif; ?>

<?php else: ?>
    <!-- List view -->
    <div class="page-header">
        <h1>Semakan Dokumen Pengesahan</h1>
    </div>
    <?php if ($successFlash): ?><div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div><?php endif; ?>

    <?php if (empty($pendingDocs)): ?>
    <div class="empty-results"><p>Tiada dokumen menunggu semakan. Semua selesai!</p></div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr><th>Iklan</th><th>Pemilik</th><th>Dihantar</th><th>Status</th><th>Tindakan</th></tr>
        </thead>
        <tbody>
        <?php foreach ($pendingDocs as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['listing_title']) ?></td>
            <td><?= htmlspecialchars($d['owner_name']) ?></td>
            <td><?= date('d M Y', strtotime($d['submitted_at'])) ?></td>
            <td><span class="badge badge-pending">Menunggu</span></td>
            <td>
                <a href="<?= BASE_URL ?>/admin/document_review/<?= $d['document_id'] ?>" class="btn btn-sm btn-primary">Semak</a>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
<?php endif; ?>

</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
