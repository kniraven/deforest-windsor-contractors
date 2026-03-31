import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
window.L = L;

/*
|--------------------------------------------------------------------------
| DEBUG
|--------------------------------------------------------------------------
|
| true  = show map logs in browser dev tools
| false = hide map logs
|
*/

const DEBUG_MAP = true;

/*
|--------------------------------------------------------------------------
| EASY SETTINGS TO EDIT
|--------------------------------------------------------------------------
|
| Leaflet zoom rule:
| - Bigger zoom number = more zoomed in
| - Smaller zoom number = more zoomed out
|
| This file uses one single dynamic framing rule:
| 1. Take the real DeForest/Windsor service area bounds
| 2. Pad them slightly so the view sits just outside the boundaries
| 3. Fit that padded area to the CURRENT map container size
|
*/

const MAP_SETTINGS = {
    // Temporary center used before the real fit runs.
    startCenter: [43.2350, -89.3150],

    // Temporary zoom used before the real fit runs.
    startZoom: 11,

    // Higher values make dragging harder outside max bounds.
    maxBoundsViscosity: 1.0,

    // Smaller values allow finer zoom precision.
    zoomSnap: 0.1,

    // Changes how much each zoom step moves.
    zoomDelta: 0.25,

    // Raises or lowers the maximum tile zoom.
    tileLayerMaxZoom: 19,

    // Changes the maximum popup width.
    popupMaxWidth: 240,
};

const DRAW_ORDER = {
    // Controls the render order of the black fog layer.
    maskPane: 430,

    // Controls the render order of the Windsor boundary.
    windsorBoundaryPane: 450,

    // Controls the render order of the DeForest boundary.
    deforestBoundaryPane: 460,
};

const BOUNDARY_REQUEST = {
    // Changes which municipal boundaries are requested.
    whereClause: "NAME IN ('Village of DeForest','Village of Windsor')",

    // Changes which non-geometry fields come back.
    outFields: 'NAME,C_T_V',

    // Changes which ArcGIS endpoint is queried.
    serviceUrl:
        'https://services6.arcgis.com/SImUBTAEkgDmXQiR/ArcGIS/rest/services/Dane_County_Municipal_Boundaries/FeatureServer/0/query',
};

const FOG_SETTINGS = {
    // Bigger values push the black fog farther past the boundaries.
    bufferRatio: 0.80,

    // Changes the outside fog color.
    fillColor: '#000000',

    // Higher values make the outside fog more opaque.
    fillOpacity: 0.96,
};

const VIEWPORT_SETTINGS = {
    // Bigger values show a little more area outside the real service boundaries.
    visibleAreaBufferRatio: 0.05,

    // Pixel padding applied when fitting the visible area to the map container.
    fitPaddingPx: [0, 0],
};

const TIMING_SETTINGS = {
    // Higher values wait longer for the container size to settle.
    stableFramesRequired: 3,

    // Higher values wait longer before forcing a refresh anyway.
    maxFramesToWait: 30,

    // Higher values group repeated refresh requests together more aggressively.
    refreshDebounceMs: 30,

    // Allows tiny pixel differences between DOM size and Leaflet size.
    sizeMatchTolerancePx: 2,
};

// Builds the custom marker icon used for each listing point.
function createKniravenMarkerIcon() {
    return L.divIcon({
        className: 'kniraven-marker',
        html: `
            <div class="kniraven-marker__outer">
                <div class="kniraven-marker__ring">
                    <div class="kniraven-marker__core"></div>
                </div>
            </div>
        `,
        // Changes the overall marker size.
        iconSize: [24, 24],

        // Changes which point sits on the map coordinate.
        iconAnchor: [12, 12],

        // Changes where the popup opens relative to the marker.
        popupAnchor: [0, -10],
    });
}

// Creates a Leaflet pane so layers can render in a controlled order.
function createMapPane(map, paneName, zIndex) {
    map.createPane(paneName);
    map.getPane(paneName).style.zIndex = String(zIndex);
}

// Injects the one-time custom CSS used by this map.
function injectKniravenMapStyles() {
    // Prevents duplicate style injection.
    if (document.getElementById('kniraven-map-theme-styles')) {
        return;
    }

    const style = document.createElement('style');
    style.id = 'kniraven-map-theme-styles';
    style.textContent = `
        .leaflet-container {
            background: #e5e7eb;
        }

        .kniraven-marker {
            background: transparent;
            border: 0;
        }

        .kniraven-marker__outer {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
        }

        .kniraven-marker__ring {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 18px;
            height: 18px;
            border-radius: 9999px;
            background: rgba(245, 158, 11, 0.16);
            border: 2px solid rgba(245, 158, 11, 0.95);
            box-shadow:
                0 0 0 3px rgba(124, 58, 237, 0.24),
                0 0 12px rgba(124, 58, 237, 0.36),
                0 0 16px rgba(245, 158, 11, 0.18);
        }

        .kniraven-marker__core {
            width: 8px;
            height: 8px;
            border-radius: 9999px;
            background: #7c3aed;
            box-shadow: 0 0 8px rgba(124, 58, 237, 0.65);
        }

        .leaflet-popup.kniraven-popup .leaflet-popup-content-wrapper {
            background: #111827;
            color: #e5e7eb;
            border: 1px solid rgba(245, 158, 11, 0.45);
            border-radius: 14px;
            box-shadow:
                0 10px 25px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(124, 58, 237, 0.12);
        }

        .leaflet-popup.kniraven-popup .leaflet-popup-tip {
            background: #111827;
        }

        .leaflet-popup.kniraven-popup .leaflet-popup-close-button {
            color: #cbd5e1;
        }

        .leaflet-popup.kniraven-popup .leaflet-popup-content {
            margin: 12px 14px;
            line-height: 1.5;
        }

        .kniraven-popup-card strong {
            color: #f8fafc;
            display: inline-block;
            margin-bottom: 4px;
        }

        .kniraven-popup-card a {
            color: #f59e0b;
            font-weight: 600;
            text-decoration: none;
        }

        .kniraven-popup-card a:hover {
            text-decoration: underline;
        }

        .kniraven-map-legend {
            background: rgba(17, 24, 39, 0.95);
            color: #e5e7eb;
            padding: 10px 12px;
            border: 1px solid rgba(124, 58, 237, 0.3);
            border-radius: 12px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.25);
            line-height: 1.5;
            font-size: 13px;
            min-width: 140px;
        }

        .kniraven-map-legend__title {
            font-weight: 700;
            margin-bottom: 6px;
            color: #f8fafc;
        }

        .kniraven-map-legend__row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 4px;
        }

        .kniraven-map-legend__swatch {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 3px;
        }

        .kniraven-map-legend__swatch--deforest {
            background: rgba(124, 58, 237, 0.16);
            border: 2px solid #7c3aed;
        }

        .kniraven-map-legend__swatch--windsor {
            background: rgba(245, 158, 11, 0.16);
            border: 2px solid #f59e0b;
        }
    `;

    document.head.appendChild(style);
}

document.addEventListener('DOMContentLoaded', async () => {
    const mapElement = document.getElementById('listing-map');

    if (!mapElement || !window.listingMapData) {
        return;
    }

    injectKniravenMapStyles();

    const map = L.map('listing-map', {
        maxBoundsViscosity: MAP_SETTINGS.maxBoundsViscosity,
        zoomSnap: MAP_SETTINGS.zoomSnap,
        zoomDelta: MAP_SETTINGS.zoomDelta,
    }).setView(MAP_SETTINGS.startCenter, MAP_SETTINGS.startZoom);

    mapElement.style.background = '#e5e7eb';

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: MAP_SETTINGS.tileLayerMaxZoom,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    map.attributionControl.setPrefix(false);

    const markerLayer = L.featureGroup().addTo(map);

    const state = {
        // Exact combined DeForest/Windsor bounds.
        serviceAreaBounds: null,

        // Large padded bounds used only for black fog and drag limits.
        fogBounds: null,

        // Prevents refreshes until map geometry is ready.
        boundariesReady: false,

        // Debounces repeated refresh requests.
        refreshTimeoutId: null,

        // Cancels older refresh runs when a newer one starts.
        activeRefreshToken: 0,

        // Watches the real rendered map size so refreshes happen after actual size changes.
        resizeObserver: null,

        // Helps trace refresh order.
        refreshRequestSequence: 0,
        appliedRefreshSequence: 0,

        // Stores the original min zoom before dynamic updates change it.
        baseMinZoom: map.getMinZoom(),

        // Stores the last good calculated fit zoom.
        lastKnownFitZoom: null,
    };

    function debugLog(label, data = null) {
        if (!DEBUG_MAP) {
            return;
        }

        if (data === null) {
            console.debug(`[Kniraven Map] ${label}`);
            return;
        }

        console.debug(`[Kniraven Map] ${label}`, data);
    }

    function roundNumber(value, decimals = 3) {
        if (typeof value !== 'number' || !Number.isFinite(value)) {
            return value;
        }

        return Number(value.toFixed(decimals));
    }

    // Reads the real DOM size of the map element.
    function getDomContainerSize() {
        const rect = mapElement.getBoundingClientRect();

        return {
            width: Math.round(rect.width),
            height: Math.round(rect.height),
        };
    }

    // Reads Leaflet's internal current map size.
    function getLeafletContainerSize() {
        const size = map.getSize();

        return {
            width: Math.round(size.x),
            height: Math.round(size.y),
        };
    }

    function hasRealSize(size) {
        return size.width > 0 && size.height > 0;
    }

    function sizesMatch(sizeA, sizeB) {
        return (
            Math.abs(sizeA.width - sizeB.width) <= TIMING_SETTINGS.sizeMatchTolerancePx &&
            Math.abs(sizeA.height - sizeB.height) <= TIMING_SETTINGS.sizeMatchTolerancePx
        );
    }

    function invalidateMapSize({ pan = false } = {}) {
        map.invalidateSize({
            pan,
            debounceMoveend: true,
        });
    }

    function getServiceAreaCenter() {
        if (!state.serviceAreaBounds) {
            return null;
        }

        const center = state.serviceAreaBounds.getCenter();

        return [center.lat, center.lng];
    }

    // Returns the slightly padded bounds used for both visible framing and min zoom.
    function getVisibleAreaBounds() {
        if (!state.serviceAreaBounds) {
            return null;
        }

        return state.serviceAreaBounds.pad(VIEWPORT_SETTINGS.visibleAreaBufferRatio);
    }

    function getSafeFallbackZoom() {
        const candidates = [
            state.lastKnownFitZoom,
            map.getZoom(),
            map.getMinZoom(),
            state.baseMinZoom,
            MAP_SETTINGS.startZoom,
        ];

        const safeZoom = candidates.find(
            (value) => typeof value === 'number' && Number.isFinite(value)
        );

        return typeof safeZoom === 'number' ? safeZoom : MAP_SETTINGS.startZoom;
    }

    // Returns the one dynamic zoom used for both initial view and min zoom.
    // Important:
    // Temporarily reset min zoom before calculating so old modal min zoom
    // cannot block the smaller close-state fit zoom.
    function getVisibleAreaFitZoom() {
        const visibleAreaBounds = getVisibleAreaBounds();

        if (!visibleAreaBounds) {
            return null;
        }

        const previousMinZoom = map.getMinZoom();
        const shouldTemporarilyResetMinZoom = previousMinZoom !== state.baseMinZoom;

        let zoom = null;

        try {
            if (shouldTemporarilyResetMinZoom) {
                map.setMinZoom(state.baseMinZoom);
            }

            zoom = map.getBoundsZoom(
                visibleAreaBounds,
                false,
                VIEWPORT_SETTINGS.fitPaddingPx
            );

            if (typeof zoom !== 'number' || !Number.isFinite(zoom)) {
                throw new Error('getBoundsZoom returned an invalid zoom value.');
            }

            state.lastKnownFitZoom = zoom;

            debugLog('Calculated visible-area fit zoom', {
                domSize: getDomContainerSize(),
                leafletSize: getLeafletContainerSize(),
                bufferRatio: VIEWPORT_SETTINGS.visibleAreaBufferRatio,
                paddingPx: VIEWPORT_SETTINGS.fitPaddingPx,
                previousMinZoom,
                baseMinZoom: state.baseMinZoom,
                zoom,
            });

            return zoom;
        } catch (error) {
            const fallbackZoom = getSafeFallbackZoom();

            console.error('[Kniraven Map] Failed to calculate visible-area fit zoom.', {
                error,
                previousMinZoom,
                baseMinZoom: state.baseMinZoom,
                fallbackZoom,
                domSize: getDomContainerSize(),
                leafletSize: getLeafletContainerSize(),
            });

            return fallbackZoom;
        } finally {
            try {
                if (shouldTemporarilyResetMinZoom) {
                    map.setMinZoom(previousMinZoom);
                }
            } catch (restoreError) {
                console.error('[Kniraven Map] Failed to restore previous min zoom after fit calculation.', {
                    restoreError,
                    previousMinZoom,
                });
            }
        }
    }

    function getMapDebugSnapshot() {
        const domSize = getDomContainerSize();
        const leafletSize = getLeafletContainerSize();
        const visibleAreaBounds = getVisibleAreaBounds();
        const center = map.getCenter();

        let fitZoom = null;
        let fitZoomError = null;

        if (visibleAreaBounds) {
            try {
                const previousMinZoom = map.getMinZoom();
                const shouldTemporarilyResetMinZoom = previousMinZoom !== state.baseMinZoom;

                if (shouldTemporarilyResetMinZoom) {
                    map.setMinZoom(state.baseMinZoom);
                }

                fitZoom = map.getBoundsZoom(
                    visibleAreaBounds,
                    false,
                    VIEWPORT_SETTINGS.fitPaddingPx
                );

                if (shouldTemporarilyResetMinZoom) {
                    map.setMinZoom(previousMinZoom);
                }
            } catch (error) {
                fitZoomError = String(error);
            }
        }

        return {
            domSize,
            leafletSize,
            domMatchesLeaflet: sizesMatch(domSize, leafletSize),
            currentZoom: roundNumber(map.getZoom()),
            currentMinZoom: roundNumber(map.getMinZoom()),
            baseMinZoom: roundNumber(state.baseMinZoom),
            lastKnownFitZoom: roundNumber(state.lastKnownFitZoom),
            currentCenter: [
                roundNumber(center.lat, 6),
                roundNumber(center.lng, 6),
            ],
            fitZoom: roundNumber(fitZoom),
            fitZoomError,
            boundariesReady: state.boundariesReady,
            hasServiceAreaBounds: Boolean(state.serviceAreaBounds),
            activeRefreshToken: state.activeRefreshToken,
            refreshRequestSequence: state.refreshRequestSequence,
            appliedRefreshSequence: state.appliedRefreshSequence,
            pendingRefreshTimeout: state.refreshTimeoutId !== null,
        };
    }

    function getBoundaryUrl() {
        const params = new URLSearchParams({
            where: BOUNDARY_REQUEST.whereClause,
            outFields: BOUNDARY_REQUEST.outFields,
            returnGeometry: 'true',
            outSR: '4326',
            f: 'geojson',
        });

        return `${BOUNDARY_REQUEST.serviceUrl}?${params.toString()}`;
    }

    function geometryToHoleRings(geometry) {
        if (!geometry || !geometry.coordinates) {
            return [];
        }

        if (geometry.type === 'Polygon') {
            return [
                geometry.coordinates[0].map(([lng, lat]) => [lat, lng]),
            ];
        }

        if (geometry.type === 'MultiPolygon') {
            return geometry.coordinates.map((polygon) =>
                polygon[0].map(([lng, lat]) => [lat, lng])
            );
        }

        return [];
    }

    function buildMaskLayer(boundaryGeoJson, fogBounds) {
        const southWest = fogBounds.getSouthWest();
        const northEast = fogBounds.getNorthEast();

        const outerRing = [
            [southWest.lat, southWest.lng],
            [northEast.lat, southWest.lng],
            [northEast.lat, northEast.lng],
            [southWest.lat, northEast.lng],
        ];

        const holes = boundaryGeoJson.features.flatMap((feature) =>
            geometryToHoleRings(feature.geometry)
        );

        return L.polygon([outerRing, ...holes], {
            pane: 'maskPane',
            stroke: false,
            fillColor: FOG_SETTINGS.fillColor,
            fillOpacity: FOG_SETTINGS.fillOpacity,
            fillRule: 'evenodd',
            interactive: false,
            bubblingMouseEvents: false,
        });
    }

    function createBoundaryLayer(featureCollection, paneName, color, fillColor) {
        return L.geoJSON(featureCollection, {
            pane: paneName,
            interactive: false,
            bubblingMouseEvents: false,
            style: {
                color,
                weight: 3.5,
                opacity: 0.95,
                fillColor,
                fillOpacity: 0.06,
            },
        });
    }

    function addLegend() {
        const legend = L.control({ position: 'bottomleft' });

        legend.onAdd = function () {
            const div = L.DomUtil.create('div', 'kniraven-map-legend');

            div.innerHTML = `
                <div class="kniraven-map-legend__title">Regions</div>
                <div class="kniraven-map-legend__row">
                    <span class="kniraven-map-legend__swatch kniraven-map-legend__swatch--deforest"></span>
                    <span>DeForest</span>
                </div>
                <div class="kniraven-map-legend__row">
                    <span class="kniraven-map-legend__swatch kniraven-map-legend__swatch--windsor"></span>
                    <span>Windsor</span>
                </div>
            `;

            return div;
        };

        legend.addTo(map);
    }

    function startContainerResizeObserver() {
        if (typeof ResizeObserver !== 'function') {
            debugLog('ResizeObserver not available; manual refresh hooks will be used');
            return;
        }

        state.resizeObserver = new ResizeObserver((entries) => {
            const entry = entries[0];

            if (!entry) {
                return;
            }

            const nextWidth = Math.round(entry.contentRect.width);
            const nextHeight = Math.round(entry.contentRect.height);

            debugLog('ResizeObserver detected map size change', {
                width: nextWidth,
                height: nextHeight,
                snapshot: getMapDebugSnapshot(),
            });

            queueViewportRefresh('container-resize');
        });

        state.resizeObserver.observe(mapElement);
    }

    // Builds the one viewport rule used everywhere.
    function buildViewport() {
        const center = getServiceAreaCenter();
        const visibleAreaBounds = getVisibleAreaBounds();
        const fitZoom = getVisibleAreaFitZoom();

        if (!center || !visibleAreaBounds || fitZoom === null) {
            return null;
        }

        return {
            center,
            bounds: visibleAreaBounds,
            minZoom: fitZoom,
            zoom: fitZoom,
        };
    }

    // Applies the current dynamic viewport to the map.
    // Uses fitBounds instead of setView so both zoom and centering are recalculated
    // from the real current container size.
    function applyViewport(reason, requestId = null) {
        if (!state.fogBounds) {
            return;
        }

        const snapshotBeforeInvalidate = getMapDebugSnapshot();

        try {
            // Force Leaflet to read the latest real DOM size before fitting.
            invalidateMapSize({ pan: true });

            const snapshotAfterInvalidate = getMapDebugSnapshot();
            const viewport = buildViewport();

            if (!viewport) {
                throw new Error('Viewport could not be built.');
            }

            if (!viewport.bounds || typeof viewport.bounds.isValid !== 'function' || !viewport.bounds.isValid()) {
                throw new Error('Viewport bounds are invalid.');
            }

            if (typeof viewport.minZoom !== 'number' || !Number.isFinite(viewport.minZoom)) {
                throw new Error('Viewport min zoom is invalid.');
            }

            // Temporarily drop the min zoom clamp so fitBounds can place the view correctly.
            map.setMinZoom(state.baseMinZoom);
            map.setMaxBounds(state.fogBounds);

            map.fitBounds(viewport.bounds, {
                padding: VIEWPORT_SETTINGS.fitPaddingPx,
                animate: false,
            });

            const actualCenterAfterFit = map.getCenter();
            const actualZoomAfterFit = map.getZoom();

            if (
                !actualCenterAfterFit ||
                typeof actualCenterAfterFit.lat !== 'number' ||
                typeof actualCenterAfterFit.lng !== 'number' ||
                !Number.isFinite(actualZoomAfterFit)
            ) {
                throw new Error('fitBounds produced an invalid center or zoom.');
            }

            map.setMinZoom(viewport.minZoom);

            state.lastKnownFitZoom = viewport.minZoom;
            state.appliedRefreshSequence = requestId ?? (state.appliedRefreshSequence + 1);

            debugLog('Applied viewport', {
                requestId,
                reason,
                snapshotBeforeInvalidate,
                snapshotAfterInvalidate,
                appliedViewport: {
                    center: viewport.center,
                    zoom: roundNumber(viewport.zoom),
                    minZoom: roundNumber(viewport.minZoom),
                },
                actualAfterFit: {
                    center: [
                        roundNumber(actualCenterAfterFit.lat, 6),
                        roundNumber(actualCenterAfterFit.lng, 6),
                    ],
                    zoom: roundNumber(actualZoomAfterFit),
                },
                snapshotAfterApply: getMapDebugSnapshot(),
            });
        } catch (error) {
            const fallbackCenter = getServiceAreaCenter() ?? MAP_SETTINGS.startCenter;
            const fallbackZoom = getSafeFallbackZoom();
            const fallbackBounds = getVisibleAreaBounds();

            console.error('[Kniraven Map] Failed to apply viewport. Attempting fallback.', {
                error,
                requestId,
                reason,
                fallbackCenter,
                fallbackZoom,
                snapshotBeforeInvalidate,
                snapshotAtFailure: getMapDebugSnapshot(),
            });

            try {
                invalidateMapSize({ pan: true });
                map.setMaxBounds(state.fogBounds);
                map.setMinZoom(state.baseMinZoom);

                if (fallbackBounds && typeof fallbackBounds.isValid === 'function' && fallbackBounds.isValid()) {
                    map.fitBounds(fallbackBounds, {
                        padding: VIEWPORT_SETTINGS.fitPaddingPx,
                        animate: false,
                    });

                    const fittedZoom = map.getZoom();

                    if (typeof fittedZoom === 'number' && Number.isFinite(fittedZoom)) {
                        map.setMinZoom(fittedZoom);
                        state.lastKnownFitZoom = fittedZoom;
                    }
                } else {
                    map.setView(fallbackCenter, fallbackZoom, {
                        animate: false,
                    });
                    map.setMinZoom(fallbackZoom);
                    state.lastKnownFitZoom = fallbackZoom;
                }

                debugLog('Applied fallback viewport after failure', {
                    requestId,
                    reason,
                    fallbackCenter,
                    fallbackZoom,
                    snapshotAfterFallback: getMapDebugSnapshot(),
                });
            } catch (fallbackError) {
                console.error('[Kniraven Map] Fallback viewport application also failed.', {
                    fallbackError,
                    requestId,
                    reason,
                    fallbackCenter,
                    fallbackZoom,
                    snapshotAfterFallbackFailure: getMapDebugSnapshot(),
                });
            }
        }
    }

    // Waits until the DOM size is stable and Leaflet has caught up to that size.
    function waitForReadyContainer(refreshToken, requestId, callback) {
        let frameCount = 0;
        let stableFrames = 0;
        let previousDomSizeKey = null;

        function step() {
            if (refreshToken !== state.activeRefreshToken) {
                debugLog('Cancelled stale refresh', {
                    requestId,
                    refreshToken,
                    activeRefreshToken: state.activeRefreshToken,
                });
                return;
            }

            frameCount += 1;
            invalidateMapSize({ pan: false });

            const domSize = getDomContainerSize();
            const leafletSize = getLeafletContainerSize();
            const domSizeKey = `${domSize.width}x${domSize.height}`;
            const leafletMatchesDom = sizesMatch(domSize, leafletSize);

            if (hasRealSize(domSize) && domSizeKey === previousDomSizeKey && leafletMatchesDom) {
                stableFrames += 1;
            } else {
                stableFrames = 0;
            }

            previousDomSizeKey = domSizeKey;

            debugLog('Waiting for ready container', {
                requestId,
                frameCount,
                stableFrames,
                domSize,
                leafletSize,
                leafletMatchesDom,
            });

            const ready = stableFrames >= TIMING_SETTINGS.stableFramesRequired;
            const timedOut = frameCount >= TIMING_SETTINGS.maxFramesToWait;

            if (ready || timedOut) {
                debugLog('Container ready for refresh', {
                    requestId,
                    frameCount,
                    stableFrames,
                    domSize,
                    leafletSize,
                    leafletMatchesDom,
                    timedOut,
                });

                callback();
                return;
            }

            requestAnimationFrame(step);
        }

        requestAnimationFrame(step);
    }

    // Queues one debounced map refresh.
    function queueViewportRefresh(reason) {
        if (!state.boundariesReady) {
            debugLog('Skipped refresh because boundaries are not ready', {
                reason,
            });
            return;
        }

        const requestId = state.refreshRequestSequence + 1;
        state.refreshRequestSequence = requestId;

        if (state.refreshTimeoutId !== null) {
            clearTimeout(state.refreshTimeoutId);
        }

        debugLog('Queued viewport refresh', {
            requestId,
            reason,
            snapshotAtQueueTime: getMapDebugSnapshot(),
        });

        state.refreshTimeoutId = setTimeout(function () {
            state.refreshTimeoutId = null;
            state.activeRefreshToken += 1;

            const refreshToken = state.activeRefreshToken;

            debugLog('Starting queued viewport refresh', {
                requestId,
                reason,
                refreshToken,
                snapshotAtStart: getMapDebugSnapshot(),
            });

            waitForReadyContainer(refreshToken, requestId, function () {
                applyViewport(reason, requestId);
            });
        }, TIMING_SETTINGS.refreshDebounceMs);
    }

    // Expose helpers so Blade can request refreshes and snapshots.
    window.kniravenListingMap = map;

    window.kniravenRefreshMapViewport = function ({ reason = 'manual-call' } = {}) {
        debugLog('External refresh request received', {
            reason,
            snapshotAtExternalRequest: getMapDebugSnapshot(),
        });

        queueViewportRefresh(reason);
    };

    window.kniravenGetMapDebugSnapshot = function (label = 'manual-snapshot') {
        const snapshot = getMapDebugSnapshot();

        debugLog('External snapshot request received', {
            label,
            snapshot,
        });

        return snapshot;
    };

    window.listingMapData.forEach((listing) => {
        const marker = L.marker([listing.latitude, listing.longitude], {
            icon: createKniravenMarkerIcon(),
        }).addTo(markerLayer);

        marker.bindPopup(
            `<div class="kniraven-popup-card"><strong>${listing.name}</strong><br><a href="${listing.url}">View Details</a></div>`,
            {
                className: 'kniraven-popup',
                closeButton: true,
                maxWidth: MAP_SETTINGS.popupMaxWidth,
            }
        );
    });

    createMapPane(map, 'maskPane', DRAW_ORDER.maskPane);
    createMapPane(map, 'windsorBoundaryPane', DRAW_ORDER.windsorBoundaryPane);
    createMapPane(map, 'deforestBoundaryPane', DRAW_ORDER.deforestBoundaryPane);

    try {
        const response = await fetch(getBoundaryUrl());

        if (!response.ok) {
            throw new Error(`Boundary request failed with status ${response.status}`);
        }

        const boundaryGeoJson = await response.json();

        if (!boundaryGeoJson.features || boundaryGeoJson.features.length === 0) {
            throw new Error('Boundary request returned no matching features.');
        }

        const deforestFeatures = boundaryGeoJson.features.filter(
            (feature) => feature?.properties?.NAME === 'Village of DeForest'
        );

        const windsorFeatures = boundaryGeoJson.features.filter(
            (feature) => feature?.properties?.NAME === 'Village of Windsor'
        );

        const boundaryGroup = L.featureGroup().addTo(map);

        if (windsorFeatures.length > 0) {
            const windsorLayer = createBoundaryLayer(
                {
                    type: 'FeatureCollection',
                    features: windsorFeatures,
                },
                'windsorBoundaryPane',
                '#f59e0b',
                '#f59e0b'
            );

            windsorLayer.addTo(map);
            boundaryGroup.addLayer(windsorLayer);
        }

        if (deforestFeatures.length > 0) {
            const deforestLayer = createBoundaryLayer(
                {
                    type: 'FeatureCollection',
                    features: deforestFeatures,
                },
                'deforestBoundaryPane',
                '#7c3aed',
                '#7c3aed'
            );

            deforestLayer.addTo(map);
            boundaryGroup.addLayer(deforestLayer);
        }

        const serviceAreaBounds = boundaryGroup.getBounds();

        if (serviceAreaBounds.isValid()) {
            const fogBounds = serviceAreaBounds.pad(FOG_SETTINGS.bufferRatio);

            state.serviceAreaBounds = serviceAreaBounds;
            state.fogBounds = fogBounds;
            state.boundariesReady = true;

            const maskLayer = buildMaskLayer(boundaryGeoJson, fogBounds).addTo(map);

            maskLayer.bringToFront();
            boundaryGroup.bringToFront();
            markerLayer.bringToFront();

            startContainerResizeObserver();
            queueViewportRefresh('initial-load');
        } else {
            throw new Error('Service area bounds were invalid after building boundary layers.');
        }

        addLegend();
    } catch (error) {
        console.error('Boundary loading failed:', error);

        const markerBounds = markerLayer.getBounds();

        if (markerBounds.isValid()) {
            try {
                map.fitBounds(markerBounds, { padding: [20, 20] });
            } catch (fitError) {
                console.error('[Kniraven Map] Fallback marker fit also failed.', fitError);

                try {
                    map.setView(MAP_SETTINGS.startCenter, MAP_SETTINGS.startZoom, {
                        animate: false,
                    });
                } catch (setViewError) {
                    console.error('[Kniraven Map] Final emergency setView failed.', setViewError);
                }
            }
        } else {
            try {
                map.setView(MAP_SETTINGS.startCenter, MAP_SETTINGS.startZoom, {
                    animate: false,
                });
            } catch (setViewError) {
                console.error('[Kniraven Map] Emergency setView failed with no valid marker bounds.', setViewError);
            }
        }
    }
});