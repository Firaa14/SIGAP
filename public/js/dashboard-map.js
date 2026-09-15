/**
 * SIGAP — Peta Sebaran PLTA (UP Brantas)
 * ---------------------------------------------------------------
 * - Basemap satelit (Esri World Imagery, gratis, tanpa API key)
 * - Marker animasi per status: normal / not_ready / abnormal
 * - Tooltip info (hover), atau tampil permanen via tombol toggle
 * - Popup klik → link ke halaman detail PLTA
 * - Auto-refresh tiap 30 detik dari endpoint JSON /dashboard/map-data
 * ---------------------------------------------------------------
 */
(function () {
    'use strict';

    const mapEl = document.getElementById('plta-map');
    if (!mapEl || typeof L === 'undefined') return;

    const initialData = Array.isArray(window.SIGAP_MAP_DATA) ? window.SIGAP_MAP_DATA : [];
    const mapDataUrl = window.SIGAP_MAP_DATA_URL || null;

    const STATUS_LABEL = {
        normal: 'Normal',
        not_ready: 'Not Ready',
        abnormal: 'Abnormal',
    };

    // ── Inisialisasi peta ────────────────────────────────────────────────
    const map = L.map(mapEl, {
        zoomControl: true,
        attributionControl: true,
        minZoom: 8,
        maxZoom: 17,
    }).setView([-8.0, 112.1], 9);

    // Basemap satelit (citra) — Esri World Imagery, tidak butuh API key.
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
        attribution: 'Tiles &copy; Esri — Source: Esri, Maxar, Earthstar Geographics',
        maxZoom: 18,
    }).addTo(map);

    // Layer label/referensi (nama kota, jalan) supaya peta satelit tetap terbaca.
    L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
        maxZoom: 18,
        opacity: 0.9,
    }).addTo(map);

    let labelsPermanent = false;
    const markerRegistry = {}; // slug -> { marker, plta }

    function buildIconHtml(status) {
        const rings = status === 'abnormal'
            ? '<span class="plta-marker-ring"></span><span class="plta-marker-ring plta-marker-ring-2"></span>'
            : '<span class="plta-marker-ring"></span>';

        return (
            '<div class="plta-marker status-' + status + '">' +
                rings +
                '<span class="plta-marker-pin"></span>' +
            '</div>'
        );
    }

    function buildIcon(status) {
        return L.divIcon({
            className: '',
            html: buildIconHtml(status),
            iconSize: [22, 22],
            iconAnchor: [11, 11],
        });
    }

    function buildTooltipHtml(plta) {
        const statusKey = plta.status;
        const statusLabel = STATUS_LABEL[statusKey] || 'Normal';

        return (
            '<div class="plta-tip-card">' +
                '<div class="plta-tip-head">' +
                    '<span class="plta-tip-code">' + escapeHtml(plta.code) + '</span>' +
                    '<span class="plta-tip-badge ' + statusKey + '">' + statusLabel + '</span>' +
                '</div>' +
                '<div class="plta-tip-name">' + escapeHtml(plta.short_name || plta.name) + '</div>' +
                '<div class="plta-tip-capacity">' + escapeHtml(plta.capacity || '—') + '</div>' +
                '<div class="plta-tip-counts">' +
                    '<span><span class="dot normal"></span>' + plta.normal + ' Normal</span>' +
                    '<span><span class="dot not_ready"></span>' + plta.not_ready + ' Not Ready</span>' +
                    '<span><span class="dot abnormal"></span>' + plta.abnormal + ' Abnormal</span>' +
                '</div>' +
            '</div>'
        );
    }

    function buildPopupHtml(plta) {
        return (
            '<div class="plta-popup-body">' +
                '<div class="plta-tip-name">' + escapeHtml(plta.name) + '</div>' +
                '<div class="plta-tip-capacity">' + escapeHtml(plta.location || '') + ' &middot; ' + escapeHtml(plta.capacity || '—') + '</div>' +
                '<div class="plta-tip-counts">' +
                    '<span><span class="dot normal"></span>' + plta.normal + '</span>' +
                    '<span><span class="dot not_ready"></span>' + plta.not_ready + '</span>' +
                    '<span><span class="dot abnormal"></span>' + plta.abnormal + '</span>' +
                '</div>' +
                (plta.url ? '<a class="plta-popup-link" href="' + plta.url + '">Lihat Detail Equipment →</a>' : '') +
            '</div>'
        );
    }

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str == null ? '' : String(str);
        return div.innerHTML;
    }

    function renderMarkers(list) {
        const bounds = [];

        list.forEach(function (plta) {
            if (plta.latitude == null || plta.longitude == null) return;

            const latlng = [plta.latitude, plta.longitude];
            bounds.push(latlng);

            let entry = markerRegistry[plta.slug];

            if (!entry) {
                const marker = L.marker(latlng, { icon: buildIcon(plta.status) }).addTo(map);

                marker.bindTooltip(buildTooltipHtml(plta), {
                    direction: 'top',
                    offset: [0, -14],
                    className: 'plta-tooltip',
                    permanent: labelsPermanent,
                    sticky: false,
                });

                marker.bindPopup(buildPopupHtml(plta), { className: 'plta-popup-wrap' });

                entry = { marker: marker, plta: plta };
                markerRegistry[plta.slug] = entry;
            } else {
                entry.marker.setIcon(buildIcon(plta.status));
                entry.marker.setTooltipContent(buildTooltipHtml(plta));
                entry.marker.setPopupContent(buildPopupHtml(plta));
                entry.plta = plta;
            }
        });

        if (bounds.length) {
            map.fitBounds(bounds, { padding: [36, 36], maxZoom: 11 });
        }
    }

    renderMarkers(initialData);

    // ── Toggle: tampilkan semua label / info card secara permanen ────────
    const toggleBtn = document.getElementById('map-toggle-labels');
    if (toggleBtn) {
        toggleBtn.addEventListener('click', function () {
            labelsPermanent = !labelsPermanent;
            toggleBtn.classList.toggle('active', labelsPermanent);
            toggleBtn.textContent = labelsPermanent ? 'Sembunyikan Label' : 'Tampilkan Semua Label';

            Object.keys(markerRegistry).forEach(function (slug) {
                const entry = markerRegistry[slug];
                entry.marker.unbindTooltip();
                entry.marker.bindTooltip(buildTooltipHtml(entry.plta), {
                    direction: 'top',
                    offset: [0, -14],
                    className: 'plta-tooltip',
                    permanent: labelsPermanent,
                    sticky: false,
                });
                if (labelsPermanent) entry.marker.openTooltip();
            });
        });
    }

    // ── Update angka stat card di atas peta ───────────────────────────────
    function updateStatCards(stats) {
        if (!stats) return;
        Object.keys(stats).forEach(function (key) {
            const el = document.querySelector('[data-stat="' + key + '"]');
            if (!el) return;
            if (el.textContent.trim() === String(stats[key])) return;

            el.textContent = stats[key];
            el.classList.add('stat-value-pulse');
            setTimeout(function () { el.classList.remove('stat-value-pulse'); }, 700);
        });
    }

    // ── Polling auto-refresh tiap 30 detik ────────────────────────────────
    if (mapDataUrl) {
        setInterval(function () {
            fetch(mapDataUrl, { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (payload) {
                    if (!payload) return;
                    renderMarkers(payload.plta || []);
                    updateStatCards(payload.stats);
                })
                .catch(function () {
                    // Diam-diam gagal — koneksi sementara terputus, coba lagi di siklus berikutnya.
                });
        }, 30000);
    }
})();
