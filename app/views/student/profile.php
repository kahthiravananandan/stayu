<?php
$pageTitle = 'Profil Saya';
require BASE_PATH . '/app/views/layouts/header.php';
$user    = $data['user'];
$success = getFlash('success');
$error   = getFlash('error');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container container--narrow">
    <div class="page-header">
        <h1>Profil Saya</h1>
        <p class="text-muted">Kemaskini maklumat peribadi anda.</p>
    </div>

    <?php if ($success): ?>
    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
    <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= BASE_URL ?>/student/profile"
          enctype="multipart/form-data" class="card card-body">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">

        <!-- Profile photo -->
        <div class="form-group profile-photo-group">
            <?php if (!empty($user['profile_photo'])): ?>
            <img src="<?= BASE_URL ?>/public/uploads/photos/<?= htmlspecialchars($user['profile_photo']) ?>"
                 alt="Gambar profil" class="profile-avatar" id="profilePreview">
            <?php else: ?>
            <div class="profile-avatar-placeholder" id="profilePreview">
                <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
            </div>
            <?php endif; ?>
            <div>
                <label for="profile_photo" class="btn btn-outline btn-sm" style="cursor:pointer">
                    Tukar Gambar Profil
                </label>
                <input type="file" id="profile_photo" name="profile_photo"
                       accept="image/jpeg,image/png,image/webp" style="display:none">
                <small class="text-muted" style="display:block;margin-top:4px">
                    JPEG, PNG atau WebP. Maks. 2 MB.
                </small>
            </div>
        </div>

        <!-- Matric (read-only) -->
        <div class="form-group">
            <label>Nombor Matrik</label>
            <input type="text" value="<?= htmlspecialchars($user['matric_number'] ?? '') ?>"
                   disabled style="background:#f5f5f5;cursor:not-allowed">
            <small class="text-muted">Nombor matrik tidak boleh ditukar.</small>
        </div>

        <!-- Editable fields -->
        <div class="form-group">
            <label for="full_name">Nama Penuh *</label>
            <input type="text" id="full_name" name="full_name" required
                   value="<?= htmlspecialchars($user['full_name']) ?>"
                   placeholder="Nama penuh seperti dalam IC">
        </div>

        <div class="form-group">
            <label for="phone_number">Nombor Telefon *</label>
            <input type="tel" id="phone_number" name="phone_number" required
                   value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>"
                   placeholder="0123456789">
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= BASE_URL ?>/student/dashboard" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
</main>
<script>
document.getElementById('profile_photo').addEventListener('change', function () {
    if (!this.files[0]) return;
    var reader = new FileReader();
    reader.onload = function (e) {
        var prev = document.getElementById('profilePreview');
        if (prev.tagName === 'IMG') {
            prev.src = e.target.result;
        } else {
            var img = document.createElement('img');
            img.src = e.target.result;
            img.className = 'profile-avatar';
            img.id = 'profilePreview';
            prev.parentNode.replaceChild(img, prev);
        }
    };
    reader.readAsDataURL(this.files[0]);
});
</script>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
