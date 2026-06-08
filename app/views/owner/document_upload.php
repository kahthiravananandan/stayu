<?php
$pageTitle   = 'Muat Naik Dokumen Pengesahan';
require BASE_PATH . '/app/views/layouts/header.php';
$listing     = $data['listing'];
$existingDoc = $data['existingDoc'];
$error       = getFlash('error');
$isRejected  = $existingDoc && $existingDoc['status'] === 'rejected';
$isApproved  = $existingDoc && $existingDoc['status'] === 'approved';
$showForm    = !$existingDoc || $isRejected;
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container container--narrow">
    <div class="card">
        <div class="card-header">
            <h1>Muat Naik Dokumen Pengesahan</h1>
        </div>
        <div class="card-body">
            <div class="listing-preview mb-3">
                <strong><?= htmlspecialchars($listing['title']) ?></strong>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($isApproved): ?>
            <div class="alert alert-success">
                &#10003; Dokumen anda telah disahkan. Iklan ini kini aktif.
            </div>
            <div class="form-actions">
                <a href="<?= BASE_URL ?>/owner/listing_manage" class="btn btn-primary">Kembali ke Senarai Iklan</a>
            </div>

            <?php elseif ($isRejected): ?>
            <div class="alert alert-error">
                <strong>Dokumen ditolak.</strong><br>
                Sebab: <?= htmlspecialchars($existingDoc['rejection_reason'] ?? 'Tiada sebab dinyatakan.') ?>
                <br><small>Dimuat naik pada <?= date('d M Y', strtotime($existingDoc['submitted_at'])) ?></small>
            </div>
            <div class="alert alert-warning">
                <strong>Penting:</strong> Muat naik IC pemilik dan geran tanah/surat perjanjian sewa.
                Fail diterima: PDF, JPEG, PNG (maks. 5MB setiap satu).
                Dokumen ini <strong>tidak boleh diakses oleh umum</strong>.
            </div>
            <form method="POST"
                  action="<?= BASE_URL ?>/owner/document_upload/<?= $listing['listing_id'] ?>"
                  enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">
                <div class="form-group">
                    <label for="ic_doc">Salinan IC Pemilik (baharu) *</label>
                    <input type="file" id="ic_doc" name="ic_doc" accept=".pdf,image/jpeg,image/png" required>
                </div>
                <div class="form-group">
                    <label for="grant_doc">Geran Tanah / Surat Hak Milik (baharu) *</label>
                    <input type="file" id="grant_doc" name="grant_doc" accept=".pdf,image/jpeg,image/png" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Muat Naik Semula</button>
                    <a href="<?= BASE_URL ?>/owner/listing_manage" class="btn btn-ghost">Batal</a>
                </div>
            </form>

            <?php else: /* no existing doc — UC12 initial upload */ ?>
            <div class="alert alert-warning">
                <strong>Penting:</strong> Muat naik IC pemilik dan geran tanah/surat perjanjian sewa.
                Fail diterima: PDF, JPEG, PNG (maks. 5MB setiap satu).
                Dokumen ini <strong>tidak boleh diakses oleh umum</strong>.
            </div>
            <form method="POST"
                  action="<?= BASE_URL ?>/owner/document_upload/<?= $listing['listing_id'] ?>"
                  enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">
                <div class="form-group">
                    <label for="ic_doc">Salinan IC Pemilik *</label>
                    <input type="file" id="ic_doc" name="ic_doc" accept=".pdf,image/jpeg,image/png" required>
                </div>
                <div class="form-group">
                    <label for="grant_doc">Geran Tanah / Surat Hak Milik *</label>
                    <input type="file" id="grant_doc" name="grant_doc" accept=".pdf,image/jpeg,image/png" required>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Hantar Dokumen</button>
                    <a href="<?= BASE_URL ?>/owner/listing_manage" class="btn btn-ghost">Batal</a>
                </div>
            </form>
            <?php endif; ?>

        </div>
    </div>
</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
