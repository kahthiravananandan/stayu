<?php
$pageTitle = 'Daftar Akaun';
require BASE_PATH . '/app/views/layouts/header.php';
$error = getFlash('error');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="auth-page">
    <div class="auth-card auth-card--wide">
        <div class="auth-brand">
            <a href="<?= BASE_URL ?>/student/search" class="auth-logo">
                <span class="brand-stay">Stay</span><span class="brand-u">U</span>
            </a>
            <p>Daftar akaun baharu.</p>
        </div>

        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="role-tabs reg-tabs">
            <button class="role-tab active" data-target="form-pelajar" type="button">Pelajar</button>
            <button class="role-tab" data-target="form-pemilik" type="button">Pemilik</button>
        </div>

        <!-- Student Registration -->
        <form method="POST" action="<?= BASE_URL ?>/auth/register" class="auth-form" id="form-pelajar">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">
            <input type="hidden" name="role" value="pelajar">

            <div class="form-row">
                <div class="form-group">
                    <label for="s_full_name">Nama Penuh</label>
                    <input type="text" id="s_full_name" name="full_name" placeholder="Seperti dalam IC" required>
                </div>
                <div class="form-group">
                    <label for="s_matric">Nombor Matrik</label>
                    <input type="text" id="s_matric" name="matric_number" placeholder="cth: A202584" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="s_phone">Nombor Telefon</label>
                    <input type="tel" id="s_phone" name="phone_number" placeholder="0123456789" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="s_pass">Kata Laluan</label>
                    <input type="password" id="s_pass" name="password" placeholder="Min. 8 aksara" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="s_pass2">Sahkan Kata Laluan</label>
                    <input type="password" id="s_pass2" name="password_confirm" placeholder="Ulang kata laluan" required>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block">Daftar sebagai Pelajar</button>
        </form>

        <!-- Owner Registration -->
        <form method="POST" action="<?= BASE_URL ?>/auth/register" class="auth-form hidden" id="form-pemilik">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">
            <input type="hidden" name="role" value="pemilik">

            <div class="form-row">
                <div class="form-group">
                    <label for="o_full_name">Nama Penuh</label>
                    <input type="text" id="o_full_name" name="full_name" placeholder="Seperti dalam IC" required>
                </div>
                <div class="form-group">
                    <label for="o_ic">Nombor IC</label>
                    <input type="text" id="o_ic" name="ic_number" placeholder="cth: 900101145555" required maxlength="14">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="o_phone">Nombor Telefon</label>
                    <input type="tel" id="o_phone" name="phone_number" placeholder="0123456789" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="o_pass">Kata Laluan</label>
                    <input type="password" id="o_pass" name="password" placeholder="Min. 8 aksara" required minlength="8">
                </div>
                <div class="form-group">
                    <label for="o_pass2">Sahkan Kata Laluan</label>
                    <input type="password" id="o_pass2" name="password_confirm" placeholder="Ulang kata laluan" required>
                </div>
            </div>
            <div class="alert alert-info">
                Pemilik individu perlu memuat naik dokumen pengesahan (IC & geran tanah) selepas mendaftar iklan.
            </div>
            <button type="submit" class="btn btn-primary btn-block">Daftar sebagai Pemilik</button>
        </form>

        <p class="auth-footer">Sudah ada akaun? <a href="<?= BASE_URL ?>/auth/login">Log masuk</a></p>
    </div>
</main>
<script>
document.querySelectorAll('.reg-tabs .role-tab').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.reg-tabs .role-tab').forEach(b => b.classList.remove('active'));
        this.classList.add('active');
        document.querySelectorAll('.auth-form').forEach(f => f.classList.add('hidden'));
        document.getElementById(this.dataset.target).classList.remove('hidden');
    });
});
</script>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
