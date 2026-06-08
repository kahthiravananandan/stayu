<?php
$pageTitle = 'Balas Aduan';
require BASE_PATH . '/app/views/layouts/header.php';
$complaint = $data['complaint'];
$error     = getFlash('error');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container container--narrow">
    <div class="card">
        <div class="card-header">
            <h1>Balas Aduan</h1>
            <span class="badge badge-<?= $complaint['status'] ?>"><?= ucfirst(str_replace('_',' ',$complaint['status'])) ?></span>
        </div>
        <div class="card-body">
            <!-- Complaint details -->
            <div class="complaint-detail">
                <div class="complaint-row">
                    <span class="label">Pengadu:</span>
                    <span><?= htmlspecialchars($complaint['student_name']) ?> (<?= htmlspecialchars($complaint['matric_number']) ?>)</span>
                </div>
                <div class="complaint-row">
                    <span class="label">Kategori:</span>
                    <span><?= htmlspecialchars($complaint['category']) ?></span>
                </div>
                <?php if ($complaint['listing_title']): ?>
                <div class="complaint-row">
                    <span class="label">Iklan:</span>
                    <span><?= htmlspecialchars($complaint['listing_title']) ?></span>
                </div>
                <?php endif; ?>
                <div class="complaint-row">
                    <span class="label">Penerangan:</span>
                    <p><?= nl2br(htmlspecialchars($complaint['description'])) ?></p>
                </div>
                <div class="complaint-row">
                    <span class="label">Tarikh:</span>
                    <span><?= date('d M Y H:i', strtotime($complaint['created_at'])) ?></span>
                </div>
            </div>

            <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <?php if (!$complaint['owner_defense']): ?>
            <form method="POST"
                  action="<?= BASE_URL ?>/owner/complaint_response/<?= $complaint['complaint_id'] ?>"
                  enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">

                <div class="form-group">
                    <label for="defense">Pembelaan Anda *</label>
                    <textarea id="defense" name="defense" rows="6"
                              placeholder="Huraikan pendirian anda terhadap aduan ini (min. 20 aksara)..."
                              required minlength="20"></textarea>
                </div>
                <div class="form-group">
                    <label for="evidence">Bukti Sokongan (pilihan — PDF/JPEG/PNG, maks. 5MB)</label>
                    <input type="file" id="evidence" name="evidence" accept=".pdf,image/jpeg,image/png">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Hantar Pembelaan</button>
                    <a href="<?= BASE_URL ?>/owner/dashboard" class="btn btn-ghost">Batal</a>
                </div>
            </form>
            <?php else: ?>
            <div class="alert alert-info">
                <strong>Pembelaan anda telah dihantar:</strong>
                <p><?= nl2br(htmlspecialchars($complaint['owner_defense'])) ?></p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
