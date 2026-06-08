<?php $pageTitle = '404 — Halaman Tidak Dijumpai'; require BASE_PATH . '/app/views/layouts/header.php'; ?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="error-page">
    <div class="container">
        <h1>404</h1>
        <p>Halaman yang anda cari tidak wujud.</p>
        <a href="<?= BASE_URL ?>/student/search" class="btn btn-primary">Kembali ke Utama</a>
    </div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
