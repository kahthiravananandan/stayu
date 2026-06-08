<?php
$pageTitle = 'Log Masuk';
require BASE_PATH . '/app/views/layouts/header.php';
$error   = getFlash('error');
$success = getFlash('success');

// Forced-logout messages passed via ?reason= (timeout, suspended)
$reasonMessages = [
    'timeout'   => 'Sesi anda telah tamat tempoh disebabkan tidak aktif. Sila log masuk semula.',
    'suspended' => 'Akaun anda telah digantung. Hubungi Pusat Perumahan Pelajar (PPP).',
];
$expiredMsg = $reasonMessages[($_GET['reason'] ?? '')] ?? null;
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="auth-page">
    <div class="auth-card">
        <div class="auth-brand">
            <a href="<?= BASE_URL ?>/student/search" class="auth-logo">
                <span class="brand-stay">Stay</span><span class="brand-u">U</span>
            </a>
            <p>Selamat kembali! Log masuk untuk meneruskan.</p>
        </div>

        <?php if ($expiredMsg): ?>
        <div class="alert alert-error"><?= htmlspecialchars($expiredMsg) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/auth/login" class="auth-form" id="loginForm">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">

            <div class="form-group">
                <label>Log masuk sebagai</label>
                <div class="role-tabs">
                    <label class="role-tab">
                        <input type="radio" name="role" value="pelajar" required> Pelajar
                    </label>
                    <label class="role-tab">
                        <input type="radio" name="role" value="pemilik"> Pemilik
                    </label>
                    <label class="role-tab">
                        <input type="radio" name="role" value="admin"> Admin
                    </label>
                </div>
            </div>

            <div class="form-group">
                <label for="identifier" id="identifierLabel">Nombor Matrik / IC / E-mel</label>
                <input type="text" id="identifier" name="identifier"
                       placeholder="Pilih peranan dahulu" required autocomplete="username">
                <small id="identifierHint" class="text-muted"></small>
            </div>

            <div class="form-group">
                <label for="password">Kata Laluan</label>
                <div class="input-group">
                    <input type="password" id="password" name="password"
                           placeholder="Kata laluan" required autocomplete="current-password">
                    <button type="button" class="toggle-password" aria-label="Tunjuk/Sembunyikan kata laluan">
                        <svg viewBox="0 0 24 24" width="18" height="18" stroke="currentColor" fill="none">
                            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Log Masuk</button>
        </form>

        <p class="auth-footer">
            Belum ada akaun? <a href="<?= BASE_URL ?>/auth/register">Daftar di sini</a>
        </p>
    </div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
