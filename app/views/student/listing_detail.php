<?php
$pageTitle = htmlspecialchars($listing['title'] ?? 'Butiran Iklan');
require BASE_PATH . '/app/views/layouts/header.php';
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="detail-page">
<div class="container">
    <nav class="breadcrumb">
        <a href="<?= BASE_URL ?>/student/search">Cari</a> &rsaquo;
        <span><?= htmlspecialchars($listing['title']) ?></span>
    </nav>

    <div class="detail-grid">
        <!-- Photos -->
        <div class="detail-photos">
            <?php $photos = $listing['photos']; ?>
            <?php if (!empty($photos)): ?>
            <div class="photo-gallery" id="gallery">
                <img src="<?= BASE_URL ?>/public/uploads/photos/<?= htmlspecialchars($photos[0]['photo_path']) ?>"
                     id="mainPhoto" alt="Foto utama" class="main-photo">
                <?php if (count($photos) > 1): ?>
                <div class="photo-thumbs">
                <?php foreach ($photos as $i => $ph): ?>
                <img src="<?= BASE_URL ?>/public/uploads/photos/<?= htmlspecialchars($ph['photo_path']) ?>"
                     alt="Foto <?= $i+1 ?>" class="thumb <?= $i === 0 ? 'active' : '' ?>"
                     onclick="setMain(this)">
                <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="photo-placeholder">&#127968; Tiada Foto</div>
            <?php endif; ?>
        </div>

        <!-- Info -->
        <div class="detail-info">
            <div class="detail-badges">
                <?php if ($listing['owner_type'] === 'korporat'): ?>
                <span class="badge-verified">&#10003; Disahkan UKM Real Estate</span>
                <?php else: ?>
                <span class="badge-individu">&#10003; Pemilik Individu Disahkan PPP</span>
                <?php endif; ?>
                <span class="badge-type"><?= match($listing['property_type']) {
                    'room' => 'Bilik', 'whole_unit' => 'Unit Penuh', 'shared_room' => 'Bilik Kongsi', default => $listing['property_type']
                } ?></span>
            </div>

            <h1 class="detail-title"><?= htmlspecialchars($listing['title']) ?></h1>
            <p class="detail-address">&#128205; <?= htmlspecialchars($listing['address']) ?></p>

            <p id="distanceResult" class="detail-dist">
                <?php if ($listing['distance_km']): ?>
                &#128337; <?= number_format($listing['distance_km'], 1) ?> km dari UKM Bangi
                <?php elseif ($listing['latitude'] && $listing['longitude']): ?>
                &#128337; Mengira jarak...
                <?php endif; ?>
            </p>

            <div class="detail-price-row">
                <span class="detail-price">RM <?= number_format($listing['monthly_rent'], 0) ?><small>/bulan</small></span>
                <?php if ($listing['deposit']): ?>
                <span class="detail-deposit">Deposit: RM <?= number_format($listing['deposit'], 0) ?></span>
                <?php endif; ?>
            </div>

            <div class="detail-meta">
                <div class="meta-item">
                    <span class="meta-label">Jantina</span>
                    <span><?= match($listing['gender_pref']) { 'male' => 'Lelaki', 'female' => 'Perempuan', default => 'Semua' } ?></span>
                </div>
                <div class="meta-item">
                    <span class="meta-label">Pemilik</span>
                    <span><?= htmlspecialchars($listing['owner_name']) ?></span>
                </div>
            </div>

            <?php if (!empty($listing['amenities'])): ?>
            <div class="detail-amenities">
                <h3>Kemudahan</h3>
                <ul class="amenity-list">
                <?php foreach ($listing['amenities'] as $am): ?>
                <li class="amenity-tag">&#10003; <?= htmlspecialchars($am['amenity_name']) ?></li>
                <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <?php if (!empty($listing['description'])): ?>
            <div class="detail-desc">
                <h3>Penerangan</h3>
                <p><?= nl2br(htmlspecialchars($listing['description'])) ?></p>
            </div>
            <?php endif; ?>

            <!-- Actions -->
            <div class="detail-actions">
                <?php if (isLoggedIn() && getSessionRole() === 'pelajar'): ?>
                    <?php if ($alreadyRequested): ?>
                    <button class="btn btn-outline" disabled>Tontonan Telah Dimohon</button>
                    <?php else: ?>
                    <a href="<?= BASE_URL ?>/student/viewing_request/<?= $listing['listing_id'] ?>"
                       class="btn btn-primary">&#128197; Minta Tontonan</a>
                    <?php endif; ?>
                    <?php if ($conversation): ?>
                    <a href="<?= BASE_URL ?>/student/chat/<?= $conversation['conversation_id'] ?>"
                       class="btn btn-outline">&#128172; Hubungi Pemilik</a>
                    <?php endif; ?>
                    <a href="<?= BASE_URL ?>/student/complaint_form/<?= $listing['listing_id'] ?>"
                       class="btn btn-ghost btn-sm">Laporkan Masalah</a>
                <?php elseif (!isLoggedIn()): ?>
                    <a href="<?= BASE_URL ?>/auth/login" class="btn btn-primary">Log Masuk untuk Hubungi</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Map -->
    <?php if ($listing['latitude'] && $listing['longitude']): ?>
    <section class="detail-map-section">
        <h2>Lokasi</h2>
        <div id="detailMap" class="detail-map"
             data-lat="<?= htmlspecialchars($listing['latitude']) ?>"
             data-lng="<?= htmlspecialchars($listing['longitude']) ?>"
             data-title="<?= htmlspecialchars($listing['title']) ?>"
             data-km="<?= (float)($listing['distance_km'] ?? 0) ?>"
             data-apikey="<?= htmlspecialchars(GOOGLE_MAPS_API_KEY) ?>"
             data-mapid="<?= htmlspecialchars(GOOGLE_MAPS_ID) ?>"></div>
    </section>
    <?php endif; ?>
</div>
</main>
<script>
function setMain(el) {
    document.getElementById('mainPhoto').src = el.src;
    document.querySelectorAll('.thumb').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}
</script>
<?php if ($listing['latitude'] && $listing['longitude']): ?>
<?php $extraScripts = '<script src="' . BASE_URL . '/public/js/map.js"></script>
<script defer src="https://maps.googleapis.com/maps/api/js?key=' . htmlspecialchars(GOOGLE_MAPS_API_KEY) . '&libraries=marker,places&callback=initDetailMap&loading=async"></script>'; ?>
<?php endif; ?>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
