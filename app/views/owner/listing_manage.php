<?php
$pageTitle = 'Urus Iklan';
require BASE_PATH . '/app/views/layouts/header.php';
$successFlash = getFlash('success');
$errorFlash   = getFlash('error');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="dashboard-page">
<div class="container">
    <div class="page-header">
        <h1>Urus Iklan</h1>
        <a href="<?= BASE_URL ?>/owner/listing_form" class="btn btn-primary">+ Tambah Iklan</a>
    </div>

    <?php if ($successFlash): ?><div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div><?php endif; ?>
    <?php if ($errorFlash):   ?><div class="alert alert-error"><?= htmlspecialchars($errorFlash) ?></div><?php endif; ?>

    <?php if (empty($listings)): ?>
    <div class="empty-results">
        <p>Tiada iklan lagi. <a href="<?= BASE_URL ?>/owner/listing_form">Cipta iklan pertama anda.</a></p>
    </div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Foto</th><th>Tajuk</th><th>Sewa</th><th>Jenis</th>
                <th>Status</th><th>Ubah Status</th><th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($listings as $l): ?>
        <tr>
            <td>
                <?php if ($l['cover_photo']): ?>
                <img src="<?= BASE_URL ?>/public/uploads/photos/<?= htmlspecialchars($l['cover_photo']) ?>"
                     alt="" class="table-thumb">
                <?php else: ?>
                <div class="table-thumb table-thumb--empty">&#127968;</div>
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($l['title']) ?></td>
            <td>RM <?= number_format($l['monthly_rent'], 0) ?></td>
            <td><?= match($l['property_type']) { 'room'=>'Bilik','whole_unit'=>'Unit Penuh','shared_room'=>'Kongsi',default=>$l['property_type'] } ?></td>
            <td><span class="badge badge-<?= $l['status'] ?>"><?= ucfirst(str_replace('_', ' ', $l['status'])) ?></span></td>
            <td>
                <?php if (in_array($l['status'], ['active','in_negotiation','unavailable'])): ?>
                <form method="POST" action="<?= BASE_URL ?>/owner/listing_status/<?= $l['listing_id'] ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <select name="status" onchange="this.form.submit()" class="select-sm">
                        <option value="active"         <?= $l['status']==='active'         ? 'selected':'' ?>>Aktif</option>
                        <option value="in_negotiation" <?= $l['status']==='in_negotiation' ? 'selected':'' ?>>Dalam Rundingan</option>
                        <option value="unavailable"    <?= $l['status']==='unavailable'    ? 'selected':'' ?>>Tidak Tersedia</option>
                    </select>
                </form>
                <?php else: ?>
                <span class="text-muted">—</span>
                <?php endif; ?>
            </td>
            <td>
                <a href="<?= BASE_URL ?>/owner/listing_form/<?= $l['listing_id'] ?>" class="btn btn-sm btn-outline">Edit</a>
                <?php if ($l['status'] === 'in_review'): ?>
                <a href="<?= BASE_URL ?>/owner/document_upload/<?= $l['listing_id'] ?>" class="btn btn-sm btn-orange">Muat Naik Dok.</a>
                <?php endif; ?>
                <form method="POST" action="<?= BASE_URL ?>/owner/listing_delete/<?= $l['listing_id'] ?>"
                      style="display:inline" onsubmit="return confirm('Padam iklan ini?')">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <button class="btn btn-sm btn-red">Padam</button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
