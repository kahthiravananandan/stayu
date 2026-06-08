<?php
$pageTitle = 'Profil Saya';
require BASE_PATH . '/app/views/layouts/header.php';
$u       = $data['user'];
$success = getFlash('success');
$error   = getFlash('error');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container container--narrow">
    <div class="card">
        <div class="card-header">
            <h1>Profil Saya</h1>
        </div>
        <div class="card-body">

            <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
            <?php if ($error):   ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

            <!-- Profile photo preview -->
            <div class="profile-photo-wrap">
                <?php if (!empty($u['profile_photo'])): ?>
                <img id="photoPreview"
                     src="<?= BASE_URL ?>/public/uploads/photos/<?= htmlspecialchars($u['profile_photo']) ?>"
                     alt="Gambar profil" class="profile-photo-img">
                <?php else: ?>
                <div class="profile-photo-initial" id="photoInitial">
                    <?= strtoupper(substr($u['full_name'], 0, 1)) ?>
                </div>
                <img id="photoPreview" src="" alt="" class="profile-photo-img hidden">
                <?php endif; ?>
            </div>

            <form method="POST" action="<?= BASE_URL ?>/owner/profile" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">

                <div class="form-group">
                    <label>Nombor IC</label>
                    <input type="text" value="<?= htmlspecialchars($u['ic_number'] ?? '') ?>"
                           disabled class="input-disabled">
                    <small class="text-muted">Nombor IC tidak boleh diubah.</small>
                </div>

                <div class="form-group">
                    <label for="full_name">Nama Penuh</label>
                    <input type="text" id="full_name" name="full_name"
                           value="<?= htmlspecialchars($u['full_name']) ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone_number">Nombor Telefon</label>
                    <input type="tel" id="phone_number" name="phone_number"
                           value="<?= htmlspecialchars($u['phone_number'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Gambar Profil</label>
                    <label for="profile_photo" class="btn btn-outline btn-sm" style="cursor:pointer;display:inline-block">
                        Pilih Gambar
                    </label>
                    <input type="file" id="profile_photo" name="profile_photo"
                           accept="image/jpeg,image/png,image/webp" class="hidden">
                    <small class="text-muted d-block mt-1">JPEG, PNG, WebP — maks. 2MB</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="<?= BASE_URL ?>/owner/dashboard" class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
</main>
<script>
document.getElementById('profile_photo').addEventListener('change', function () {
    const file = this.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (e) {
        const img = document.getElementById('photoPreview');
        img.src = e.target.result;
        img.classList.remove('hidden');
        const init = document.getElementById('photoInitial');
        if (init) init.classList.add('hidden');
    };
    reader.readAsDataURL(file);
});
</script>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
