<?php
$pageTitle = 'Hantar Aduan';
require BASE_PATH . '/app/views/layouts/header.php';
$listing = $data['listing'] ?? null;
$error   = getFlash('error');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container container--narrow">
    <div class="card">
        <div class="card-header"><h1>Hantar Aduan</h1></div>
        <div class="card-body">
            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST"
                  action="<?= BASE_URL ?>/student/complaint_form<?= $listing ? '/' . $listing['listing_id'] : '' ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">
                <?php if ($listing): ?>
                <input type="hidden" name="listing_id"  value="<?= $listing['listing_id'] ?>">
                <input type="hidden" name="owner_id"    value="<?= $listing['owner_id'] ?>">
                <?php endif; ?>

                <?php if (!$listing): ?>
                <div class="form-group">
                    <label for="owner_id">ID Pemilik</label>
                    <input type="number" id="owner_id" name="owner_id" placeholder="Masukkan ID pemilik" required>
                    <small class="form-hint">Anda boleh dapatkan ID pemilik daripada halaman iklan.</small>
                </div>
                <?php else: ?>
                <div class="listing-preview">
                    <strong><?= htmlspecialchars($listing['title']) ?></strong>
                    <span class="text-muted">Pemilik: <?= htmlspecialchars($listing['owner_name']) ?></span>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="category">Kategori Aduan</label>
                    <select id="category" name="category" required>
                        <option value="">-- Pilih kategori --</option>
                        <option value="Iklan Palsu">Iklan Palsu</option>
                        <option value="Maklumat Mengelirukan">Maklumat Mengelirukan</option>
                        <option value="Pemilik Tidak Bertindak Balas">Pemilik Tidak Bertindak Balas</option>
                        <option value="Masalah Sewaktu Tontonan">Masalah Sewaktu Tontonan</option>
                        <option value="Lain-lain">Lain-lain</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="description">
                        Penerangan Aduan
                        <span id="charCounter" style="float:right;font-weight:400;font-size:.85rem;color:#6b7280">0 / 20 min</span>
                    </label>
                    <textarea id="description" name="description" rows="5"
                              placeholder="Huraikan masalah anda dengan terperinci (min. 20 aksara)..."
                              required minlength="20"></textarea>
                    <small id="charHint" class="text-muted" style="display:none;color:#dc2626">Penerangan mesti sekurang-kurangnya 20 aksara.</small>
                </div>

                <div class="alert alert-info">
                    Aduan anda akan disemak oleh admin PPP UKM dalam masa 3 hari bekerja.
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Hantar Aduan</button>
                    <a href="<?= BASE_URL ?>/student/dashboard" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
</main>
<script>
(function () {
    var textarea = document.getElementById('description');
    var counter  = document.getElementById('charCounter');
    var hint     = document.getElementById('charHint');
    var MIN      = 20;

    if (!textarea || !counter) return;

    function update() {
        var len = textarea.value.length;
        counter.textContent = len + ' / ' + MIN + ' min';
        counter.style.color = len >= MIN ? '#16a34a' : '#6b7280';
        if (hint) hint.style.display = (len > 0 && len < MIN) ? '' : 'none';
    }

    textarea.addEventListener('input', update);
    update();
})();
</script>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
