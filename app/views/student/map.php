<?php
$pageTitle  = 'Peta Penginapan';
$bodyClass  = 'map-fullpage';
$extraHead  = '<style>.site-footer{display:none}</style>';
require BASE_PATH . '/app/views/layouts/header.php';
$listingsJson = json_encode(array_map(fn($l) => [
    'id'       => $l['listing_id'],
    'title'    => $l['title'],
    'address'  => $l['address'],
    'rent'     => $l['monthly_rent'],
    'lat'      => $l['latitude'],
    'lng'      => $l['longitude'],
    'type'     => $l['property_type'],
    'verified' => $l['owner_type'] === 'korporat',
    'url'      => BASE_URL . '/student/listing/' . $l['listing_id'],
], array_filter($listings, fn($l) => $l['latitude'] && $l['longitude'])));
?>
<?php require BASE_PATH . '/app/views/layouts/navbar.php'; ?>
<div class="map-layout">
    <aside class="map-sidebar">
        <div class="sidebar-header">
            <h2>Peta Penginapan</h2>
            <form method="GET" action="<?= BASE_URL ?>/student/map" class="map-search-form">
                <input type="text" name="keyword" value="<?= htmlspecialchars($filters['keyword'] ?? '') ?>"
                       placeholder="Cari kawasan..." class="map-search-input">
                <button type="submit" class="btn btn-primary btn-sm">Cari</button>
            </form>
        </div>
        <ul class="map-listing-list" id="mapSidebarList">
        <?php foreach ($listings as $l): if (!$l['latitude'] || !$l['longitude']) continue; ?>
        <li class="map-sidebar-item" data-id="<?= $l['listing_id'] ?>" onclick="focusMarker(<?= $l['listing_id'] ?>)">
            <div class="msb-title"><?= htmlspecialchars($l['title']) ?></div>
            <div class="msb-rent">RM <?= number_format($l['monthly_rent'], 0) ?>/bln</div>
            <?php if ($l['owner_type'] === 'korporat'): ?>
            <span class="badge-verified-sm">&#10003; UKM</span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        </ul>
    </aside>
    <div id="mainMap" class="map-canvas"
         data-mapid="<?= htmlspecialchars(GOOGLE_MAPS_ID) ?>"></div>
</div>
<script>
window.STAYU_LISTINGS = <?= $listingsJson ?>;
window.UKM_LAT = <?= UKM_LAT ?>;
window.UKM_LNG = <?= UKM_LNG ?>;
window.BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>/public/js/map.js"></script>
<script defer
  src="https://maps.googleapis.com/maps/api/js?key=<?= htmlspecialchars(GOOGLE_MAPS_API_KEY) ?>&libraries=marker,places&callback=initMainMap&loading=async">
</script>
<?php require BASE_PATH . '/app/views/layouts/footer.php'; ?>
