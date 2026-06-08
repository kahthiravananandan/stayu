/* ================================================================
   StayU — map.js
   Google Maps integration — three entry points (API callbacks):
     initMainMap()   — full-page map with all listings
     initDetailMap() — single listing detail map + distance
     initPickerMap() — draggable location picker on listing form

   Globals used:
     window.STAYU_LISTINGS — [{id,lat,lng,title,address,rent,type,verified,url}]
     window.UKM_LAT / window.UKM_LNG   — numbers (main map)
     window.BASE_URL                    — string  (main map)
   ================================================================ */

/* eslint-env browser */

'use strict';

// ── Shared constants ─────────────────────────────────────────────
var UKM_LAT = 2.9213;
var UKM_LNG = 101.7740;   // UKM main gate

// ── Shared map style (minimal) ───────────────────────────────────
var LIGHT_STYLE = [
    { featureType: 'poi',     stylers: [{ visibility: 'off' }] },
    { featureType: 'transit', stylers: [{ visibility: 'simplified' }] }
];

// ── Marker icons ─────────────────────────────────────────────────
function ukmIcon() {
    return {
        path:         google.maps.SymbolPath.CIRCLE,
        fillColor:    '#2563eb',
        fillOpacity:  1,
        strokeColor:  '#fff',
        strokeWeight: 2,
        scale:        10
    };
}

function listingIcon(verified) {
    return {
        path:         google.maps.SymbolPath.CIRCLE,
        fillColor:    verified ? '#16a34a' : '#ea580c',
        fillOpacity:  1,
        strokeColor:  '#fff',
        strokeWeight: 2,
        scale:        9
    };
}

function pickerIcon() {
    return {
        path:         google.maps.SymbolPath.DROP,
        fillColor:    '#2563eb',
        fillOpacity:  1,
        strokeColor:  '#fff',
        strokeWeight: 2,
        scale:        14,
        anchor:       new google.maps.Point(0, 4)
    };
}

// ── Info-window HTML builder ─────────────────────────────────────
function buildInfoHtml(listing) {
    var typeLabel = { room: 'Bilik', whole_unit: 'Unit Penuh', shared_room: 'Bilik Kongsi' }[listing.type] || listing.type;
    var badge = listing.verified
        ? '<span style="background:#dcfce7;color:#15803d;padding:2px 7px;border-radius:99px;font-size:.72rem;font-weight:700">&#10003; UKM Verified</span>'
        : '';
    return [
        '<div style="max-width:230px;font-family:Inter,sans-serif;padding:4px 2px">',
        badge,
        '<div style="font-weight:700;font-size:.95rem;margin:4px 0 2px">', escHtml(listing.title), '</div>',
        '<div style="color:#4b5563;font-size:.82rem;margin-bottom:6px">&#128205; ', escHtml(listing.address), '</div>',
        '<div style="font-size:.82rem;margin-bottom:2px">Jenis: ', typeLabel, '</div>',
        '<div style="font-weight:800;font-size:1.05rem;color:#2563eb;margin-bottom:8px">',
        'RM ', Number(listing.rent).toLocaleString('ms-MY'),
        '<span style="font-weight:400;font-size:.8rem;color:#6b7280">/bln</span></div>',
        '<a href="', listing.url, '" ',
        'style="display:inline-block;background:#2563eb;color:#fff;padding:5px 14px;',
        'border-radius:6px;text-decoration:none;font-size:.85rem;font-weight:600">Lihat Iklan &rarr;</a>',
        '</div>'
    ].join('');
}

function escHtml(str) {
    var d = document.createElement('div');
    d.appendChild(document.createTextNode(str || ''));
    return d.innerHTML;
}

// ── Haversine formula ────────────────────────────────────────────
function haversineKm(lat1, lng1, lat2, lng2) {
    var R    = 6371;
    var dLat = (lat2 - lat1) * Math.PI / 180;
    var dLng = (lng2 - lng1) * Math.PI / 180;
    var a    = Math.sin(dLat / 2) * Math.sin(dLat / 2)
             + Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180)
             * Math.sin(dLng / 2) * Math.sin(dLng / 2);
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

// ════════════════════════════════════════════════════════════════
//  initMainMap — full-page listing map
// ════════════════════════════════════════════════════════════════
var markerMap = {};

window.initMainMap = function () {
    var listings = window.STAYU_LISTINGS || [];
    var cLat     = window.UKM_LAT || UKM_LAT;
    var cLng     = window.UKM_LNG || UKM_LNG;

    var mapEl = document.getElementById('mainMap');
    if (!mapEl) return;

    var map = new google.maps.Map(mapEl, {
        center:            { lat: cLat, lng: cLng },
        zoom:              13,
        mapId:             mapEl.dataset.mapid,
        mapTypeControl:    false,
        streetViewControl: false,
        fullscreenControl: true
    });

    // UKM campus marker
    var ukmMarker = new google.maps.marker.AdvancedMarkerElement({
        position:     { lat: cLat, lng: cLng },
        map:          map,
        title:        'UKM Bangi',
        zIndex:       10,
        gmpClickable: true
    });
    var ukmInfo = new google.maps.InfoWindow({
        content: '<div style="padding:4px 2px;font-family:Inter,sans-serif"><strong>Universiti Kebangsaan Malaysia</strong><br><small>UKM Bangi</small></div>'
    });
    ukmMarker.addListener('gmp-click', function () { ukmInfo.open(map, ukmMarker); });

    var activeInfo = null;

    listings.forEach(function (listing) {
        if (!listing.lat || !listing.lng) return;

        var marker = new google.maps.marker.AdvancedMarkerElement({
            position:     { lat: Number(listing.lat), lng: Number(listing.lng) },
            map:          map,
            title:        listing.title,
            gmpClickable: true
        });

        markerMap[listing.id] = { marker: marker, map: map };

        var infoWin = new google.maps.InfoWindow({ content: buildInfoHtml(listing) });

        marker.addListener('gmp-click', function () {
            if (activeInfo) activeInfo.close();
            infoWin.open(map, marker);
            activeInfo = infoWin;
            document.querySelectorAll('.map-sidebar-item').forEach(function (el) {
                el.classList.toggle('active', Number(el.dataset.id) === listing.id);
            });
        });
    });
};

window.focusMarker = function (id) {
    var entry = markerMap[id];
    if (!entry) return;
    entry.map.panTo(entry.marker.position);
    entry.map.setZoom(16);
    google.maps.event.trigger(entry.marker, 'gmp-click');
    document.querySelectorAll('.map-sidebar-item').forEach(function (el) {
        el.classList.toggle('active', Number(el.dataset.id) === id);
    });
};

// ════════════════════════════════════════════════════════════════
//  initDetailMap — single listing detail page
// ════════════════════════════════════════════════════════════════
window.initDetailMap = function () {
    var mapEl = document.getElementById('detailMap');
    if (!mapEl) return;

    var lat   = Number(mapEl.dataset.lat);
    var lng   = Number(mapEl.dataset.lng);
    var title = mapEl.dataset.title || 'Lokasi';

    if (!lat || !lng) return;

    var map = new google.maps.Map(mapEl, {
        center:            { lat: lat, lng: lng },
        zoom:              15,
        mapId:             mapEl.dataset.mapid,
        mapTypeControl:    false,
        streetViewControl: false
    });

    // Listing marker
    var listingMarker = new google.maps.marker.AdvancedMarkerElement({
        position:     { lat: lat, lng: lng },
        map:          map,
        title:        title,
        gmpClickable: true
    });
    var listingInfo = new google.maps.InfoWindow({
        content: '<div style="font-family:Inter,sans-serif;padding:4px 2px"><strong>' + escHtml(title) + '</strong></div>'
    });
    listingMarker.addListener('gmp-click', function () { listingInfo.open(map, listingMarker); });

    // UKM marker
    var ukmMarker = new google.maps.marker.AdvancedMarkerElement({
        position:     { lat: UKM_LAT, lng: UKM_LNG },
        map:          map,
        title:        'UKM Bangi',
        gmpClickable: true
    });
    var ukmInfo = new google.maps.InfoWindow({
        content: '<div style="padding:4px 2px;font-family:Inter,sans-serif"><strong>UKM Bangi (Pintu Utama)</strong></div>'
    });
    ukmMarker.addListener('gmp-click', function () { ukmInfo.open(map, ukmMarker); });

    // Dashed polyline: listing → UKM
    new google.maps.Polyline({
        path:          [{ lat: lat, lng: lng }, { lat: UKM_LAT, lng: UKM_LNG }],
        geodesic:      true,
        strokeColor:   '#2563eb',
        strokeOpacity: 0,
        icons: [{
            icon:   { path: 'M 0,-1 0,1', strokeOpacity: 1, scale: 3 },
            offset: '0',
            repeat: '12px'
        }],
        map: map
    });

    // Fit bounds to include both markers
    var bounds = new google.maps.LatLngBounds();
    bounds.extend({ lat: lat,     lng: lng     });
    bounds.extend({ lat: UKM_LAT, lng: UKM_LNG });
    map.fitBounds(bounds, { top: 40, right: 40, bottom: 40, left: 40 });
    google.maps.event.addListenerOnce(map, 'bounds_changed', function () {
        if (map.getZoom() > 16) map.setZoom(16);
    });

    // ── Distance computation ──────────────────────────────────────
    var distEl = document.getElementById('distanceResult');
    if (!distEl) return;

    function showDistance(km, mode) {
        distEl.innerHTML = '&#128337; ' + Number(km).toFixed(1) + ' km dari UKM Bangi'
                         + (mode ? ' <small class="text-muted">(' + mode + ')</small>' : '');
    }

    // Try Routes API (driving), fall back to Haversine
    var apiKey = document.getElementById('detailMap').dataset.apikey;
    fetch('https://routes.googleapis.com/distanceMatrix/v2:computeRouteMatrix', {
        method:  'POST',
        headers: {
            'Content-Type':     'application/json',
            'X-Goog-Api-Key':   apiKey,
            'X-Goog-FieldMask': 'originIndex,destinationIndex,distanceMeters,duration'
        },
        body: JSON.stringify({
            origins: [{
                waypoint: { location: { latLng: { latitude: lat, longitude: lng } } }
            }],
            destinations: [{
                waypoint: { location: { latLng: { latitude: UKM_LAT, longitude: UKM_LNG } } }
            }],
            travelMode: 'DRIVE'
        })
    })
    .then(function (res) { return res.json(); })
    .then(function (data) {
        if (data && data[0] && data[0].distanceMeters) {
            showDistance(data[0].distanceMeters / 1000, 'memandu');
        } else {
            showDistance(haversineKm(lat, lng, UKM_LAT, UKM_LNG), 'anggaran');
        }
    })
    .catch(function () {
        showDistance(haversineKm(lat, lng, UKM_LAT, UKM_LNG), 'anggaran');
    });
};

// ════════════════════════════════════════════════════════════════
//  initPickerMap — draggable location picker on listing form
// ════════════════════════════════════════════════════════════════
window.initPickerMap = function () {
    var mapEl     = document.getElementById('pickerMap');
    var latInput  = document.getElementById('latitude');
    var lngInput  = document.getElementById('longitude');
    var distInput = document.getElementById('distance_km');
    var coordDisp = document.getElementById('coordDisplay');
    var geocodeBtn = document.getElementById('geocodeBtn');

    if (!mapEl || !latInput || !lngInput) return;

    // Seed from edit-mode data attributes, fall back to UKM campus centre
    var initLat     = parseFloat(mapEl.dataset.lat) || UKM_LAT;
    var initLng     = parseFloat(mapEl.dataset.lng) || UKM_LNG;
    var hasExisting = !!(parseFloat(mapEl.dataset.lat) && parseFloat(mapEl.dataset.lng));

    var map = new google.maps.Map(mapEl, {
        center:            { lat: initLat, lng: initLng },
        zoom:              hasExisting ? 15 : 14,
        mapId:             mapEl.dataset.mapid,
        mapTypeControl:    false,
        streetViewControl: false,
        fullscreenControl: false
    });

    // Static UKM reference marker
    new google.maps.marker.AdvancedMarkerElement({
        position: { lat: UKM_LAT, lng: UKM_LNG },
        map:      map,
        title:    'UKM Bangi (rujukan)'
    });

    // Draggable listing-location marker
    var markerEl = document.createElement('div');
    markerEl.style.cssText = 'width:16px;height:16px;background:#003366;border:2px solid #fff;border-radius:50%;cursor:grab;';

    var marker = new google.maps.marker.AdvancedMarkerElement({
        position:     { lat: initLat, lng: initLng },
        map:          hasExisting ? map : null,
        title:        'Seret atau klik peta untuk menetapkan lokasi',
        content:      markerEl,
        gmpDraggable: true
    });

    // ── Helpers ───────────────────────────────────────────────────

    function setCoords(latlng) {
        var lat = typeof latlng.lat === 'function' ? latlng.lat() : Number(latlng.lat);
        var lng = typeof latlng.lng === 'function' ? latlng.lng() : Number(latlng.lng);
        latInput.value = lat.toFixed(7);
        lngInput.value = lng.toFixed(7);

        var km = haversineKm(lat, lng, UKM_LAT, UKM_LNG);
        if (distInput) distInput.value = km.toFixed(2);

        if (coordDisp) {
            coordDisp.innerHTML =
                '<strong>Koordinat ditetapkan:</strong> ' +
                lat.toFixed(5) + ', ' + lng.toFixed(5) +
                ' &nbsp;|&nbsp; Jarak UKM (anggaran): <strong>' + km.toFixed(1) + ' km</strong>';
        }
    }

    function placeMarker(latlng) {
        marker.position = latlng;
        marker.map = map;
        map.panTo(latlng);
        setCoords(latlng);
    }

    // Initialise coord display for edit mode
    if (hasExisting) {
        setCoords({ lat: initLat, lng: initLng });
    }

    // Marker drag end
    marker.addListener('dragend', function () { setCoords(marker.position); });

    // Map click → move marker
    map.addListener('click', function (e) { placeMarker(e.latLng); });

    // ── Geocode button ─────────────────────────────────────────────
    if (geocodeBtn) {
        geocodeBtn.addEventListener('click', function () {
            var addrEl = document.getElementById('address');
            var addr   = addrEl ? addrEl.value.trim() : '';
            if (!addr) { alert('Sila masukkan alamat dahulu.'); return; }

            var origText = geocodeBtn.textContent;
            geocodeBtn.textContent = 'Mencari...';
            geocodeBtn.disabled    = true;

            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: addr + ', Malaysia' }, function (results, status) {
                geocodeBtn.textContent = origText;
                geocodeBtn.disabled    = false;

                if (status === 'OK') {
                    var loc = results[0].geometry.location;
                    map.setZoom(16);
                    placeMarker(loc);
                } else {
                    alert('Alamat tidak dijumpai (' + status + '). Cuba tambah bandar atau poskod.');
                }
            });
        });
    }
};
