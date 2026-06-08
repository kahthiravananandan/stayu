<?php
$pageTitle = 'Cari Penginapan';
require BASE_PATH . '/app/views/layouts/header.php';
$f = $filters ?? [];
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<main class="search-page">
    <div class="container">
        <div class="search-hero">
            <h1>Cari Penginapan Luar Kampus</h1>
            <p>Iklan-iklan yang telah disahkan oleh PPP UKM untuk keselamatan anda.</p>
        </div>

        <!-- Search & Filters -->
        <form method="GET" action="<?= BASE_URL ?>/student/search" class="search-form" id="searchForm">
            <div class="search-bar-group">
                <input type="text" name="keyword" value="<?= htmlspecialchars($f['keyword'] ?? '') ?>"
                       placeholder="Cari mengikut tajuk atau alamat..." class="search-input">
                <button type="submit" class="btn btn-primary">Cari</button>
                <a href="<?= BASE_URL ?>/student/map" class="btn btn-outline">&#128205; Peta</a>
            </div>

            <details class="filter-panel" <?= !empty(array_filter($f)) ? 'open' : '' ?>>
                <summary>Penapis Lanjutan</summary>
                <div class="filter-grid">
                    <div class="form-group">
                        <label>Jenis Hartanah</label>
                        <select name="property_type">
                            <option value="">Semua</option>
                            <option value="room"        <?= ($f['property_type'] ?? '') === 'room'        ? 'selected' : '' ?>>Bilik</option>
                            <option value="whole_unit"  <?= ($f['property_type'] ?? '') === 'whole_unit'  ? 'selected' : '' ?>>Unit Penuh</option>
                            <option value="shared_room" <?= ($f['property_type'] ?? '') === 'shared_room' ? 'selected' : '' ?>>Bilik Kongsi</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sewa Min (RM)</label>
                        <input type="number" name="min_rent" value="<?= htmlspecialchars($f['min_rent'] ?? '') ?>" min="0" step="50" placeholder="0">
                    </div>
                    <div class="form-group">
                        <label>Sewa Maks (RM)</label>
                        <input type="number" name="max_rent" value="<?= htmlspecialchars($f['max_rent'] ?? '') ?>" min="0" step="50" placeholder="2000">
                    </div>
                    <div class="form-group">
                        <label>Keutamaan Jantina</label>
                        <select name="gender_pref">
                            <option value="">Semua</option>
                            <option value="male"   <?= ($f['gender_pref'] ?? '') === 'male'   ? 'selected' : '' ?>>Lelaki</option>
                            <option value="female" <?= ($f['gender_pref'] ?? '') === 'female' ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Jarak Maks dari UKM (km)</label>
                        <input type="number" name="max_distance" value="<?= htmlspecialchars($f['max_distance'] ?? '') ?>" min="0" step="0.5" placeholder="5">
                    </div>
                    <div class="form-group form-group--full">
                        <label>Kemudahan</label>
                        <div class="amenity-checks">
                        <?php foreach ($amenities as $am): ?>
                        <label class="check-label">
                            <input type="checkbox" name="amenities[]" value="<?= $am['amenity_id'] ?>"
                                   <?= in_array($am['amenity_id'], (array)($f['amenities'] ?? [])) ? 'checked' : '' ?>>
                            <?= htmlspecialchars($am['amenity_name']) ?>
                        </label>
                        <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="filter-actions">
                    <button type="submit" class="btn btn-primary">Tapis</button>
                    <a href="<?= BASE_URL ?>/student/search" class="btn btn-ghost">Kosongkan</a>
                </div>
            </details>
        </form>

        <!-- Results Info -->
        <div class="results-info">
            <span><?= $totalCount ?> iklan dijumpai</span>
        </div>

        <!-- Listing Grid -->
        <?php if (empty($listings)): ?>
        <div class="empty-results">
            <p>Tiada iklan dijumpai. Cuba ubah penapis carian.</p>
        </div>
        <?php else: ?>
        <div class="listing-grid">
        <?php foreach ($listings as $l): ?>
        <article class="listing-card">
            <?php if ($l['owner_type'] === 'korporat'): ?>
            <div class="verified-badge">&#10003; Disahkan UKM</div>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/student/listing/<?= $l['listing_id'] ?>" class="listing-img-link">
                <?php if ($l['cover_photo']): ?>
                <img src="<?= BASE_URL ?>/public/uploads/photos/<?= htmlspecialchars($l['cover_photo']) ?>"
                     alt="<?= htmlspecialchars($l['title']) ?>" class="listing-img" loading="lazy">
                <?php else: ?>
                <div class="listing-img listing-img--placeholder">&#127968;</div>
                <?php endif; ?>
            </a>
            <div class="listing-body">
                <div class="listing-type"><?= match($l['property_type']) {
                    'room' => 'Bilik', 'whole_unit' => 'Unit Penuh', 'shared_room' => 'Bilik Kongsi', default => $l['property_type']
                } ?></div>
                <h3 class="listing-title">
                    <a href="<?= BASE_URL ?>/student/listing/<?= $l['listing_id'] ?>">
                        <?= htmlspecialchars($l['title']) ?>
                    </a>
                </h3>
                <p class="listing-address">&#128205; <?= htmlspecialchars($l['address']) ?></p>
                <?php if ($l['distance_km']): ?>
                <p class="listing-dist">&#128337; <?= number_format($l['distance_km'], 1) ?> km dari UKM</p>
                <?php endif; ?>
                <div class="listing-footer">
                    <span class="listing-rent">RM <?= number_format($l['monthly_rent'], 0) ?>/bulan</span>
                    <span class="listing-gender"><?= match($l['gender_pref']) {
                        'male' => '&#9794; Lelaki', 'female' => '&#9792; Perempuan', default => '&#9899; Semua'
                    } ?></span>
                </div>
            </div>
        </article>
        <?php endforeach; ?>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <nav class="pagination">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
               class="page-btn <?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
            <?php endfor; ?>
        </nav>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
