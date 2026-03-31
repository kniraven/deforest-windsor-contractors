@php
    $currentSort = $currentSort ?? 'display_name';
    $currentDirection = $currentDirection ?? 'asc';

    $nextDirection = function (string $column) use ($currentSort, $currentDirection): string {
        if ($currentSort === $column && $currentDirection === 'asc') {
            return 'desc';
        }

        return 'asc';
    };

    $sortIndicator = function (string $column) use ($currentSort, $currentDirection): string {
        if ($currentSort !== $column) {
            return '';
        }

        return $currentDirection === 'asc' ? ' ↑' : ' ↓';
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Listings</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="max-w-7xl mx-auto p-6">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold">Manage Listings</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Signed in as {{ auth()->user()->email }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('listings.index') }}" class="inline-flex items-center rounded border px-4 py-2 text-sm">
                    View Homepage
                </a>

                <a href="{{ route('admin.logs.index') }}" class="inline-flex items-center rounded border px-4 py-2 text-sm">
                    Admin Log
                </a>

                <a href="{{ route('admin.listings.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded">
                    Create Listing
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded border border-red-300 px-4 py-2 text-sm text-red-700">
                        Log Out
                    </button>
                </form>
            </div>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-3 text-sm text-gray-600">
            <span>Total: {{ $listings->count() }}</span>
            <span>Active: {{ $listings->where('is_active', true)->count() }}</span>
            <span>Inactive: {{ $listings->where('is_active', false)->count() }}</span>
            <span>Pending: {{ $listings->where('submission_status', 'pending')->count() }}</span>
        </div>

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-3">
                            <a
                                href="{{ route('admin.listings.index', array_merge(request()->query(), ['sort' => 'display_name', 'direction' => $nextDirection('display_name')])) }}"
                                class="underline"
                            >
                                Name{{ $sortIndicator('display_name') }}
                            </a>
                        </th>
                        <th class="text-left p-3">
                            <a
                                href="{{ route('admin.listings.index', array_merge(request()->query(), ['sort' => 'listing_type', 'direction' => $nextDirection('listing_type')])) }}"
                                class="underline"
                            >
                                Type{{ $sortIndicator('listing_type') }}
                            </a>
                        </th>
                        <th class="text-left p-3">
                            <a
                                href="{{ route('admin.listings.index', array_merge(request()->query(), ['sort' => 'service_type', 'direction' => $nextDirection('service_type')])) }}"
                                class="underline"
                            >
                                Service{{ $sortIndicator('service_type') }}
                            </a>
                        </th>
                        <th class="text-left p-3">
                            <a
                                href="{{ route('admin.listings.index', array_merge(request()->query(), ['sort' => 'municipality', 'direction' => $nextDirection('municipality')])) }}"
                                class="underline"
                            >
                                Municipality{{ $sortIndicator('municipality') }}
                            </a>
                        </th>
                        <th class="text-left p-3">
                            <a
                                href="{{ route('admin.listings.index', array_merge(request()->query(), ['sort' => 'submission_status', 'direction' => $nextDirection('submission_status')])) }}"
                                class="underline"
                            >
                                Status{{ $sortIndicator('submission_status') }}
                            </a>
                        </th>
                        <th class="text-left p-3">Tags</th>
                        <th class="text-left p-3">
                            <a
                                href="{{ route('admin.listings.index', array_merge(request()->query(), ['sort' => 'local_priority', 'direction' => $nextDirection('local_priority')])) }}"
                                class="underline"
                            >
                                Priority{{ $sortIndicator('local_priority') }}
                            </a>
                        </th>
                        <th class="text-left p-3">
                            <a
                                href="{{ route('admin.listings.index', array_merge(request()->query(), ['sort' => 'created_at', 'direction' => $nextDirection('created_at')])) }}"
                                class="underline"
                            >
                                Created{{ $sortIndicator('created_at') }}
                            </a>
                        </th>
                        <th class="text-left p-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($listings as $listing)
                        <tr
                            class="border-t align-top"
                            @if (!$listing->is_active)
                                style="background: rgba(127, 29, 29, 0.22);"
                            @endif
                        >
                            <td class="p-3">
                                <div class="font-medium">{{ $listing->display_name }}</div>
                                @if (!$listing->is_active)
                                    <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-red-300">
                                        Inactive listing
                                    </div>
                                @endif
                            </td>
                            <td class="p-3">{{ ucfirst($listing->listing_type) }}</td>
                            <td class="p-3">
                                {{ $listing->service_type === 'Other' && $listing->other_service_type
                                    ? $listing->other_service_type
                                    : $listing->service_type }}
                            </td>
                            <td class="p-3">{{ $listing->municipality }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-2">
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                        {{ ucfirst($listing->submission_status) }}
                                    </span>

                                    @if ($listing->is_active)
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-medium text-emerald-700">
                                            Active
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-medium text-red-700">
                                            Inactive
                                        </span>
                                    @endif

                                    @if ($listing->is_verified)
                                        <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-xs font-medium text-blue-700">
                                            Verified
                                        </span>
                                    @endif

                                    @if ($listing->is_featured)
                                        <span class="inline-flex items-center rounded-full bg-fuchsia-50 px-3 py-1 text-xs font-medium text-fuchsia-700">
                                            Featured
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">
                                @if ($listing->tags->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach ($listing->tags as $tag)
                                            <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-sm text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-2">
                                    @if ($listing->is_locally_independent)
                                        <span class="inline-flex items-center rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-700">
                                            Independent
                                        </span>
                                    @endif

                                    @if ($listing->is_owner_local)
                                        <span class="inline-flex items-center rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                            Owner Local
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3 text-sm text-gray-500">
                                {{ $listing->created_at?->format('Y-m-d') }}
                            </td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('admin.listings.edit', $listing) }}" class="text-blue-600 underline">Edit</a>
                                    <a href="{{ route('admin.logs.index', ['listing_id' => $listing->id]) }}" class="text-slate-700 underline">History</a>
                                    <form
                                        method="POST"
                                        action="{{ route('admin.listings.destroy', $listing) }}"
                                        onsubmit="return confirm('Are you sure you want to delete this listing? This cannot be undone.');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 underline">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                    @if ($listings->isEmpty())
                        <tr class="border-t">
                            <td colspan="9" class="p-6 text-center text-sm text-gray-500">
                                No listings found.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>