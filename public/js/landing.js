/**
 * SIGAP Landing Page — landing.js
 * ------------------------------------------------------------
 * - Navbar scroll effect
 * - Mobile menu toggle
 * - Smooth scroll navigation
 * - Scroll reveal animation
 * - Interactive Leaflet map (reuse existing dashboard-map logic)
 * - Live stats polling dari /landing/stats endpoint
 * ------------------------------------------------------------
 */
(function () {
    'use strict';

    /* ── 1. NAVBAR SCROLL EFFECT ──────────────────────────────── */
    const nav = document.getElementById('lp-navbar');
    if (nav) {
        const handleScroll = function () {
            if (window.scrollY > 10) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        };
        window.addEventListener('scroll', handleScroll, { passive: true });
        handleScroll();
    }

    /* ── 2. MOBILE MENU TOGGLE ────────────────────────────────── */
    const hamburger = document.getElementById('lp-hamburger');
    const mobileMenu = document.getElementById('lp-mobile-menu');

    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', function () {
            hamburger.classList.toggle('active');
            mobileMenu.classList.toggle('open');
        });

        // Close mobile menu on link click
        mobileMenu.querySelectorAll('a, [data-scroll-to]').forEach(function (el) {
            el.addEventListener('click', function () {
                hamburger.classList.remove('active');
                mobileMenu.classList.remove('open');
            });
        });
    }

    /* ── 3. SMOOTH SCROLL ─────────────────────────────────────── */
    function scrollToSection(sectionId) {
        const el = document.getElementById(sectionId);
        if (!el) return;
        const offset = 72; // navbar height
        const top = el.getBoundingClientRect().top + window.scrollY - offset;
        window.scrollTo({ top: top, behavior: 'smooth' });
    }

    document.querySelectorAll('[data-scroll-to]').forEach(function (el) {
        el.addEventListener('click', function () {
            scrollToSection(el.getAttribute('data-scroll-to'));
        });
    });

    /* ── 4. SCROLL REVEAL ─────────────────────────────────────── */
    const revealEls = document.querySelectorAll('.lp-reveal');
    if (revealEls.length > 0 && typeof IntersectionObserver !== 'undefined') {
        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

        revealEls.forEach(function (el) {
            observer.observe(el);
        });
    } else {
        // Fallback: reveal all immediately
        revealEls.forEach(function (el) { el.classList.add('revealed'); });
    }

    /* ── 5. INTERACTIVE MAP ───────────────────────────────────── */
    var lpMap = null;
    var lpMarkerRegistry = {};

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.textContent = (str == null) ? '' : String(str);
        return div.innerHTML;
    }

    function getLandingStatusLabel(status) {
        var labels = {
            normal:    'Normal',
            abnormal:  'Abnormal',
            not_ready: 'Not Ready',
        };
        return labels[status] || status;
    }

    function buildLpIcon(status) {
        if (typeof L === 'undefined') return null;
        var colors = {
            normal:    '#10B981',
            abnormal:  '#F59E0B',
            not_ready: '#EF4444',
        };
        var color = colors[status] || colors.normal;
        var pulse = (status === 'abnormal')
            ? '<span style="position:absolute;inset:-6px;border-radius:50%;border:2px solid ' + color + ';opacity:0.5;animation:lp-ring 1.8s linear infinite;"></span>'
            : '';
        var html = '<div style="position:relative;width:24px;height:24px;display:flex;align-items:center;justify-content:center;">'
            + pulse
            + '<span style="display:block;width:14px;height:14px;border-radius:50%;background:' + color + ';border:2.5px solid #fff;box-shadow:0 2px 8px rgba(0,0,0,0.3);"></span>'
            + '</div>';
        return L.divIcon({
            className: '',
            html: html,
            iconSize: [24, 24],
            iconAnchor: [12, 12],
        });
    }

    function buildLpTooltip(plta) {
        var statusColors = { normal: '#10B981', abnormal: '#F59E0B', not_ready: '#EF4444' };
        var statusColor = statusColors[plta.status] || '#10B981';
        var statusLabel = getLandingStatusLabel(plta.status);
        return '<div style="font-family:Inter,sans-serif;min-width:180px;">'
            + '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;gap:8px;">'
            + '<span style="font-size:11px;font-weight:700;letter-spacing:0.5px;color:#64748B;">' + escapeHtml(plta.code) + '</span>'
            + '<span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:6px;background:' + statusColor + '22;color:' + statusColor + ';">' + statusLabel + '</span>'
            + '</div>'
            + '<div style="font-size:13px;font-weight:700;color:#0A1628;margin-bottom:4px;">' + escapeHtml(plta.short_name || plta.name) + '</div>'
            + '<div style="font-size:11px;color:#94A3B8;margin-bottom:8px;">' + escapeHtml(plta.capacity || '—') + '</div>'
            + '<div style="display:flex;gap:10px;font-size:11px;">'
            + '<span style="display:flex;align-items:center;gap:3px;"><span style="width:7px;height:7px;border-radius:50%;background:#10B981;display:inline-block;"></span>' + (plta.normal || 0) + '</span>'
            + '<span style="display:flex;align-items:center;gap:3px;"><span style="width:7px;height:7px;border-radius:50%;background:#EF4444;display:inline-block;"></span>' + (plta.not_ready || 0) + '</span>'
            + '<span style="display:flex;align-items:center;gap:3px;"><span style="width:7px;height:7px;border-radius:50%;background:#F59E0B;display:inline-block;"></span>' + (plta.abnormal || 0) + '</span>'
            + '</div></div>';
    }

    function buildLpPopup(plta) {
        var detailUrl = plta.url || null;
        return '<div style="font-family:Inter,sans-serif;min-width:200px;">'
            + '<div style="font-size:14px;font-weight:700;color:#0A1628;margin-bottom:4px;">' + escapeHtml(plta.name) + '</div>'
            + '<div style="font-size:11.5px;color:#94A3B8;margin-bottom:10px;">' + escapeHtml((plta.location || '') + (plta.capacity ? ' · ' + plta.capacity : '')) + '</div>'
            + '<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:12px;">'
            + '<div style="text-align:center;padding:6px 4px;border-radius:8px;background:#ECFDF5;">'
            + '<div style="font-size:16px;font-weight:800;color:#059669;">' + (plta.normal || 0) + '</div>'
            + '<div style="font-size:9px;font-weight:600;color:#6EE7B7;text-transform:uppercase;letter-spacing:0.5px;">Normal</div></div>'
            + '<div style="text-align:center;padding:6px 4px;border-radius:8px;background:#FEF2F2;">'
            + '<div style="font-size:16px;font-weight:800;color:#DC2626;">' + (plta.not_ready || 0) + '</div>'
            + '<div style="font-size:9px;font-weight:600;color:#FCA5A5;text-transform:uppercase;letter-spacing:0.5px;">Not Ready</div></div>'
            + '<div style="text-align:center;padding:6px 4px;border-radius:8px;background:#FFFBEB;">'
            + '<div style="font-size:16px;font-weight:800;color:#D97706;">' + (plta.abnormal || 0) + '</div>'
            + '<div style="font-size:9px;font-weight:600;color:#FCD34D;text-transform:uppercase;letter-spacing:0.5px;">Abnormal</div></div>'
            + '</div>'
            + (detailUrl
                ? '<a href="' + detailUrl + '" style="display:block;text-align:center;padding:8px;border-radius:8px;background:#1A56DB;color:#fff;font-size:12px;font-weight:600;text-decoration:none;">Lihat Detail Equipment →</a>'
                : '')
            + '</div>';
    }

    function renderLpMarkers(list) {
        if (typeof L === 'undefined' || !lpMap) return;
        var bounds = [];

        list.forEach(function (plta) {
            if (plta.latitude == null || plta.longitude == null) return;
            var latlng = [plta.latitude, plta.longitude];
            bounds.push(latlng);

            var entry = lpMarkerRegistry[plta.slug];
            if (!entry) {
                var marker = L.marker(latlng, { icon: buildLpIcon(plta.status) }).addTo(lpMap);
                marker.bindTooltip(buildLpTooltip(plta), {
                    direction: 'top', offset: [0, -14],
                    className: 'lp-map-tip',
                    sticky: false,
                });
                marker.bindPopup(buildLpPopup(plta), { className: 'lp-map-popup' });
                lpMarkerRegistry[plta.slug] = { marker: marker, plta: plta };
            } else {
                entry.marker.setIcon(buildLpIcon(plta.status));
                entry.marker.setTooltipContent(buildLpTooltip(plta));
                entry.marker.setPopupContent(buildLpPopup(plta));
                entry.plta = plta;
            }
        });

        if (bounds.length && !window._lpMapFitted) {
            lpMap.fitBounds(bounds, { padding: [36, 36], maxZoom: 11 });
            window._lpMapFitted = true;
        }
    }

    // Filter markers by status
    var currentStatusFilter = 'all';

    function applyMapFilter(status) {
        currentStatusFilter = status;
        Object.keys(lpMarkerRegistry).forEach(function (slug) {
            var entry = lpMarkerRegistry[slug];
            if (status === 'all' || entry.plta.status === status) {
                if (!lpMap.hasLayer(entry.marker)) entry.marker.addTo(lpMap);
            } else {
                if (lpMap.hasLayer(entry.marker)) lpMap.removeLayer(entry.marker);
            }
        });
    }

    var statusFilter = document.getElementById('lp-map-status-filter');
    if (statusFilter) {
        statusFilter.addEventListener('change', function () {
            applyMapFilter(this.value);
        });
    }

    function initLpMap() {
        var mapEl = document.getElementById('lp-plta-map');
        if (!mapEl || typeof L === 'undefined') return;

        lpMap = L.map(mapEl, {
            zoomControl: true,
            attributionControl: true,
            minZoom: 7,
            maxZoom: 17,
        }).setView([-8.0, 112.1], 9);

        // Satellite basemap
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri — Esri, Maxar, Earthstar Geographics',
            maxZoom: 18,
        }).addTo(lpMap);

        // Label overlay
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}', {
            maxZoom: 18,
            opacity: 0.9,
        }).addTo(lpMap);

        // Handle resize
        function refreshSize() {
            requestAnimationFrame(function () { lpMap.invalidateSize({ animate: false }); });
        }
        window.addEventListener('resize', refreshSize, { passive: true });
        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(refreshSize).observe(mapEl);
        }

        // Render initial data
        var initData = window.SIGAP_LANDING_MAP_DATA || [];
        renderLpMarkers(initData);
    }

    /* Inject keyframe for abnormal ring animation */
    (function () {
        var style = document.createElement('style');
        style.textContent = '@keyframes lp-ring { 0%{transform:scale(1);opacity:0.5} 100%{transform:scale(1.8);opacity:0} }';
        document.head.appendChild(style);
    })();

    /* ── 6. LIVE STATS POLLING ────────────────────────────────── */
    function updateLpStats(stats) {
        if (!stats) return;
        var mapping = {
            total_plta:      'lp-stat-total-plta',
            total_equipment: 'lp-stat-total-equip',
            normal:          'lp-stat-normal',
            abnormal:        'lp-stat-abnormal',
            not_ready:       'lp-stat-not-ready',
        };
        Object.keys(mapping).forEach(function (key) {
            var el = document.getElementById(mapping[key]);
            if (!el) return;
            var newVal = String(stats[key] !== undefined ? stats[key] : '—');
            if (el.textContent !== newVal) {
                el.textContent = newVal;
                el.classList.add('lp-stat-pulse');
                setTimeout(function () { el.classList.remove('lp-stat-pulse'); }, 700);
            }
        });
    }

    function updateLpMapData(pltaList) {
        if (!pltaList || !lpMap) return;
        renderLpMarkers(pltaList);
    }

    function updateLastRefresh(ts) {
        var el = document.getElementById('lp-last-refresh');
        if (el && ts) el.textContent = ts;
    }

    var statsUrl = window.SIGAP_LANDING_STATS_URL || null;
    if (statsUrl) {
        // Poll tiap 60 detik (landing page tidak butuh refresh secepat dashboard)
        setInterval(function () {
            fetch(statsUrl, { headers: { 'Accept': 'application/json' } })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (payload) {
                    if (!payload) return;
                    updateLpStats(payload.stats);
                    updateLpMapData(payload.plta || []);
                    if (payload.last_upload) {
                        var el = document.getElementById('lp-last-upload-time');
                        if (el) el.textContent = payload.last_upload;
                    }
                    updateLastRefresh(payload.updated_at);
                })
                .catch(function () { /* silent fail */ });
        }, 60000);
    }

    /* ── 7. INIT ON DOM READY ─────────────────────────────────── */
    function init() {
        // Leaflet may be loaded async from CDN; wait until it's ready
        if (typeof L !== 'undefined') {
            initLpMap();
        } else {
            // Retry after short delay (CDN still loading)
            var attempts = 0;
            var retryInterval = setInterval(function () {
                attempts++;
                if (typeof L !== 'undefined') {
                    clearInterval(retryInterval);
                    initLpMap();
                } else if (attempts > 20) {
                    clearInterval(retryInterval);
                }
            }, 200);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
