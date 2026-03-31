@php
    // These values read the current filter choices from the URL.
    // This lets the page keep the user's current search and filter state.
    $siteName = 'DeForest Connect';
    $selectedListingTypes = collect((array) request('listing_type', []));
    $selectedLocalPriorities = collect((array) request('local_priority', []));
    $selectedSort = request('sort', 'local_priority');

    // Use one shared purple style for both municipalities.
    $municipalityBadgeClass = 'kn-badge kn-badge-location-deforest';

    $featuredBadgeClass = 'kn-badge kn-badge-featured';

    // Custom gold verified style defined in this file.
    $verifiedBadgeClass = 'kn-badge kn-badge-verified-custom';

    $listingTypeBadgeClass = 'kn-badge kn-badge-type';
    $serviceBadgeClass = 'kn-badge kn-badge-service';

    // Use the same green style for both local badges.
    $localBadgeClass = 'kn-badge kn-badge-owner';
    $ownerBadgeClass = 'kn-badge kn-badge-owner';

    $tagBadgeClass = 'kn-tag-pill';
    $moreTagsBadgeClass = 'kn-tag-pill kn-tag-pill--more';
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $siteName }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="icon" href="{{ asset('favicon.ico') }}">

    <style>
        /* Hide the dark backdrop until the map modal is opened. */
        #map-modal-backdrop {
            display: none;
            pointer-events: none;
        }

        /* Keep the normal map wrapper in the page flow by default. */
        #listing-map-wrapper {
            position: relative;
            z-index: 1;
        }

        /* Hide the close button until the map modal is opened. */
        #listing-map-close {
            display: none;
        }

        /* This holds the map and helps center it in modal view. */
        #listing-map-modal-stage {
            position: relative;
        }

        /* Stop the page from scrolling while the map modal is open. */
        body.map-modal-open {
            overflow: hidden;
        }

        /* Show a dark overlay behind the expanded map. */
        body.map-modal-open #map-modal-backdrop {
            display: block;
            position: fixed;
            inset: 0;
            background: rgba(4, 4, 10, 0.82);
            pointer-events: none;
        }

        /* Turn the normal map wrapper into a centered modal box. */
        body.map-modal-open #listing-map-wrapper {
            position: fixed;
            top: 50%;
            left: 50%;
            width: 90vw;
            height: 90vh;
            transform: translate(-50%, -50%);
            z-index: 9999;
            border-radius: 1rem;
            border: 1px solid rgba(155, 114, 176, 0.32);
            box-shadow:
                0 28px 60px rgba(0, 0, 0, 0.6),
                0 0 24px rgba(96, 21, 108, 0.22);
            background: linear-gradient(180deg, rgba(16, 16, 24, 0.98), rgba(10, 10, 16, 0.99));
            padding: 1rem;
            pointer-events: auto;
        }

        /* Let the map sit centered inside the modal area. */
        body.map-modal-open #listing-map-modal-stage {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Give the expanded map a border and rounded corners. */
        body.map-modal-open #listing-map {
            border-radius: 0.75rem;
            border: 1px solid rgba(155, 114, 176, 0.22);
            overflow: hidden;
            pointer-events: auto;
            background: #090910;
        }

        /* Show the close button only while the map is expanded. */
        body.map-modal-open #listing-map-close {
            display: inline-flex;
            position: absolute;
            top: 0.75rem;
            right: 0.75rem;
            z-index: 10000;
            width: 2.5rem;
            height: 2.5rem;
            align-items: center;
            justify-content: center;
            border-radius: 9999px;
            font-size: 1.25rem;
            line-height: 1;
            color: #f8fafc;
            border: 1px solid rgba(148, 163, 184, 0.28);
            background: rgba(10, 10, 16, 0.88);
        }

        /* Gold verified badge with good text contrast. */
        .kn-badge-verified-custom {
            color: #f7e7a8 !important;
            border-color: rgba(234, 179, 8, 0.52) !important;
            background:
                linear-gradient(180deg, rgba(110, 76, 8, 0.92) 0%, rgba(72, 50, 8, 0.96) 100%) !important;
            box-shadow:
                0 0 0 1px rgba(255, 255, 255, 0.02) inset,
                0 0 14px rgba(234, 179, 8, 0.12);
        }

        /* On smaller screens, make the modal a little tighter. */
        @media (max-width: 640px) {
            body.map-modal-open #listing-map-wrapper {
                width: 92vw;
                height: 92vh;
                padding: 0.75rem;
            }
        }
    </style>
</head>
<body class="kn-page-shell">
    {{-- Dark overlay used when the map is expanded. --}}
    <div id="map-modal-backdrop"></div>

    <div class="mx-auto max-w-6xl px-4 py-4 sm:px-6 sm:py-5 lg:px-8">
        <main class="grid gap-6 lg:grid-cols-5 lg:items-start">
            {{-- Top intro section --}}
            <section class="lg:col-span-3 lg:col-start-3 lg:row-start-1">
                <section class="kn-panel rounded-2xl p-4 sm:p-5">
                    <p class="kn-kicker mb-2">
                        Local-first directory
                    </p>

                    <h1 class="text-2xl font-bold tracking-tight sm:text-3xl">
                        {{ $siteName }}
                    </h1>

                    <p class="kn-body-text mt-3 text-sm leading-6">
                        A local directory built to help neighbors find trusted contractors, services, and community support in the DeForest area. I may add more features in the future!
                    </p>

                    <a
                        href="{{ route('listings.submit.create') }}"
                        class="kn-btn-primary mt-2"
                    >
                        Submit a Listing
                    </a>

                    {{-- Small creator/support panel --}}
                    <div class="kn-panel-subtle mt-4 rounded-2xl p-3">
                        <h2 class="text-sm font-semibold">
                            About the creator
                        </h2>

                        <p class="kn-body-text mt-1 text-sm leading-6">
                            Hi, I’m Nick, also known as Kniraven. I'm into TTRPGs, MMOs, and Magic.
                            <br> I built this site to help people find local help and support local businesses.
                        </p>

                        <div class="mt-3 flex items-center justify-between gap-3">
                            <a
                                href="https://www.paypal.com/paypalme/kniraven"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="kn-btn-support"
                            >
                                Support via PayPal
                            </a>

                            <a
                                href="{{ url('/admin/listings') }}"
                                class="text-[11px] uppercase tracking-[0.24em] text-slate-500 transition hover:text-slate-300"
                                aria-label="Admin"
                                title="Admin"
                            >
                                Admin
                            </a>
                        </div>
                    </div>
                </section>
            </section>

            {{-- Left sidebar with filters and map --}}
            <aside class="lg:col-span-2 lg:col-start-1 lg:row-start-1 lg:row-span-2 lg:sticky lg:top-4 lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto lg:pr-1">
                <div class="space-y-6">
                    {{-- Search and filter panel --}}
                    <section class="kn-panel rounded-2xl p-4 sm:p-5">
                        <div class="mb-3">
                            <h2 class="text-lg font-semibold">
                                Find local businesses
                            </h2>
                        </div>

                        <form id="listing-filters-form" method="GET" action="{{ route('listings.index') }}" class="grid gap-4">
                            <div>
                                <label for="q" class="mb-1 block text-sm font-medium text-slate-200">Search</label>
                                <input
                                    id="q"
                                    name="q"
                                    type="text"
                                    value="{{ request('q') }}"
                                    class="kn-input px-4 py-2.5 text-base"
                                    placeholder="Search by name, type, service, or tag"
                                    data-auto-submit-input
                                >
                            </div>

                            <div>
                                <p class="mb-2 block text-sm font-medium text-slate-200">Filters</p>

                                <div class="flex flex-wrap gap-2">
                                    <label>
                                        <input
                                            type="checkbox"
                                            name="listing_type[]"
                                            value="business"
                                            class="peer sr-only"
                                            @checked($selectedListingTypes->contains('business'))
                                            data-auto-submit-checkbox
                                        >
                                        <span class="kn-filter-pill">
                                            Business
                                        </span>
                                    </label>

                                    <label>
                                        <input
                                            type="checkbox"
                                            name="listing_type[]"
                                            value="individual"
                                            class="peer sr-only"
                                            @checked($selectedListingTypes->contains('individual'))
                                            data-auto-submit-checkbox
                                        >
                                        <span class="kn-filter-pill">
                                            Individual
                                        </span>
                                    </label>

                                    <label>
                                        <input
                                            type="checkbox"
                                            name="listing_type[]"
                                            value="nonprofit"
                                            class="peer sr-only"
                                            @checked($selectedListingTypes->contains('nonprofit'))
                                            data-auto-submit-checkbox
                                        >
                                        <span class="kn-filter-pill">
                                            Nonprofit
                                        </span>
                                    </label>

                                    <label>
                                        <input
                                            type="checkbox"
                                            name="local_priority[]"
                                            value="independent-local"
                                            class="peer sr-only"
                                            @checked($selectedLocalPriorities->contains('independent-local'))
                                            data-auto-submit-checkbox
                                        >
                                        <span class="kn-filter-pill">
                                            Locally Independent
                                        </span>
                                    </label>

                                    <label>
                                        <input
                                            type="checkbox"
                                            name="local_priority[]"
                                            value="owner-local"
                                            class="peer sr-only"
                                            @checked($selectedLocalPriorities->contains('owner-local'))
                                            data-auto-submit-checkbox
                                        >
                                        <span class="kn-filter-pill">
                                            Owner Lives Here
                                        </span>
                                    </label>
                                </div>

                                <p class="kn-muted-text mt-2 text-xs">
                                    If both local-priority filters are selected, listings must match both.
                                </p>
                            </div>
                        </form>
                    </section>

                    {{-- Community map panel --}}
                    <section class="kn-panel rounded-2xl p-4 sm:p-5">
                        <div class="mb-3 flex items-start justify-between gap-3">
                            <div>
                                <h2 class="text-lg font-semibold">
                                    Community map
                                </h2>
                                <p class="kn-muted-text mt-1 text-sm">
                                    DeForest and Windsor service area.
                                </p>
                            </div>

                            <button
                                id="open-map-modal"
                                type="button"
                                class="kn-btn-secondary shrink-0"
                            >
                                Expand Map
                            </button>
                        </div>

                        {{-- This same map element is used for both normal and expanded view. --}}
                        <div id="listing-map-wrapper" class="kn-map-panel rounded-2xl p-0">
                            <button
                                id="listing-map-close"
                                type="button"
                                class="transition"
                                aria-label="Close expanded map"
                            >
                                ×
                            </button>

                            <div id="listing-map-modal-stage">
                                <div
                                    id="listing-map"
                                    class="h-80 w-full overflow-hidden rounded-xl border border-[rgba(155,114,176,0.18)] bg-slate-950 sm:h-96 lg:h-72 xl:h-80"
                                ></div>
                            </div>
                        </div>
                    </section>
                </div>
            </aside>

            {{-- Main results area --}}
            <section class="lg:col-span-3 lg:col-start-3 lg:row-start-2">
                <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <h2 class="text-xl font-semibold">
                            Browse the directory
                        </h2>
                        <p class="kn-muted-text mt-1 text-sm">
                            Explore local listings while keeping the filters and map close by.
                        </p>
                    </div>

                    <div class="w-full sm:w-auto">
                        <label for="sort" class="mb-1 block text-sm font-medium text-slate-200">Sort By</label>
                        <select
                            id="sort"
                            name="sort"
                            form="listing-filters-form"
                            class="kn-select px-4 py-2.5 text-sm"
                            data-auto-submit-select
                        >
                            <option value="local_priority" @selected($selectedSort === 'local_priority')>Local Priority</option>
                            <option value="name_az" @selected($selectedSort === 'name_az')>Name A–Z</option>
                            <option value="name_za" @selected($selectedSort === 'name_za')>Name Z–A</option>
                            <option value="municipality_az" @selected($selectedSort === 'municipality_az')>Municipality A–Z</option>
                            <option value="listing_type_az" @selected($selectedSort === 'listing_type_az')>Listing Type A–Z</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="kn-results-count text-sm font-medium">
                        {{ $listings->count() }} {{ $listings->count() === 1 ? 'listing' : 'listings' }} found
                    </p>
                </div>

                <div class="grid gap-4">
                    @forelse ($listings as $listing)
                        <article @class([
                            'kn-listing-card rounded-2xl p-4 sm:p-5',
                            'kn-listing-card--featured' => $listing->is_featured,
                        ])>
                            <h3 class="kn-listing-title text-xl font-semibold tracking-tight">
                                {{ $listing->display_name }}
                            </h3>

                            <div class="mt-3">
                                <div class="kn-badge-row">
                                    <span class="{{ $municipalityBadgeClass }}">
                                        <span>{{ $listing->municipality }}</span>
                                    </span>

                                    <span class="{{ $listingTypeBadgeClass }}">
                                        {{ ucfirst($listing->listing_type) }}
                                    </span>

                                    <span class="{{ $serviceBadgeClass }}">
                                        {{ $listing->service_type === 'Other' && $listing->other_service_type ? $listing->other_service_type : $listing->service_type }}
                                    </span>
                                </div>
                            </div>

                            <p class="kn-body-text mt-3 text-sm leading-6">
                                {{ $listing->short_description }}
                            </p>

                            @if ($listing->phone || $listing->email)
                                <div class="kn-muted-text mt-4 space-y-1 text-sm">
                                    @if ($listing->phone)
                                        <p>{{ $listing->phone }}</p>
                                    @endif

                                    @if ($listing->email)
                                        <p>{{ $listing->email }}</p>
                                    @endif
                                </div>
                            @endif

                            <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                                <a
                                    href="{{ route('listings.show', $listing) }}"
                                    class="kn-btn-primary"
                                >
                                    View Details
                                </a>

                                @if ($listing->website_url)
                                    <a
                                        href="{{ $listing->website_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="kn-btn-secondary"
                                    >
                                        Visit Website
                                    </a>
                                @endif
                            </div>

                            <div class="mt-4 space-y-2">
                                {{-- Row 1: verified / local badges --}}
                                <div class="kn-badge-row">
                                    @if ($listing->is_verified)
                                        <span class="{{ $verifiedBadgeClass }}">
                                            <span>Verified</span>
                                        </span>
                                    @endif

                                    @if ($listing->is_locally_independent)
                                        <span class="{{ $localBadgeClass }}">
                                            Locally Independent
                                        </span>
                                    @endif

                                    @if ($listing->is_owner_local)
                                        <span class="{{ $ownerBadgeClass }}">
                                            Owner Lives Here
                                        </span>
                                    @endif
                                </div>

                                {{-- Row 2: tags --}}
                                @if ($listing->tags->isNotEmpty())
                                    <div class="kn-badge-row">
                                        @foreach ($listing->tags->take(4) as $tag)
                                            <span class="{{ $tagBadgeClass }}">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach

                                        @if ($listing->tags->count() > 4)
                                            <span class="{{ $moreTagsBadgeClass }}">
                                                +{{ $listing->tags->count() - 4 }} more
                                            </span>
                                        @endif
                                    </div>
                                @endif

                                {{-- Row 3: featured --}}
                                @if ($listing->is_featured)
                                    <div class="kn-badge-row">
                                        <span class="{{ $featuredBadgeClass }}">
                                            <span>Featured Listing</span>
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </article>
                    @empty
                        <div class="kn-empty-state rounded-2xl p-5">
                            <h3 class="text-lg font-semibold text-slate-100">
                                No listings found
                            </h3>
                            <p class="kn-body-text mt-2 text-sm leading-6">
                                Try changing your search or clearing the current filters to see more results.
                            </p>
                            <div class="mt-4">
                                <a
                                    href="{{ route('listings.index') }}"
                                    class="kn-btn-secondary"
                                >
                                    Reset Filters
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Closing information block --}}
                <section class="kn-section-tail mt-6 rounded-2xl p-5">
                    <h2 class="text-lg font-semibold">
                        Built for local trust
                    </h2>
                    <p class="kn-body-text mt-2 text-sm leading-6">
                        This directory is meant to make local discovery easier, highlight nearby providers, and help the community support people who live and work in the area. The map and listings are built to stay focused on the practical local service region instead of trying to cover too much at once.
                    </p>
                </section>
            </section>
        </main>
    </div>

    <script>
    // Send map marker data from Laravel into JavaScript.
    window.listingMapData = @json($mapListings);

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('listing-filters-form');
        const autoSubmitSelects = document.querySelectorAll('[data-auto-submit-select]');
        const autoSubmitCheckboxes = document.querySelectorAll('[data-auto-submit-checkbox]');
        const autoSubmitInput = document.querySelector('[data-auto-submit-input]');
        const openMapModalButton = document.getElementById('open-map-modal');
        const closeMapModalButton = document.getElementById('listing-map-close');
        const mapElement = document.getElementById('listing-map');
        const mapStage = document.getElementById('listing-map-modal-stage');
        const mapWrapper = document.getElementById('listing-map-wrapper');
        const mapModalClass = 'map-modal-open';
        const originalMapWrapperParent = mapWrapper ? mapWrapper.parentNode : null;
        const originalMapWrapperNextSibling = mapWrapper ? mapWrapper.nextSibling : null;

        let resizeTimeoutId = null;
        let openFollowUpTimeoutIds = [];
        let closeDiagnosticTimeoutIds = [];

        function submitFilters() {
            if (form) {
                form.requestSubmit();
            }
        }

        function isMapModalOpen() {
            return document.body.classList.contains(mapModalClass);
        }

        if (form) {
            autoSubmitSelects.forEach(function (element) {
                element.addEventListener('change', submitFilters);
            });

            autoSubmitCheckboxes.forEach(function (element) {
                element.addEventListener('change', submitFilters);
            });

            if (autoSubmitInput) {
                let inputTimeout = null;

                autoSubmitInput.addEventListener('input', function () {
                    clearTimeout(inputTimeout);

                    inputTimeout = setTimeout(function () {
                        submitFilters();
                    }, 350);
                });
            }
        }

        function getRoundedRect(element) {
            if (!element) {
                return null;
            }

            const rect = element.getBoundingClientRect();

            return {
                width: Math.round(rect.width),
                height: Math.round(rect.height),
                top: Math.round(rect.top),
                left: Math.round(rect.left),
            };
        }

        function getComputedValue(element, propertyName) {
            if (!element) {
                return null;
            }

            return window.getComputedStyle(element).getPropertyValue(propertyName);
        }

        function getModalDomSnapshot() {
            return {
                modalOpen: isMapModalOpen(),
                bodyOverflow: getComputedValue(document.body, 'overflow'),
                windowInnerWidth: window.innerWidth,
                windowInnerHeight: window.innerHeight,
                documentClientWidth: document.documentElement.clientWidth,
                documentClientHeight: document.documentElement.clientHeight,
                scrollY: Math.round(window.scrollY),
                wrapperRect: getRoundedRect(mapWrapper),
                stageRect: getRoundedRect(mapStage),
                mapRect: getRoundedRect(mapElement),
                wrapperPosition: getComputedValue(mapWrapper, 'position'),
                wrapperWidth: getComputedValue(mapWrapper, 'width'),
                wrapperHeight: getComputedValue(mapWrapper, 'height'),
                mapComputedWidth: getComputedValue(mapElement, 'width'),
                mapComputedHeight: getComputedValue(mapElement, 'height'),
                mapInlineWidth: mapElement ? mapElement.style.width || '' : '',
                mapInlineHeight: mapElement ? mapElement.style.height || '' : '',
            };
        }

        function logModalSnapshot(label) {
            let mapSnapshot = null;

            if (typeof window.kniravenGetMapDebugSnapshot === 'function') {
                mapSnapshot = window.kniravenGetMapDebugSnapshot(label);
            }

            console.debug(`[Kniraven Modal] ${label}`, {
                dom: getModalDomSnapshot(),
                map: mapSnapshot,
            });
        }

        function clearOpenFollowUpTimeouts() {
            openFollowUpTimeoutIds.forEach(function (timeoutId) {
                clearTimeout(timeoutId);
            });

            openFollowUpTimeoutIds = [];
        }

        function clearCloseDiagnosticTimeouts() {
            closeDiagnosticTimeoutIds.forEach(function (timeoutId) {
                clearTimeout(timeoutId);
            });

            closeDiagnosticTimeoutIds = [];
        }

        // Run after the browser has had time to apply layout changes.
        function runAfterLayoutSettles(callback) {
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    callback();
                });
            });
        }

        // Ask app.js to recalculate the map from its current rendered size.
        function refreshMapViewport(reason) {
            console.debug('[Kniraven Modal] Refresh requested', {
                reason: reason,
                dom: getModalDomSnapshot(),
            });

            if (typeof window.kniravenRefreshMapViewport === 'function') {
                window.kniravenRefreshMapViewport({
                    reason: reason,
                });
            }
        }

        // Keep the expanded map at 4:3 inside the modal stage.
        function sizeExpandedMap() {
            if (!isMapModalOpen() || !mapElement || !mapStage) {
                return;
            }

            const availableWidth = mapStage.clientWidth;
            const availableHeight = mapStage.clientHeight;
            const aspectRatio = 4 / 3;

            let width = availableWidth;
            let height = width / aspectRatio;

            if (height > availableHeight) {
                height = availableHeight;
                width = height * aspectRatio;
            }

            mapElement.style.width = width + 'px';
            mapElement.style.height = height + 'px';

            console.debug('[Kniraven Modal] Sized expanded map', {
                availableWidth: availableWidth,
                availableHeight: availableHeight,
                appliedWidth: width,
                appliedHeight: height,
                dom: getModalDomSnapshot(),
            });
        }

        // After opening, run a few extra refreshes.
        // This handles layout changes that finish a little later,
        // such as scrollbar removal and final modal settling.
        function scheduleOpenFollowUpRefreshes() {
            clearOpenFollowUpTimeouts();

            const followUpDelays = [70, 160, 280];

            followUpDelays.forEach(function (delayMs) {
                const timeoutId = setTimeout(function () {
                    if (!isMapModalOpen()) {
                        return;
                    }

                    console.debug('[Kniraven Modal] Open follow-up checkpoint', {
                        delayMs: delayMs,
                        dom: getModalDomSnapshot(),
                    });

                    sizeExpandedMap();

                    runAfterLayoutSettles(function () {
                        logModalSnapshot(`open-followup-${delayMs}ms-before-refresh`);
                        refreshMapViewport(`modal-open-followup-${delayMs}ms`);
                    });
                }, delayMs);

                openFollowUpTimeoutIds.push(timeoutId);
            });
        }

        function scheduleCloseDiagnostics() {
            clearCloseDiagnosticTimeouts();

            logModalSnapshot('close-checkpoint-immediate-after-mutations');

            requestAnimationFrame(function () {
                logModalSnapshot('close-checkpoint-raf-1');
            });

            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    logModalSnapshot('close-checkpoint-raf-2');
                    refreshMapViewport('modal-close-raf-2');
                });
            });

            closeDiagnosticTimeoutIds.push(setTimeout(function () {
                logModalSnapshot('close-checkpoint-50ms');
                refreshMapViewport('modal-close-50ms');
            }, 50));

            closeDiagnosticTimeoutIds.push(setTimeout(function () {
                logModalSnapshot('close-checkpoint-150ms');
                refreshMapViewport('modal-close-150ms');
            }, 150));

            closeDiagnosticTimeoutIds.push(setTimeout(function () {
                logModalSnapshot('close-checkpoint-300ms');
                refreshMapViewport('modal-close-300ms');
            }, 300));
        }

        function moveMapWrapperToBody() {
            if (!mapWrapper || !document.body) {
                return;
            }

            if (mapWrapper.parentNode !== document.body) {
                document.body.appendChild(mapWrapper);
            }
        }

        function restoreMapWrapperToOriginalLocation() {
            if (!mapWrapper || !originalMapWrapperParent) {
                return;
            }

            if (originalMapWrapperNextSibling && originalMapWrapperNextSibling.parentNode === originalMapWrapperParent) {
                originalMapWrapperParent.insertBefore(mapWrapper, originalMapWrapperNextSibling);
                return;
            }

            originalMapWrapperParent.appendChild(mapWrapper);
        }

        // Open modal, size the map, refresh once, then run follow-up refreshes.
        function openMapModal() {
            clearOpenFollowUpTimeouts();
            clearCloseDiagnosticTimeouts();

            logModalSnapshot('open-before-class-add');

            moveMapWrapperToBody();
            document.body.classList.add(mapModalClass);

            logModalSnapshot('open-after-class-add');

            runAfterLayoutSettles(function () {
                logModalSnapshot('open-after-layout-settle-before-size');

                sizeExpandedMap();

                runAfterLayoutSettles(function () {
                    logModalSnapshot('open-after-size-before-refresh');
                    refreshMapViewport('modal-open-initial');
                    scheduleOpenFollowUpRefreshes();
                });
            });
        }

        // Close modal, remove custom modal sizing, then run timed diagnostics.
        function closeMapModal() {
            clearOpenFollowUpTimeouts();
            clearCloseDiagnosticTimeouts();

            logModalSnapshot('close-before-class-remove');

            document.body.classList.remove(mapModalClass);

            if (mapElement) {
                mapElement.style.removeProperty('width');
                mapElement.style.removeProperty('height');
            }

            restoreMapWrapperToOriginalLocation();

            logModalSnapshot('close-after-restore');

            scheduleCloseDiagnostics();
        }

        if (openMapModalButton) {
            openMapModalButton.addEventListener('click', openMapModal);
        }

        if (closeMapModalButton) {
            closeMapModalButton.addEventListener('click', closeMapModal);
        }

        // When the browser window changes size, update the modal box if needed,
        // then refresh the map after layout has settled.
        window.addEventListener('resize', function () {
            clearTimeout(resizeTimeoutId);

            resizeTimeoutId = setTimeout(function () {
                console.debug('[Kniraven Modal] Window resize detected', {
                    dom: getModalDomSnapshot(),
                });

                if (isMapModalOpen()) {
                    sizeExpandedMap();
                }

                runAfterLayoutSettles(function () {
                    logModalSnapshot('window-resize-after-layout-settle');
                    refreshMapViewport('window-resize');
                });
            }, 120);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && isMapModalOpen()) {
                closeMapModal();
            }
        });
    });
    </script>
</body>
</html>