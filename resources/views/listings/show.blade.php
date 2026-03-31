<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $listing->display_name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="kn-page-shell">
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
            <a href="{{ route('listings.index') }}" class="kn-link-subtle">Back to Directory</a>

            <a
                href="{{ route('listings.requests.create', $listing) }}"
                class="kn-btn-secondary"
            >
                Request Update / Takedown
            </a>
        </div>

        <article class="kn-panel rounded-2xl p-5 sm:p-6">
            <div class="mb-4 flex flex-wrap gap-2">
                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                    {{ $listing->municipality }}
                </span>

                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                    {{ ucfirst($listing->listing_type) }}
                </span>

                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                    {{ $listing->service_type === 'Other' && $listing->other_service_type
                        ? $listing->other_service_type
                        : $listing->service_type }}
                </span>

                @if ($listing->is_featured)
                    <span class="inline-flex items-center rounded-full bg-fuchsia-50 px-3 py-1 text-xs font-medium text-fuchsia-700">
                        Featured
                    </span>
                @endif

                @if ($listing->is_verified)
                    <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                        Verified
                    </span>
                @endif

                @if ($listing->is_locally_independent)
                    <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-medium text-amber-700">
                        Locally Independent
                    </span>
                @endif

                @if ($listing->is_owner_local)
                    <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                        Owner Lives Here
                    </span>
                @endif
            </div>

            <h1 class="text-3xl font-bold tracking-tight">
                {{ $listing->display_name }}
            </h1>

            <p class="mt-3 text-sm leading-6 kn-body-text">
                {{ $listing->short_description }}
            </p>

            @if ($listing->tags->isNotEmpty())
                <section class="mt-6">
                    <h2 class="text-lg font-semibold">Tags</h2>

                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($listing->tags as $tag)
                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                {{ $tag->name }}
                            </span>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="mt-6 grid gap-6 md:grid-cols-2">
                <div>
                    <h2 class="text-lg font-semibold">Listing Details</h2>

                    <dl class="mt-3 space-y-3 text-sm">
                        <div>
                            <dt class="font-semibold text-slate-200">Municipality</dt>
                            <dd class="kn-body-text">{{ $listing->municipality }}</dd>
                        </div>

                        <div>
                            <dt class="font-semibold text-slate-200">Listing Type</dt>
                            <dd class="kn-body-text">{{ ucfirst($listing->listing_type) }}</dd>
                        </div>

                        <div>
                            <dt class="font-semibold text-slate-200">Service Type</dt>
                            <dd class="kn-body-text">
                                {{ $listing->service_type === 'Other' && $listing->other_service_type
                                    ? $listing->other_service_type
                                    : $listing->service_type }}
                            </dd>
                        </div>

                        @if ($listing->legal_structure)
                            <div>
                                <dt class="font-semibold text-slate-200">Legal Structure</dt>
                                <dd class="kn-body-text">
                                    {{ $listing->legal_structure === 'Other' && $listing->other_legal_structure
                                        ? $listing->other_legal_structure
                                        : $listing->legal_structure }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div>
                    <h2 class="text-lg font-semibold">Contact Information</h2>

                    <dl class="mt-3 space-y-3 text-sm">
                        @if ($listing->phone)
                            <div>
                                <dt class="font-semibold text-slate-200">Phone</dt>
                                <dd class="kn-body-text">{{ $listing->phone }}</dd>
                            </div>
                        @endif

                        @if ($listing->email)
                            <div>
                                <dt class="font-semibold text-slate-200">Email</dt>
                                <dd class="kn-body-text">
                                    <a href="mailto:{{ $listing->email }}" class="underline">
                                        {{ $listing->email }}
                                    </a>
                                </dd>
                            </div>
                        @endif

                        @if ($listing->website_url)
                            <div>
                                <dt class="font-semibold text-slate-200">Website</dt>
                                <dd class="kn-body-text">
                                    <a
                                        href="{{ $listing->website_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="underline"
                                    >
                                        {{ $listing->website_url }}
                                    </a>
                                </dd>
                            </div>
                        @endif

                        @if ($listing->street_address)
                            <div>
                                <dt class="font-semibold text-slate-200">Street Address</dt>
                                <dd class="kn-body-text">
                                    {{ $listing->street_address }}
                                    @if ($listing->postal_code)
                                        <br>{{ $listing->postal_code }}
                                    @endif
                                </dd>
                            </div>
                        @elseif ($listing->postal_code)
                            <div>
                                <dt class="font-semibold text-slate-200">Postal Code</dt>
                                <dd class="kn-body-text">{{ $listing->postal_code }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </section>

            @if ($listing->latitude !== null && $listing->longitude !== null)
                <section class="mt-6">
                    <h2 class="text-lg font-semibold">Map Coordinates</h2>

                    <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        <p><strong>Latitude:</strong> {{ $listing->latitude }}</p>
                        <p class="mt-1"><strong>Longitude:</strong> {{ $listing->longitude }}</p>
                    </div>
                </section>
            @endif

            <div class="mt-8">
                <a
                    href="{{ route('listings.requests.create', $listing) }}"
                    class="kn-btn-primary"
                >
                    Request a Change or Takedown
                </a>
            </div>
        </article>
    </div>
</body>
</html>