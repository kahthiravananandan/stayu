<?php
$pageTitle = 'Minta Tontonan';
require BASE_PATH . '/app/views/layouts/header.php';
$listing = $data['listing'];
$error   = getFlash('error');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container container--narrow">
    <nav class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/listing/<?= $listing['listing_id'] ?>">
            <?= htmlspecialchars($listing['title']) ?></a> &rsaquo; Minta Tontonan
    </nav>

    <div class="card">
        <div class="card-header"><h1>Permintaan Tontonan</h1></div>
        <div class="card-body">
            <div class="listing-preview">
                <strong><?= htmlspecialchars($listing['title']) ?></strong>
                <span class="text-muted">&#128205; <?= htmlspecialchars($listing['address']) ?></span>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" action="<?= BASE_URL ?>/student/viewing_request/<?= $listing['listing_id'] ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="proposed_date">Tarikh Cadangan</label>
                        <input type="date" id="proposed_date" name="proposed_date" required
                               min="<?= date('Y-m-d', strtotime('+1 day')) ?>">
                    </div>
                    <div class="form-group">
                        <label for="proposed_time">Masa Cadangan</label>
                        <input type="time" id="proposed_time" name="proposed_time" required
                               min="08:00" max="20:00">
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>Nota:</strong> Pemilik akan mengesahkan atau menolak permintaan anda.
                    Anda akan dimaklumkan melalui notifikasi.
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Hantar Permintaan</button>
                    <a href="<?= BASE_URL ?>/student/listing/<?= $listing['listing_id'] ?>"
                       class="btn btn-ghost">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
