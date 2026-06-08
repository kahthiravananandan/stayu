<?php $pageTitle = '403 — Akses Ditolak'; require BASE_PATH . '/app/views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="error-page">
    <div class="container">
        <h1>403</h1>
        <p>Anda tidak mempunyai kebenaran untuk mengakses halaman ini.</p>
        <a href="<?= BASE_URL ?>/student/search" class="btn btn-primary">Kembali ke Utama</a>
    </div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
