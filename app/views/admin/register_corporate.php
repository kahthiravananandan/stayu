<?php
$pageTitle    = 'Daftar Pemilik Korporat';
require BASE_PATH . '/app/views/layouts/header.php';
$successFlash = getFlash('success');
$errorFlash   = getFlash('error');
$tempPass     = $data['tempPass'] ?? null;
$tempName     = $data['tempName'] ?? null;
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container">
    <div class="page-header">
        <h1>Daftar Pemilik Korporat</h1>
        <p class="text-muted">Hanya admin PPP boleh mendaftarkan syarikat korporat seperti UKM Real Estate Sdn. Bhd.</p>
    </div>

    <?php if ($errorFlash): ?>
    <div class="alert alert-error"><?= htmlspecialchars($errorFlash) ?></div>
    <?php endif; ?>

    <?php if ($successFlash && $tempPass): ?>
    <div class="alert alert-warning temp-password-box">
        <strong>&#9888; Akaun Berjaya Didaftarkan — Simpan Kata Laluan Sementara</strong>
        <p>Akaun untuk <strong><?= htmlspecialchars($tempName ?? 'pemilik korporat') ?></strong> telah dicipta.</p>
        <p>Kata laluan sementara (tunjukkan kepada pemilik, tidak boleh dilihat semula):</p>
        <div class="temp-pass-display">
            <code id="tempPassCode"><?= htmlspecialchars($tempPass) ?></code>
            <button type="button" class="btn btn-sm btn-outline"
                    onclick="navigator.clipboard.writeText('<?= htmlspecialchars($tempPass) ?>').then(() => this.textContent = 'Disalin!')">
                Salin
            </button>
        </div>
        <small class="text-muted mt-1 d-block">
            Pemilik hendaklah menukar kata laluan ini selepas log masuk pertama.
        </small>
    </div>
    <?php elseif ($successFlash): ?>
    <div class="alert alert-success"><?= htmlspecialchars($successFlash) ?></div>
    <?php endif; ?>

    <div class="split-layout">
        <!-- Registration form -->
        <div class="card card-body">
            <h2>Tambah Akaun Korporat Baharu</h2>
            <form method="POST" action="<?= BASE_URL ?>/admin/register_corporate">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">

                <div class="form-group">
                    <label for="full_name">Nama Syarikat *</label>
                    <input type="text" id="full_name" name="full_name"
                           placeholder="cth: UKM Real Estate Sdn. Bhd." required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="ic_number">Nombor Pendaftaran / IC *</label>
                        <input type="text" id="ic_number" name="ic_number"
                               placeholder="12 digit" required maxlength="14">
                    </div>
                    <div class="form-group">
                        <label for="phone_number">Nombor Telefon *</label>
                        <input type="tel" id="phone_number" name="phone_number"
                               placeholder="0389215000" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="email">E-mel *</label>
                    <input type="email" id="email" name="email"
                           placeholder="realestate@ukm.my" required>
                </div>

                <div class="alert alert-info">
                    Sistem akan menjana kata laluan sementara secara automatik dan dipaparkan selepas pendaftaran.
                    Iklan daripada akaun korporat akan <strong>aktif serta-merta</strong> dengan lencana
                    <strong>Verified — UKM Real Estate</strong>.
                </div>
                <button type="submit" class="btn btn-primary btn-block">Daftar Akaun Korporat</button>
            </form>
        </div>

        <!-- Existing corporates list -->
        <div class="card">
            <div class="card-header"><h2>Senarai Pemilik Korporat</h2></div>
            <?php if (empty($data['corporates'])): ?>
            <p class="empty-state">Tiada pemilik korporat berdaftar.</p>
            <?php else: ?>
            <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr><th>Nama</th><th>E-mel</th><th>Telefon</th><th>Status</th></tr>
                </thead>
                <tbody>
                <?php foreach ($data['corporates'] as $c): ?>
                <tr>
                    <td><?= htmlspecialchars($c['full_name']) ?></td>
                    <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['phone_number'] ?? '—') ?></td>
                    <td>
                        <span class="badge badge-<?= $c['status'] ?>"><?= ucfirst($c['status']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
