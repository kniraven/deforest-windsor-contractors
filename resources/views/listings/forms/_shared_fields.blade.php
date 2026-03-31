@php
    $listing = $listing ?? null;

    $currentListingType = old('listing_type', $listing->listing_type ?? 'business');

    $questionSets = [
        'business' => [
            'local_label' => 'Does the primary owner of this business live in DeForest or Windsor?',
            'local_help' => 'This helps us highlight listings where the owner personally lives in the local community.',
            'independent_label' => 'Is this business independently owned and operated?',
            'independent_help' => 'Independent usually means this business is not controlled by a larger outside company.',
            'parent_label' => 'Is this business a franchise, chain, or location of a larger parent company?',
            'parent_help' => 'Examples include chain brands, franchises, or branch locations controlled by a larger company.',
            'show_parent' => true,
        ],
        'individual' => [
            'local_label' => 'Do you live in DeForest or Windsor?',
            'local_help' => 'This helps us highlight service providers who personally live in the local community.',
            'independent_label' => 'Do you offer this service independently, rather than on behalf of a larger company, franchise, or employer?',
            'independent_help' => 'Choose Yes if this listing is for your own independent service work.',
            'parent_label' => 'Is this service part of a larger company, franchise, or employer?',
            'parent_help' => 'This question is hidden for individual listings because the independence answer is enough for the system to classify it.',
            'show_parent' => false,
        ],
        'nonprofit' => [
            'local_label' => 'Is this organization primarily led by someone who lives in DeForest or Windsor?',
            'local_help' => 'This helps us identify organizations with leadership rooted in the local community.',
            'independent_label' => 'Is this organization independently operated?',
            'independent_help' => 'Choose Yes if this organization is not primarily controlled by a larger outside organization.',
            'parent_label' => 'Is this organization a chapter, branch, affiliate, or program of a larger outside organization?',
            'parent_help' => 'Examples include local chapters or branches that are part of a larger parent organization.',
            'show_parent' => true,
        ],
    ];

    $currentQuestions = $questionSets[$currentListingType] ?? $questionSets['business'];
@endphp

<section class="grid gap-4">
    <h2 class="text-lg font-semibold border-b pb-2">Basic Information</h2>

    <div>
        <label for="display_name" class="block text-sm font-medium mb-1">Display Name</label>
        <input
            id="display_name"
            name="display_name"
            class="w-full border rounded px-3 py-2 @error('display_name') border-red-500 @enderror"
            placeholder="Example: Kniraven LLC"
            value="{{ old('display_name', $listing->display_name ?? '') }}"
        >
        @error('display_name')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label for="listing_type" class="block text-sm font-medium mb-1">Listing Type</label>
            <select
                id="listing_type"
                name="listing_type"
                data-listing-type-select
                class="w-full border rounded px-3 py-2 @error('listing_type') border-red-500 @enderror"
            >
                @foreach ($listingTypes as $listingType)
                    <option value="{{ $listingType }}" @selected(old('listing_type', $listing->listing_type ?? 'business') === $listingType)>
                        {{ ucfirst($listingType) }}
                    </option>
                @endforeach
            </select>
            @error('listing_type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="municipality" class="block text-sm font-medium mb-1">Municipality</label>
            <select id="municipality" name="municipality" class="w-full border rounded px-3 py-2 @error('municipality') border-red-500 @enderror">
                @foreach ($municipalities as $municipality)
                    <option value="{{ $municipality }}" @selected(old('municipality', $listing->municipality ?? '') === $municipality)>
                        {{ $municipality }}
                    </option>
                @endforeach
            </select>
            @error('municipality')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="service_type_search" class="block text-sm font-medium mb-1">Service Type</label>

        <div id="service_type_combobox" class="relative">
            <input
                id="service_type"
                name="service_type"
                type="hidden"
                value="{{ old('service_type', $listing->service_type ?? '') }}"
            >

            <input
                id="service_type_search"
                type="text"
                autocomplete="off"
                spellcheck="false"
                role="combobox"
                aria-autocomplete="list"
                aria-expanded="false"
                aria-controls="service_type_dropdown"
                aria-haspopup="listbox"
                class="w-full rounded-xl border bg-white px-4 py-2.5 pr-11 text-base text-slate-900 placeholder:text-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200 @error('service_type') border-red-500 @else border-slate-300 @enderror"
                placeholder="Start typing to filter service types"
                value="{{ old('service_type', $listing->service_type ?? '') }}"
            >

            <button
                id="service_type_toggle"
                type="button"
                class="absolute inset-y-0 right-0 flex w-11 items-center justify-center rounded-r-xl text-slate-600 transition hover:text-slate-900"
                aria-label="Toggle service type options"
                tabindex="-1"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                id="service_type_dropdown"
                class="absolute left-0 right-0 z-30 mt-2 hidden overflow-hidden rounded-xl border border-slate-200 bg-white shadow-lg"
            >
                <div
                    id="service_type_options"
                    role="listbox"
                    class="max-h-64 overflow-y-auto py-1"
                ></div>

                <div
                    id="service_type_empty"
                    class="hidden px-4 py-3 text-sm text-slate-500"
                >
                    No matching service types found.
                </div>
            </div>
        </div>

        <p class="text-xs text-gray-500 mt-1">
            Type to filter, then click a result or press Enter to choose an exact value.
        </p>

        @error('service_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div id="other_service_type_group" class="@if (old('service_type', $listing->service_type ?? '') !== 'Other') hidden @endif">
        <label for="other_service_type" class="block text-sm font-medium mb-1">Other Service Type <span class="text-gray-500 font-normal">(only if Service Type is Other)</span></label>
        <input
            id="other_service_type"
            name="other_service_type"
            class="w-full border rounded px-3 py-2 @error('other_service_type') border-red-500 @enderror"
            placeholder="Example: Piano Tuning"
            value="{{ old('other_service_type', $listing->other_service_type ?? '') }}"
        >
        @error('other_service_type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="legal_structure" class="block text-sm font-medium mb-1">Legal Structure <span class="text-gray-500 font-normal">(optional)</span></label>
        <select
            id="legal_structure"
            name="legal_structure"
            class="w-full border rounded px-3 py-2 @error('legal_structure') border-red-500 @enderror"
        >
            <option value="">None selected</option>
            @foreach ($legalStructures as $legalStructure)
                <option value="{{ $legalStructure }}" @selected(old('legal_structure', $listing->legal_structure ?? '') === $legalStructure)>
                    {{ $legalStructure }}
                </option>
            @endforeach
        </select>

        <p class="text-xs text-gray-500 mt-1">This list changes based on the selected listing type.</p>

        <div
            id="legal_structure_individual_tip"
            class="mt-2 rounded-xl border border-[rgba(252,211,77,0.82)] bg-[rgba(120,53,15,0.88)] px-3 py-3 text-sm leading-6 text-amber-50 shadow-[inset_0_0_0_1px_rgba(252,211,77,0.18)] @if ($currentListingType !== 'individual') hidden @endif"
        >
            <span class="font-semibold text-amber-200">Tip:</span>
            Sole Proprietorship is usually the right choice if you're just one person offering services and haven't formed an LLC or corporation.
        </div>

        @error('legal_structure')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div id="other_legal_structure_group" class="@if (old('legal_structure', $listing->legal_structure ?? '') !== 'Other') hidden @endif">
        <label for="other_legal_structure" class="block text-sm font-medium mb-1">Other Legal Structure <span class="text-gray-500 font-normal">(only if Legal Structure is Other)</span></label>
        <input
            id="other_legal_structure"
            name="other_legal_structure"
            class="w-full border rounded px-3 py-2 @error('other_legal_structure') border-red-500 @enderror"
            placeholder="Describe the legal structure"
            value="{{ old('other_legal_structure', $listing->other_legal_structure ?? '') }}"
        >
        @error('other_legal_structure')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="short_description" class="block text-sm font-medium mb-1">Short Description</label>
        <textarea
            id="short_description"
            name="short_description"
            class="w-full border rounded px-3 py-2 @error('short_description') border-red-500 @enderror"
            rows="4"
            placeholder="Brief public-facing summary of the listing"
        >{{ old('short_description', $listing->short_description ?? '') }}</textarea>
        @error('short_description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="tags_input" class="block text-sm font-medium mb-1">Tags <span class="text-gray-500 font-normal">(optional)</span></label>
        <textarea
            id="tags_input"
            name="tags_input"
            class="w-full border rounded px-3 py-2 @error('tags_input') border-red-500 @enderror"
            rows="3"
            placeholder="Example: emergency service, free estimates, weekend availability"
        >{{ old('tags_input', isset($listing) && $listing?->relationLoaded('tags') ? $listing->tags->pluck('name')->implode(', ') : '') }}</textarea>
        <p class="text-xs text-gray-500 mt-1">Enter up to 10 tags separated by commas. These tags help people find the listing in search.</p>
        @error('tags_input')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</section>

<section class="grid gap-4">
    <h2 class="text-lg font-semibold border-b pb-2">Local Community Questions</h2>

    <div class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
        These answers are used to determine whether the listing is marked as owner-local and locally independent.
    </div>

    <fieldset class="grid gap-3">
        <div class="flex items-start gap-2">
            <legend id="local-connection-label" data-question-key="local_label" class="text-sm font-medium text-slate-900">
                {{ $currentQuestions['local_label'] }}
            </legend>
            <x-info-tooltip>
                <span data-help-key="local_help">{{ $currentQuestions['local_help'] }}</span>
            </x-info-tooltip>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            @foreach ($answerOptions as $value => $label)
                <label class="flex items-center gap-2 border rounded px-3 py-2">
                    <input
                        type="radio"
                        name="local_connection_answer"
                        value="{{ $value }}"
                        @checked(old('local_connection_answer', $listing->local_connection_answer ?? '') === $value)
                    >
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        @error('local_connection_answer')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset class="grid gap-3">
        <div class="flex items-start gap-2">
            <legend id="independent-operation-label" data-question-key="independent_label" class="text-sm font-medium text-slate-900">
                {{ $currentQuestions['independent_label'] }}
            </legend>
            <x-info-tooltip>
                <span data-help-key="independent_help">{{ $currentQuestions['independent_help'] }}</span>
            </x-info-tooltip>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            @foreach ($answerOptions as $value => $label)
                <label class="flex items-center gap-2 border rounded px-3 py-2">
                    <input
                        type="radio"
                        name="independent_operation_answer"
                        value="{{ $value }}"
                        @checked(old('independent_operation_answer', $listing->independent_operation_answer ?? '') === $value)
                    >
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        @error('independent_operation_answer')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </fieldset>

    <fieldset id="parent-affiliation-group" class="grid gap-3 @if (!$currentQuestions['show_parent']) hidden @endif">
        <div class="flex items-start gap-2">
            <legend id="parent-affiliation-label" data-question-key="parent_label" class="text-sm font-medium text-slate-900">
                {{ $currentQuestions['parent_label'] }}
            </legend>
            <x-info-tooltip>
                <span data-help-key="parent_help">{{ $currentQuestions['parent_help'] }}</span>
            </x-info-tooltip>
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            @foreach ($answerOptions as $value => $label)
                <label class="flex items-center gap-2 border rounded px-3 py-2">
                    <input
                        type="radio"
                        name="parent_affiliation_answer"
                        value="{{ $value }}"
                        @checked(old('parent_affiliation_answer', $listing->parent_affiliation_answer ?? '') === $value)
                    >
                    <span>{{ $label }}</span>
                </label>
            @endforeach
        </div>

        @error('parent_affiliation_answer')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </fieldset>
</section>

<section class="grid gap-4">
    <h2 class="text-lg font-semibold border-b pb-2">Contact Information</h2>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label for="phone" class="block text-sm font-medium mb-1">Phone <span class="text-gray-500 font-normal">(optional)</span></label>
            <input
                id="phone"
                name="phone"
                type="tel"
                class="w-full border rounded px-3 py-2 @error('phone') border-red-500 @enderror"
                placeholder="Example: 608-555-1234"
                value="{{ old('phone', $listing->phone ?? '') }}"
            >
            @error('phone')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-sm font-medium mb-1">Email <span class="text-gray-500 font-normal">(optional)</span></label>
            <input
                id="email"
                name="email"
                type="email"
                class="w-full border rounded px-3 py-2 @error('email') border-red-500 @enderror"
                placeholder="Example: hello@example.com"
                value="{{ old('email', $listing->email ?? '') }}"
            >
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="website_url" class="block text-sm font-medium mb-1">Website URL <span class="text-gray-500 font-normal">(optional)</span></label>
        <input
            id="website_url"
            name="website_url"
            type="url"
            class="w-full border rounded px-3 py-2 @error('website_url') border-red-500 @enderror"
            placeholder="Example: https://example.com"
            value="{{ old('website_url', $listing->website_url ?? '') }}"
        >
        @error('website_url')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</section>

<section class="grid gap-4">
    <h2 class="text-lg font-semibold border-b pb-2">Map and Location <span class="text-gray-500 font-normal">(optional)</span></h2>

    <div class="rounded-xl border border-[rgba(125,211,252,0.82)] bg-[rgba(15,23,42,0.94)] px-4 py-3 text-sm leading-6 text-slate-50 shadow-[inset_0_0_0_1px_rgba(125,211,252,0.14)]">
        <p class="font-semibold text-sky-200">Optional, but recommended</p>
        <p class="mt-1 text-slate-50">
            You do not have to provide a street address or map location. But listings with a map location may get higher visibility because they can appear on the site map.
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label for="street_address" class="block text-sm font-medium mb-1">Street Address <span class="text-gray-500 font-normal">(optional)</span></label>
            <input
                id="street_address"
                name="street_address"
                class="w-full border rounded px-3 py-2 @error('street_address') border-red-500 @enderror"
                placeholder="Example: 123 Main St"
                value="{{ old('street_address', $listing->street_address ?? '') }}"
            >
            @error('street_address')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="postal_code" class="block text-sm font-medium mb-1">Postal Code <span class="text-gray-500 font-normal">(optional)</span></label>
            <input
                id="postal_code"
                name="postal_code"
                class="w-full border rounded px-3 py-2 @error('postal_code') border-red-500 @enderror"
                placeholder="Example: 53532"
                value="{{ old('postal_code', $listing->postal_code ?? '') }}"
            >
            @error('postal_code')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label for="latitude" class="block text-sm font-medium mb-1">Latitude <span class="text-gray-500 font-normal">(optional)</span></label>
            <input
                id="latitude"
                name="latitude"
                type="number"
                step="any"
                class="w-full border rounded px-3 py-2 @error('latitude') border-red-500 @enderror"
                placeholder="Example: 43.2486"
                value="{{ old('latitude', $listing->latitude ?? '') }}"
            >
            @error('latitude')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="longitude" class="block text-sm font-medium mb-1">Longitude <span class="text-gray-500 font-normal">(optional)</span></label>
            <input
                id="longitude"
                name="longitude"
                type="number"
                step="any"
                class="w-full border rounded px-3 py-2 @error('longitude') border-red-500 @enderror"
                placeholder="Example: -89.3437"
                value="{{ old('longitude', $listing->longitude ?? '') }}"
            >
            @error('longitude')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <p class="text-xs text-slate-500">
        You can type coordinates manually, use your current location, or click the location on the map below.
    </p>

    <div class="flex flex-wrap items-center gap-3">
        <button
            id="use_current_location_button"
            type="button"
            class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-100"
        >
            Use My Current Location
        </button>

        <button
            id="center_local_area_button"
            type="button"
            class="inline-flex items-center rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-slate-800 transition hover:bg-slate-100"
        >
            Center on DeForest/Windsor
        </button>
    </div>

    <div
        id="listing_location_picker"
        class="h-80 w-full overflow-hidden rounded-xl border border-slate-200 bg-slate-100"
    ></div>

    <p id="location_picker_status" class="text-xs text-slate-500">
        Loading local boundary map…
    </p>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const listingTypeSelect = document.querySelector('[data-listing-type-select]');
        const legalStructureSelect = document.getElementById('legal_structure');
        const otherServiceTypeGroup = document.getElementById('other_service_type_group');
        const otherLegalStructureGroup = document.getElementById('other_legal_structure_group');
        const legalStructureIndividualTip = document.getElementById('legal_structure_individual_tip');

        const serviceTypeInput = document.getElementById('service_type_search');
        const serviceTypeHiddenInput = document.getElementById('service_type');
        const serviceTypeCombobox = document.getElementById('service_type_combobox');
        const serviceTypeDropdown = document.getElementById('service_type_dropdown');
        const serviceTypeOptionsContainer = document.getElementById('service_type_options');
        const serviceTypeEmptyState = document.getElementById('service_type_empty');
        const serviceTypeToggle = document.getElementById('service_type_toggle');

        const latitudeInput = document.getElementById('latitude');
        const longitudeInput = document.getElementById('longitude');
        const locationPickerElement = document.getElementById('listing_location_picker');
        const useCurrentLocationButton = document.getElementById('use_current_location_button');
        const centerLocalAreaButton = document.getElementById('center_local_area_button');
        const locationPickerStatus = document.getElementById('location_picker_status');

        const questionSets = @json($questionSets);
        const serviceTypes = @json(array_values($serviceTypes));

        const validLegalStructuresByListingType = {
            business: [
                'Corporation',
                'General Partnership',
                'Limited Liability Partnership (LLP)',
                'Limited Partnership (LP)',
                'Multi-Member LLC',
                'S Corporation',
                'Single-Member LLC',
                'Sole Proprietorship',
                'Other',
            ],
            individual: [
                'Sole Proprietorship',
                'Single-Member LLC',
                'Other',
            ],
            nonprofit: [
                'Nonprofit Corporation',
                'Other',
            ],
        };

        const localLabel = document.querySelector('[data-question-key="local_label"]');
        const independentLabel = document.querySelector('[data-question-key="independent_label"]');
        const parentLabel = document.querySelector('[data-question-key="parent_label"]');

        const localHelp = document.querySelector('[data-help-key="local_help"]');
        const independentHelp = document.querySelector('[data-help-key="independent_help"]');
        const parentHelp = document.querySelector('[data-help-key="parent_help"]');

        const parentGroup = document.getElementById('parent-affiliation-group');

        let filteredServiceTypes = [];
        let activeServiceTypeIndex = -1;
        let serviceTypeDropdownOpen = false;
        let serviceTypeBlurTimeoutId = null;

        let allLegalStructureOptions = [];

        let locationPickerMap = null;
        let locationPickerMarker = null;
        let locationPickerBoundaryGeoJson = null;
        let locationPickerBoundaryLayer = null;
        let locationPickerMaskLayer = null;
        let locationPickerServiceAreaBounds = null;
        let locationPickerMaxBounds = null;

        const localAreaBounds = [
            [43.1800, -89.3950],
            [43.2900, -89.2400],
        ];

        const boundaryRequest = {
            whereClause: "NAME IN ('Village of DeForest','Village of Windsor')",
            outFields: 'NAME,C_T_V',
            serviceUrl: 'https://services6.arcgis.com/SImUBTAEkgDmXQiR/ArcGIS/rest/services/Dane_County_Municipal_Boundaries/FeatureServer/0/query',
        };

        const locationPickerViewSettings = {
            maxBoundsPaddingRatio: 0.18,
            fitPaddingPx: [12, 12],
            clickZoomLevel: 16,
            maxBoundsViscosity: 1.0,
        };

        function moveOtherToEnd(values) {
            const safeValues = Array.isArray(values) ? [...values] : [];

            const nonOtherValues = safeValues.filter(function (value) {
                return value !== 'Other';
            });

            const hasOther = safeValues.includes('Other');

            if (hasOther) {
                nonOtherValues.push('Other');
            }

            return nonOtherValues;
        }

        function updateQuestionText() {
            if (!listingTypeSelect) {
                return;
            }

            const selectedType = listingTypeSelect.value;
            const questions = questionSets[selectedType] || questionSets.business;

            localLabel.textContent = questions.local_label;
            independentLabel.textContent = questions.independent_label;
            parentLabel.textContent = questions.parent_label;

            localHelp.textContent = questions.local_help;
            independentHelp.textContent = questions.independent_help;
            parentHelp.textContent = questions.parent_help;

            if (questions.show_parent) {
                parentGroup.classList.remove('hidden');
            } else {
                parentGroup.classList.add('hidden');
            }
        }

        function updateOtherServiceTypeVisibility() {
            if (!otherServiceTypeGroup || !serviceTypeHiddenInput) {
                return;
            }

            if (serviceTypeHiddenInput.value === 'Other') {
                otherServiceTypeGroup.classList.remove('hidden');
            } else {
                otherServiceTypeGroup.classList.add('hidden');
            }
        }

        function updateOtherLegalStructureVisibility() {
            if (!otherLegalStructureGroup || !legalStructureSelect) {
                return;
            }

            if (legalStructureSelect.value === 'Other') {
                otherLegalStructureGroup.classList.remove('hidden');
            } else {
                otherLegalStructureGroup.classList.add('hidden');
            }
        }

        function updateLegalStructureTipVisibility() {
            if (!legalStructureIndividualTip || !listingTypeSelect) {
                return;
            }

            if (listingTypeSelect.value === 'individual') {
                legalStructureIndividualTip.classList.remove('hidden');
            } else {
                legalStructureIndividualTip.classList.add('hidden');
            }
        }

        function captureLegalStructureOptions() {
            if (!legalStructureSelect) {
                return;
            }

            allLegalStructureOptions = Array.from(legalStructureSelect.options).map(function (option) {
                return {
                    value: option.value,
                    label: option.textContent,
                };
            });
        }

        function updateLegalStructureOptions() {
            if (!listingTypeSelect || !legalStructureSelect || allLegalStructureOptions.length === 0) {
                return;
            }

            const selectedListingType = listingTypeSelect.value;
            const allowedValues = moveOtherToEnd(validLegalStructuresByListingType[selectedListingType] || []);
            const currentValue = legalStructureSelect.value;

            const filteredOptions = allLegalStructureOptions.filter(function (option) {
                if (option.value === '') {
                    return true;
                }

                return allowedValues.includes(option.value);
            });

            const orderedFilteredOptions = [
                ...filteredOptions.filter(function (option) {
                    return option.value !== 'Other';
                }),
                ...filteredOptions.filter(function (option) {
                    return option.value === 'Other';
                }),
            ];

            legalStructureSelect.innerHTML = '';

            orderedFilteredOptions.forEach(function (optionData) {
                const option = document.createElement('option');
                option.value = optionData.value;
                option.textContent = optionData.label;
                legalStructureSelect.appendChild(option);
            });

            const stillValid = orderedFilteredOptions.some(function (optionData) {
                return optionData.value === currentValue;
            });

            if (stillValid) {
                legalStructureSelect.value = currentValue;
            } else {
                legalStructureSelect.value = '';
            }

            updateOtherLegalStructureVisibility();
        }

        function normalizeServiceTypeValue(value) {
            return String(value || '').trim().toLowerCase();
        }

        function getOrderedServiceTypesForDisplay(values) {
            return moveOtherToEnd(values);
        }

        function findExactServiceTypeMatch(value) {
            const normalizedValue = normalizeServiceTypeValue(value);

            if (normalizedValue === '') {
                return null;
            }

            return serviceTypes.find(function (serviceType) {
                return normalizeServiceTypeValue(serviceType) === normalizedValue;
            }) || null;
        }

        function setServiceTypeValue(value) {
            const safeValue = String(value || '');
            serviceTypeInput.value = safeValue;
            serviceTypeHiddenInput.value = safeValue;
            updateOtherServiceTypeVisibility();
        }

        function openServiceTypeDropdown() {
            if (!serviceTypeDropdown) {
                return;
            }

            serviceTypeDropdown.classList.remove('hidden');
            serviceTypeInput.setAttribute('aria-expanded', 'true');
            serviceTypeDropdownOpen = true;
        }

        function closeServiceTypeDropdown() {
            if (!serviceTypeDropdown) {
                return;
            }

            serviceTypeDropdown.classList.add('hidden');
            serviceTypeInput.setAttribute('aria-expanded', 'false');
            serviceTypeDropdownOpen = false;
            activeServiceTypeIndex = -1;
        }

        function syncHiddenServiceTypeInput() {
            const exactMatch = findExactServiceTypeMatch(serviceTypeInput.value);

            if (exactMatch) {
                setServiceTypeValue(exactMatch);
                return;
            }

            serviceTypeHiddenInput.value = serviceTypeInput.value.trim();
            updateOtherServiceTypeVisibility();
        }

        function getServiceTypeOptionButtonId(index) {
            return `service_type_option_${index}`;
        }

        function renderServiceTypeOptions() {
            const currentQuery = normalizeServiceTypeValue(serviceTypeInput.value);
            const currentSelectionIsOther = normalizeServiceTypeValue(serviceTypeHiddenInput.value) === 'other';

            const otherOption = serviceTypes.find(function (serviceType) {
                return normalizeServiceTypeValue(serviceType) === 'other';
            });

            let matchingServiceTypes = [];

            // If "Other" is the current selected value, show everything.
            if (currentSelectionIsOther) {
                matchingServiceTypes = [...serviceTypes];
            } else {
                // Otherwise, filter normally, but do NOT include "Other" here yet.
                matchingServiceTypes = serviceTypes.filter(function (serviceType) {
                    return normalizeServiceTypeValue(serviceType) !== 'other'
                        && normalizeServiceTypeValue(serviceType).includes(currentQuery);
                });

                // Always show "Other" at the end, even if it does not match the typed text.
                if (otherOption) {
                    matchingServiceTypes.push(otherOption);
                }
            }

            filteredServiceTypes = getOrderedServiceTypesForDisplay(matchingServiceTypes);

            if (filteredServiceTypes.length === 0) {
                activeServiceTypeIndex = -1;
                serviceTypeOptionsContainer.innerHTML = '';
                serviceTypeEmptyState.classList.remove('hidden');
                serviceTypeInput.removeAttribute('aria-activedescendant');
                return;
            }

            if (activeServiceTypeIndex >= filteredServiceTypes.length) {
                activeServiceTypeIndex = filteredServiceTypes.length - 1;
            }

            if (activeServiceTypeIndex < 0) {
                activeServiceTypeIndex = 0;
            }

            serviceTypeEmptyState.classList.add('hidden');

            serviceTypeOptionsContainer.innerHTML = filteredServiceTypes.map(function (serviceType, index) {
                const isActive = index === activeServiceTypeIndex;

                return `
                    <button
                        id="${getServiceTypeOptionButtonId(index)}"
                        type="button"
                        role="option"
                        aria-selected="${isActive ? 'true' : 'false'}"
                        data-service-type-option="${serviceType}"
                        class="kn-service-type-option ${isActive ? 'kn-service-type-option-active' : ''}"
                    >
                        ${serviceType}
                    </button>
                `;
            }).join('');

            serviceTypeInput.setAttribute(
                'aria-activedescendant',
                getServiceTypeOptionButtonId(activeServiceTypeIndex)
            );

            const optionButtons = serviceTypeOptionsContainer.querySelectorAll('[data-service-type-option]');

            optionButtons.forEach(function (button) {
                button.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                });

                button.addEventListener('click', function () {
                    selectServiceType(button.getAttribute('data-service-type-option'));
                });
            });
        }

        function selectServiceType(value) {
            setServiceTypeValue(value);
            renderServiceTypeOptions();
            closeServiceTypeDropdown();
            serviceTypeInput.focus();
        }

        function moveActiveServiceType(step) {
            if (filteredServiceTypes.length === 0) {
                return;
            }

            if (!serviceTypeDropdownOpen) {
                openServiceTypeDropdown();
            }

            activeServiceTypeIndex += step;

            if (activeServiceTypeIndex < 0) {
                activeServiceTypeIndex = filteredServiceTypes.length - 1;
            }

            if (activeServiceTypeIndex >= filteredServiceTypes.length) {
                activeServiceTypeIndex = 0;
            }

            renderServiceTypeOptions();

            const activeButton = document.getElementById(getServiceTypeOptionButtonId(activeServiceTypeIndex));

            if (activeButton) {
                activeButton.scrollIntoView({
                    block: 'nearest',
                });
            }
        }

        function applyActiveOrExactServiceType() {
            if (serviceTypeDropdownOpen && activeServiceTypeIndex >= 0 && filteredServiceTypes[activeServiceTypeIndex]) {
                selectServiceType(filteredServiceTypes[activeServiceTypeIndex]);
                return;
            }

            syncHiddenServiceTypeInput();
            closeServiceTypeDropdown();
        }

        function updateLocationPickerStatus(message, isError = false) {
            if (!locationPickerStatus) {
                return;
            }

            locationPickerStatus.textContent = message;
            locationPickerStatus.className = isError
                ? 'text-xs text-red-600'
                : 'text-xs text-slate-500';
        }

        function parseCoordinate(value) {
            const parsedValue = parseFloat(value);
            return Number.isFinite(parsedValue) ? parsedValue : null;
        }

        function removeLocationMarker() {
            if (!locationPickerMap || !locationPickerMarker) {
                return;
            }

            locationPickerMap.removeLayer(locationPickerMarker);
            locationPickerMarker = null;
        }

        function ensureLocationMarker(lat, lng) {
            if (!locationPickerMap || !window.L) {
                return;
            }

            if (!locationPickerMarker) {
                locationPickerMarker = window.L.circleMarker([lat, lng], {
                    radius: 8,
                    color: '#2563eb',
                    weight: 2,
                    fillColor: '#60a5fa',
                    fillOpacity: 0.85,
                }).addTo(locationPickerMap);

                locationPickerMarker.bringToFront();
                return;
            }

            locationPickerMarker.setLatLng([lat, lng]);
            locationPickerMarker.bringToFront();
        }

        function getLocationPickerBoundaryUrl() {
            const params = new URLSearchParams({
                where: boundaryRequest.whereClause,
                outFields: boundaryRequest.outFields,
                returnGeometry: 'true',
                outSR: '4326',
                f: 'geojson',
            });

            return `${boundaryRequest.serviceUrl}?${params.toString()}`;
        }

        function geometryToHoleRings(geometry) {
            if (!geometry || !geometry.coordinates) {
                return [];
            }

            if (geometry.type === 'Polygon') {
                return [
                    geometry.coordinates[0].map(function (pair) {
                        return [pair[1], pair[0]];
                    }),
                ];
            }

            if (geometry.type === 'MultiPolygon') {
                return geometry.coordinates.map(function (polygon) {
                    return polygon[0].map(function (pair) {
                        return [pair[1], pair[0]];
                    });
                });
            }

            return [];
        }

        function buildLocationPickerMaskLayer(boundaryGeoJson, fogBounds) {
            const southWest = fogBounds.getSouthWest();
            const northEast = fogBounds.getNorthEast();

            const outerRing = [
                [southWest.lat, southWest.lng],
                [northEast.lat, southWest.lng],
                [northEast.lat, northEast.lng],
                [southWest.lat, northEast.lng],
            ];

            const holes = boundaryGeoJson.features.flatMap(function (feature) {
                return geometryToHoleRings(feature.geometry);
            });

            return window.L.polygon([outerRing, ...holes], {
                stroke: false,
                fillColor: '#000000',
                fillOpacity: 0.28,
                fillRule: 'evenodd',
                interactive: false,
                bubblingMouseEvents: false,
            });
        }

        function isPointInRing(point, ring) {
            if (!Array.isArray(ring) || ring.length < 3) {
                return false;
            }

            const x = Number(point[0]);
            const y = Number(point[1]);

            let isInside = false;

            for (let i = 0, j = ring.length - 1; i < ring.length; j = i, i += 1) {
                const current = ring[i];
                const previous = ring[j];

                if (!Array.isArray(current) || !Array.isArray(previous)) {
                    continue;
                }

                const xi = Number(current[0]);
                const yi = Number(current[1]);
                const xj = Number(previous[0]);
                const yj = Number(previous[1]);

                const intersects = ((yi > y) !== (yj > y))
                    && (x < ((xj - xi) * (y - yi)) / ((yj - yi) || Number.EPSILON) + xi);

                if (intersects) {
                    isInside = !isInside;
                }
            }

            return isInside;
        }

        function isPointInPolygonCoordinates(point, polygonCoordinates) {
            if (!Array.isArray(polygonCoordinates) || polygonCoordinates.length === 0) {
                return false;
            }

            const exteriorRing = polygonCoordinates[0];

            if (!isPointInRing(point, exteriorRing)) {
                return false;
            }

            for (let ringIndex = 1; ringIndex < polygonCoordinates.length; ringIndex += 1) {
                if (isPointInRing(point, polygonCoordinates[ringIndex])) {
                    return false;
                }
            }

            return true;
        }

        function isPointInsideFeatureGeometry(point, geometry) {
            if (!geometry || !Array.isArray(geometry.coordinates)) {
                return false;
            }

            if (geometry.type === 'Polygon') {
                return isPointInPolygonCoordinates(point, geometry.coordinates);
            }

            if (geometry.type === 'MultiPolygon') {
                return geometry.coordinates.some(function (polygonCoordinates) {
                    return isPointInPolygonCoordinates(point, polygonCoordinates);
                });
            }

            return false;
        }

        function isPointInsideLocationPickerBoundary(lat, lng) {
            if (!locationPickerBoundaryGeoJson || !Array.isArray(locationPickerBoundaryGeoJson.features) || locationPickerBoundaryGeoJson.features.length === 0) {
                return true;
            }

            const point = [Number(lng), Number(lat)];

            return locationPickerBoundaryGeoJson.features.some(function (feature) {
                return isPointInsideFeatureGeometry(point, feature.geometry);
            });
        }

        function fitLocationPickerToAllowedArea() {
            if (!locationPickerMap) {
                return;
            }

            if (
                locationPickerServiceAreaBounds
                && typeof locationPickerServiceAreaBounds.isValid === 'function'
                && locationPickerServiceAreaBounds.isValid()
            ) {
                locationPickerMap.fitBounds(locationPickerServiceAreaBounds, {
                    padding: locationPickerViewSettings.fitPaddingPx,
                });

                return;
            }

            locationPickerMap.fitBounds(localAreaBounds, {
                padding: locationPickerViewSettings.fitPaddingPx,
            });
        }

        function clearLocationPickerBoundaryLayers() {
            if (locationPickerMap && locationPickerBoundaryLayer) {
                locationPickerMap.removeLayer(locationPickerBoundaryLayer);
                locationPickerBoundaryLayer = null;
            }

            if (locationPickerMap && locationPickerMaskLayer) {
                locationPickerMap.removeLayer(locationPickerMaskLayer);
                locationPickerMaskLayer = null;
            }
        }

        async function loadLocationPickerBoundaries() {
            const response = await fetch(getLocationPickerBoundaryUrl());

            if (!response.ok) {
                throw new Error(`Boundary request failed with status ${response.status}`);
            }

            const boundaryGeoJson = await response.json();

            if (!boundaryGeoJson.features || boundaryGeoJson.features.length === 0) {
                throw new Error('Boundary request returned no matching features.');
            }

            clearLocationPickerBoundaryLayers();

            locationPickerBoundaryGeoJson = boundaryGeoJson;

            const boundaryLayer = window.L.geoJSON(boundaryGeoJson, {
                interactive: false,
                bubblingMouseEvents: false,
                style: function (feature) {
                    const isDeForest = feature?.properties?.NAME === 'Village of DeForest';

                    return {
                        color: isDeForest ? '#7c3aed' : '#f59e0b',
                        weight: 3,
                        opacity: 0.95,
                        fillColor: isDeForest ? '#7c3aed' : '#f59e0b',
                        fillOpacity: 0.05,
                    };
                },
            });

            const serviceAreaBounds = boundaryLayer.getBounds();

            if (!serviceAreaBounds.isValid()) {
                throw new Error('Location picker service area bounds were invalid.');
            }

            locationPickerServiceAreaBounds = serviceAreaBounds;
            locationPickerMaxBounds = serviceAreaBounds.pad(locationPickerViewSettings.maxBoundsPaddingRatio);

            locationPickerBoundaryLayer = boundaryLayer.addTo(locationPickerMap);
            locationPickerMaskLayer = buildLocationPickerMaskLayer(boundaryGeoJson, locationPickerMaxBounds).addTo(locationPickerMap);

            locationPickerMap.setMaxBounds(locationPickerMaxBounds);
            fitLocationPickerToAllowedArea();

            if (locationPickerMaskLayer) {
                locationPickerMaskLayer.bringToFront();
            }

            if (locationPickerBoundaryLayer) {
                locationPickerBoundaryLayer.bringToFront();
            }

            if (locationPickerMarker) {
                locationPickerMarker.bringToFront();
            }
        }

        function setCoordinates(lat, lng, options = {}) {
            const {
                recenterMap = true,
                zoomLevel = null,
                statusMessage = null,
            } = options;

            if (latitudeInput) {
                latitudeInput.value = Number(lat).toFixed(7);
            }

            if (longitudeInput) {
                longitudeInput.value = Number(lng).toFixed(7);
            }

            ensureLocationMarker(lat, lng);

            if (locationPickerMap && recenterMap) {
                locationPickerMap.setView(
                    [lat, lng],
                    zoomLevel ?? Math.max(locationPickerMap.getZoom(), locationPickerViewSettings.clickZoomLevel)
                );
            }

            if (statusMessage) {
                updateLocationPickerStatus(statusMessage, false);
            }
        }

        function syncMapFromManualCoordinates() {
            const rawLat = latitudeInput ? latitudeInput.value.trim() : '';
            const rawLng = longitudeInput ? longitudeInput.value.trim() : '';

            if (rawLat === '' && rawLng === '') {
                removeLocationMarker();
                fitLocationPickerToAllowedArea();
                updateLocationPickerStatus('No map location selected. You can leave this blank or click the map to place one.', false);
                return;
            }

            const lat = parseCoordinate(rawLat);
            const lng = parseCoordinate(rawLng);

            if (lat === null || lng === null) {
                updateLocationPickerStatus('Enter both Latitude and Longitude, or leave both blank.', true);
                return;
            }

            if (!isPointInsideLocationPickerBoundary(lat, lng)) {
                removeLocationMarker();
                updateLocationPickerStatus('Coordinates must be inside DeForest or Windsor.', true);
                return;
            }

            setCoordinates(lat, lng, {
                recenterMap: true,
                zoomLevel: Math.max(locationPickerMap ? locationPickerMap.getZoom() : locationPickerViewSettings.clickZoomLevel, locationPickerViewSettings.clickZoomLevel),
                statusMessage: 'Map updated from the Latitude and Longitude fields.',
            });
        }

        async function initializeLocationPicker() {
            if (!locationPickerElement || !window.L) {
                updateLocationPickerStatus('Map picker could not load. You can still leave location blank or enter coordinates manually.', true);
                return;
            }

            locationPickerMap = window.L.map(locationPickerElement, {
                zoomControl: true,
                maxBoundsViscosity: locationPickerViewSettings.maxBoundsViscosity,
            });

            window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors',
            }).addTo(locationPickerMap);

            locationPickerMap.setMaxBounds(localAreaBounds);
            locationPickerMap.fitBounds(localAreaBounds, {
                padding: locationPickerViewSettings.fitPaddingPx,
            });

            try {
                await loadLocationPickerBoundaries();
                updateLocationPickerStatus('Click inside DeForest or Windsor to place the map point, use your current location, or enter coordinates manually.', false);
            } catch (error) {
                console.error('Location picker boundary loading failed:', error);

                locationPickerMap.setMaxBounds(localAreaBounds);
                locationPickerMap.fitBounds(localAreaBounds, {
                    padding: locationPickerViewSettings.fitPaddingPx,
                });

                updateLocationPickerStatus('Boundary guide could not load. The picker is still limited to the local area, but exact municipal shape checking is temporarily unavailable.', true);
            }

            locationPickerMap.on('click', function (event) {
                const clickedLat = event.latlng.lat;
                const clickedLng = event.latlng.lng;

                if (!isPointInsideLocationPickerBoundary(clickedLat, clickedLng)) {
                    updateLocationPickerStatus('Please choose a point inside DeForest or Windsor.', true);
                    return;
                }

                setCoordinates(clickedLat, clickedLng, {
                    recenterMap: false,
                    statusMessage: 'Coordinates updated from the map.',
                });
            });

            const existingLat = parseCoordinate(latitudeInput ? latitudeInput.value : null);
            const existingLng = parseCoordinate(longitudeInput ? longitudeInput.value : null);

            if (existingLat !== null && existingLng !== null) {
                if (!isPointInsideLocationPickerBoundary(existingLat, existingLng)) {
                    removeLocationMarker();
                    updateLocationPickerStatus('The current coordinates are outside DeForest or Windsor. Please choose a point inside the allowed area or clear the fields.', true);
                } else {
                    setCoordinates(existingLat, existingLng, {
                        recenterMap: true,
                        zoomLevel: locationPickerViewSettings.clickZoomLevel,
                        statusMessage: 'Loaded existing coordinates on the map.',
                    });
                }
            }

            setTimeout(function () {
                locationPickerMap.invalidateSize();
            }, 0);
        }

        if (listingTypeSelect) {
            captureLegalStructureOptions();

            listingTypeSelect.addEventListener('change', function () {
                updateQuestionText();
                updateLegalStructureOptions();
                updateLegalStructureTipVisibility();
            });

            updateQuestionText();
            updateLegalStructureOptions();
            updateLegalStructureTipVisibility();
        }

        if (legalStructureSelect) {
            legalStructureSelect.addEventListener('change', updateOtherLegalStructureVisibility);
            updateOtherLegalStructureVisibility();
        }

        if (serviceTypeInput && serviceTypeHiddenInput && serviceTypeDropdown && serviceTypeOptionsContainer && serviceTypeEmptyState && serviceTypeToggle) {
            renderServiceTypeOptions();
            syncHiddenServiceTypeInput();
            updateOtherServiceTypeVisibility();

            serviceTypeInput.addEventListener('focus', function () {
                renderServiceTypeOptions();
                openServiceTypeDropdown();
            });

            serviceTypeInput.addEventListener('click', function () {
                renderServiceTypeOptions();
                openServiceTypeDropdown();
            });

            serviceTypeInput.addEventListener('input', function () {
                const exactMatch = findExactServiceTypeMatch(serviceTypeInput.value);
                const currentSelectionIsOther = normalizeServiceTypeValue(serviceTypeHiddenInput.value) === 'other';

                if (exactMatch) {
                    serviceTypeHiddenInput.value = exactMatch;
                } else if (!currentSelectionIsOther) {
                    serviceTypeHiddenInput.value = serviceTypeInput.value.trim();
                }

                updateOtherServiceTypeVisibility();
                renderServiceTypeOptions();
                openServiceTypeDropdown();
            });

            serviceTypeInput.addEventListener('keydown', function (event) {
                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    moveActiveServiceType(1);
                    return;
                }

                if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    moveActiveServiceType(-1);
                    return;
                }

                if (event.key === 'Enter') {
                    event.preventDefault();
                    applyActiveOrExactServiceType();
                    return;
                }

                if (event.key === 'Escape') {
                    event.preventDefault();
                    closeServiceTypeDropdown();
                }
            });

            serviceTypeInput.addEventListener('blur', function () {
                clearTimeout(serviceTypeBlurTimeoutId);

                serviceTypeBlurTimeoutId = setTimeout(function () {
                    syncHiddenServiceTypeInput();
                    closeServiceTypeDropdown();
                }, 120);
            });

            serviceTypeToggle.addEventListener('mousedown', function (event) {
                event.preventDefault();
            });

            serviceTypeToggle.addEventListener('click', function () {
                if (serviceTypeDropdownOpen) {
                    closeServiceTypeDropdown();
                    return;
                }

                renderServiceTypeOptions();
                openServiceTypeDropdown();
                serviceTypeInput.focus();
            });

            document.addEventListener('click', function (event) {
                if (!serviceTypeCombobox.contains(event.target)) {
                    syncHiddenServiceTypeInput();
                    closeServiceTypeDropdown();
                }
            });
        }

        initializeLocationPicker();

        if (useCurrentLocationButton) {
            useCurrentLocationButton.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    updateLocationPickerStatus('Your browser does not support current location lookup.', true);
                    return;
                }

                useCurrentLocationButton.disabled = true;
                updateLocationPickerStatus('Getting your current location…', false);

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const currentLat = position.coords.latitude;
                        const currentLng = position.coords.longitude;

                        if (!isPointInsideLocationPickerBoundary(currentLat, currentLng)) {
                            updateLocationPickerStatus('Your current location is outside DeForest or Windsor, so it was not selected.', true);
                            useCurrentLocationButton.disabled = false;
                            return;
                        }

                        setCoordinates(currentLat, currentLng, {
                            recenterMap: true,
                            zoomLevel: 17,
                            statusMessage: 'Coordinates updated from your current location.',
                        });

                        useCurrentLocationButton.disabled = false;
                    },
                    function () {
                        updateLocationPickerStatus('Could not get your current location. You can still click the map, enter coordinates manually, or leave location blank.', true);
                        useCurrentLocationButton.disabled = false;
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0,
                    }
                );
            });
        }

        if (centerLocalAreaButton) {
            centerLocalAreaButton.addEventListener('click', function () {
                if (!locationPickerMap) {
                    return;
                }

                fitLocationPickerToAllowedArea();
                updateLocationPickerStatus('Map centered on the DeForest/Windsor area.', false);
            });
        }

        if (latitudeInput) {
            latitudeInput.addEventListener('change', syncMapFromManualCoordinates);
            latitudeInput.addEventListener('blur', syncMapFromManualCoordinates);
        }

        if (longitudeInput) {
            longitudeInput.addEventListener('change', syncMapFromManualCoordinates);
            longitudeInput.addEventListener('blur', syncMapFromManualCoordinates);
        }
    });
</script>