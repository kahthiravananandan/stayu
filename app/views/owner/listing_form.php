<?php
$pageTitle = ($data['listing'] ? 'Edit Iklan' : 'Tambah Iklan Baharu');
$isEdit    = !empty($data['listing']);
$l         = $data['listing'] ?? [];
$amenityList = $data['amenityList'] ?? [];
$selectedAmenities = array_column($l['amenities'] ?? [], 'amenity_id');
$error   = getFlash('error');
require BASE_PATH . '/app/views/layouts/header.php';
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="form-page">
<div class="container container--medium">
    <div class="page-header">
        <h1><?= $isEdit ? 'Edit Iklan' : 'Tambah Iklan Baharu' ?></h1>
    </div>

    <?php if ($error): ?><div class="alert alert-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST"
          action="<?= BASE_URL ?>/owner/listing_form<?= $isEdit ? '/' . $l['listing_id'] : '' ?>"
          enctype="multipart/form-data"
          class="listing-form card card-body">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($data['csrf']) ?>">

        <h2 class="form-section-title">Maklumat Asas</h2>
        <div class="form-group">
            <label for="title">Tajuk Iklan *</label>
            <input type="text" id="title" name="title" required
                   value="<?= htmlspecialchars($l['title'] ?? '') ?>"
                   placeholder="cth: Bilik Sewa Dekat UKM, Fully Furnished">
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="property_type">Jenis Hartanah *</label>
                <select id="property_type" name="property_type" required>
                    <option value="room"        <?= ($l['property_type'] ?? '') === 'room'        ? 'selected':'' ?>>Bilik</option>
                    <option value="whole_unit"  <?= ($l['property_type'] ?? '') === 'whole_unit'  ? 'selected':'' ?>>Unit Penuh</option>
                    <option value="shared_room" <?= ($l['property_type'] ?? '') === 'shared_room' ? 'selected':'' ?>>Bilik Kongsi</option>
                </select>
            </div>
            <div class="form-group">
                <label for="gender_pref">Keutamaan Jantina *</label>
                <select id="gender_pref" name="gender_pref" required>
                    <option value="any"    <?= ($l['gender_pref'] ?? 'any') === 'any'    ? 'selected':'' ?>>Semua</option>
                    <option value="male"   <?= ($l['gender_pref'] ?? '') === 'male'   ? 'selected':'' ?>>Lelaki</option>
                    <option value="female" <?= ($l['gender_pref'] ?? '') === 'female' ? 'selected':'' ?>>Perempuan</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="monthly_rent">Sewa Bulanan (RM) *</label>
                <input type="number" id="monthly_rent" name="monthly_rent" required min="1" step="0.01"
                       value="<?= htmlspecialchars($l['monthly_rent'] ?? '') ?>" placeholder="cth: 550">
            </div>
            <div class="form-group">
                <label for="deposit">Deposit (RM)</label>
                <input type="number" id="deposit" name="deposit" min="0" step="0.01"
                       value="<?= htmlspecialchars($l['deposit'] ?? '') ?>" placeholder="cth: 1100">
            </div>
        </div>

        <h2 class="form-section-title">Lokasi</h2>
        <div class="form-group">
            <label for="address">Alamat Penuh *</label>
            <input type="text" id="address" name="address" required
                   value="<?= htmlspecialchars($l['address'] ?? '') ?>"
                   placeholder="cth: No. 5, Jalan Damai 3, Bandar Baru Bangi">
            <button type="button" class="btn btn-ghost btn-sm mt-1" id="geocodeBtn">
                &#128205; Dapatkan Koordinat dari Alamat
            </button>
        </div>

        <!-- Hidden coordinate fields — updated by the map picker -->
        <input type="hidden" id="latitude"    name="latitude"    value="<?= htmlspecialchars($l['latitude']    ?? '') ?>">
        <input type="hidden" id="longitude"   name="longitude"   value="<?= htmlspecialchars($l['longitude']   ?? '') ?>">
        <input type="hidden" id="distance_km" name="distance_km" value="<?= htmlspecialchars($l['distance_km'] ?? '') ?>">

        <!-- Map picker: drag the pin or click the map to set location -->
        <div id="pickerMap" class="picker-map"
             data-lat="<?= htmlspecialchars($l['latitude']  ?? '') ?>"
             data-lng="<?= htmlspecialchars($l['longitude'] ?? '') ?>"
             data-mapid="<?= htmlspecialchars(GOOGLE_MAPS_ID) ?>"></div>
        <div id="coordDisplay" class="coord-display">
            <?php if (!empty($l['latitude']) && !empty($l['longitude'])): ?>
                &#128205; Koordinat sedia ada: <?= htmlspecialchars($l['latitude']) ?>, <?= htmlspecialchars($l['longitude']) ?>
                &nbsp;|&nbsp; Seret pin atau klik peta untuk mengubah lokasi.
            <?php else: ?>
                Klik pada peta atau gunakan butang di atas untuk menetapkan lokasi iklan.
            <?php endif; ?>
        </div>

        <h2 class="form-section-title">Kemudahan</h2>
        <div class="amenity-checks">
        <?php foreach ($amenityList as $am): ?>
        <label class="check-label">
            <input type="checkbox" name="amenities[]" value="<?= $am['amenity_id'] ?>"
                   <?= in_array($am['amenity_id'], $selectedAmenities) ? 'checked' : '' ?>>
            <?= htmlspecialchars($am['amenity_name']) ?>
        </label>
        <?php endforeach; ?>
        </div>

        <h2 class="form-section-title">Penerangan</h2>
        <div class="form-group">
            <label for="description">Penerangan (pilihan)</label>
            <textarea id="description" name="description" rows="5"
                      placeholder="Huraikan unit, peraturan, kemudahan berhampiran..."><?= htmlspecialchars($l['description'] ?? '') ?></textarea>
        </div>

        <h2 class="form-section-title">Foto</h2>
        <div class="form-group">
            <label>Muat Naik Foto (maks. 5, JPEG/PNG/WebP, maks. 5MB setiap satu)</label>
            <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple id="photoInput">
            <div id="photoPreview" class="photo-preview-row"></div>
        </div>
        <?php if (!empty($l['photos'])): ?>
        <div class="existing-photos">
            <p class="text-muted">Foto sedia ada:</p>
            <div class="photo-thumb-row">
            <?php foreach ($l['photos'] as $ph): ?>
            <div class="existing-thumb">
                <img src="<?= BASE_URL ?>/public/uploads/photos/<?= htmlspecialchars($ph['photo_path']) ?>" alt="foto">
                <?php if ($ph['is_cover']): ?><span class="cover-label">Utama</span><?php endif; ?>
            </div>
            <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Kemaskini Iklan' : 'Hantar Iklan' ?></button>
            <a href="<?= BASE_URL ?>/owner/listing_manage" class="btn btn-ghost">Batal</a>
        </div>
    </form>
</div>
</main>
<script>
// Photo preview — no Maps dependency
document.getElementById('photoInput').addEventListener('change', function () {
    const preview = document.getElementById('photoPreview');
    preview.innerHTML = '';
    Array.from(this.files).slice(0, 5).forEach(function (f) {
        const img = document.createElement('img');
        img.src       = URL.createObjectURL(f);
        img.className = 'preview-thumb';
        preview.appendChild(img);
    });
});
</script>
<!-- map.js must load before the Maps API so initPickerMap is defined when the callback fires -->
<script src="<?= BASE_URL ?>/public/js/map.js"></script>
<script defer
  src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars(GOOGLE_MAPS_API_KEY) ?>&libraries=marker,places&callback=initPickerMap&loading=async">
</script>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
