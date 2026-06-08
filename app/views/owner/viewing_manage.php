<?php
$pageTitle = 'Urus Permintaan Tontonan';
require BASE_PATH . '/app/views/layouts/header.php';
$successFlash = getFlash('success');
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="dashboard-page">
<div class="container">
    <div class="page-header"><h1>Permintaan Tontonan</h1></div>

    <?php if ($successFlash): ?><div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div><?php endif; ?>

    <?php if (empty($viewings)): ?>
    <div class="empty-results"><p>Tiada permintaan tontonan.</p></div>
    <?php else: ?>
    <div class="table-responsive">
    <table class="data-table">
        <thead>
            <tr>
                <th>Pelajar</th><th>Matrik</th><th>Iklan</th>
                <th>Tarikh</th><th>Masa</th><th>Status</th><th>Tindakan</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($viewings as $v): ?>
        <tr>
            <td><?= htmlspecialchars($v['student_name']) ?></td>
            <td><?= htmlspecialchars($v['matric_number']) ?></td>
            <td><?= htmlspecialchars($v['listing_title']) ?></td>
            <td><?= date('d M Y', strtotime($v['proposed_date'])) ?></td>
            <td><?= date('H:i', strtotime($v['proposed_time'])) ?></td>
            <td><span class="badge badge-<?= $v['status'] ?>"><?= ucfirst($v['status']) ?></span></td>
            <td>
                <?php if ($v['status'] === 'pending'): ?>
                <form method="POST" action="<?= BASE_URL ?>/owner/viewing_action/<?= $v['request_id'] ?>" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="confirmed">
                    <button class="btn btn-sm btn-green">Sahkan</button>
                </form>
                <form method="POST" action="<?= BASE_URL ?>/owner/viewing_action/<?= $v['request_id'] ?>" style="display:inline">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="rejected">
                    <button class="btn btn-sm btn-red">Tolak</button>
                </form>
                <?php else: ?>
                <span class="text-muted">—</span>
                <?php endif; ?>
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
