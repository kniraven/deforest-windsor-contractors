@php
    $formatValue = function ($value) {
        if (is_bool($value)) {
            return $value ? 'True' : 'False';
        }

        if ($value === null || $value === '') {
            return '—';
        }

        if (is_array($value)) {
            return count($value) ? implode(', ', $value) : '—';
        }

        return (string) $value;
    };
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Activity Log</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="max-w-7xl mx-auto p-6">
        <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold">Admin Activity Log</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Signed in as {{ auth()->user()->email }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('admin.listings.index') }}" class="inline-flex items-center rounded border px-4 py-2 text-sm">
                    Manage Listings
                </a>

                <a href="{{ route('listings.index') }}" class="inline-flex items-center rounded border px-4 py-2 text-sm">
                    View Homepage
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded border border-red-300 px-4 py-2 text-sm text-red-700">
                        Log Out
                    </button>
                </form>
            </div>
        </div>

        <div class="mb-6 rounded-lg bg-white p-4 shadow">
            <form method="GET" action="{{ route('admin.logs.index') }}" class="grid gap-4 md:grid-cols-3">
                <div>
                    <label for="listing_id" class="mb-1 block text-sm font-medium text-gray-700">Listing ID</label>
                    <input
                        id="listing_id"
                        name="listing_id"
                        type="number"
                        value="{{ $selectedListingId }}"
                        class="w-full rounded border px-3 py-2"
                        placeholder="Optional"
                    >
                </div>

                <div>
                    <label for="action" class="mb-1 block text-sm font-medium text-gray-700">Action</label>
                    <select id="action" name="action" class="w-full rounded border px-3 py-2">
                        <option value="">All actions</option>
                        @foreach ($actionOptions as $value => $label)
                            <option value="{{ $value }}" @selected($selectedAction === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-end gap-3">
                    <button type="submit" class="rounded bg-blue-600 px-4 py-2 text-sm font-medium text-white">
                        Filter Log
                    </button>

                    <a href="{{ route('admin.logs.index') }}" class="rounded border px-4 py-2 text-sm">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <div class="mb-4 flex flex-wrap items-center gap-3 text-sm text-gray-600">
            <span>Total log entries on this page: {{ $logs->count() }}</span>

            @if ($selectedListingId)
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-medium text-slate-700">
                    Filtered to {{ $selectedListingName }}
                </span>
            @endif
        </div>

        <div class="space-y-4">
            @forelse ($logs as $log)
                <div class="rounded-lg bg-white p-5 shadow">
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">
                                    {{ $actionOptions[$log->action] ?? \Illuminate\Support\Str::headline(str_replace('_', ' ', $log->action)) }}
                                </span>

                                <span class="text-xs text-gray-500">
                                    {{ $log->created_at?->format('Y-m-d H:i:s') }}
                                </span>
                            </div>

                            <h2 class="mt-2 text-lg font-semibold">
                                {{ $log->listing_name ?: 'Unknown Listing' }}
                            </h2>

                            <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-gray-500">
                                @if ($log->listing_id)
                                    <span>Listing ID: {{ $log->listing_id }}</span>
                                    <a href="{{ route('admin.logs.index', ['listing_id' => $log->listing_id]) }}" class="text-blue-600 underline">
                                        View This Listing's History
                                    </a>
                                @endif

                                @if ($log->listing)
                                    <a href="{{ route('admin.listings.edit', $log->listing) }}" class="text-blue-600 underline">
                                        Edit Listing
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="text-sm text-gray-600 sm:text-right">
                            <div><strong>Actor Type:</strong> {{ ucfirst($log->actor_type) }}</div>

                            @if ($log->actor_name)
                                <div><strong>Name:</strong> {{ $log->actor_name }}</div>
                            @endif

                            @if ($log->actor_email)
                                <div><strong>Email:</strong> {{ $log->actor_email }}</div>
                            @endif
                        </div>
                    </div>

                    <p class="mt-4 text-sm text-gray-800">
                        {{ $log->summary }}
                    </p>

                    @if (!empty($log->changes))
                        <details class="mt-4 rounded border border-slate-200 bg-slate-50 p-4">
                            <summary class="cursor-pointer text-sm font-semibold text-slate-800">
                                Show change details
                            </summary>

                            <div class="mt-4 grid gap-3">
                                @foreach ($log->changes as $field => $change)
                                    <div class="rounded border bg-white p-3">
                                        <div class="mb-2 text-sm font-semibold text-slate-900">
                                            {{ \Illuminate\Support\Str::headline($field) }}
                                        </div>

                                        <div class="grid gap-2 md:grid-cols-2">
                                            <div>
                                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                    Before
                                                </div>
                                                <div class="mt-1 rounded bg-slate-100 px-3 py-2 text-sm text-slate-800">
                                                    {{ $formatValue($change['before'] ?? null) }}
                                                </div>
                                            </div>

                                            <div>
                                                <div class="text-xs font-semibold uppercase tracking-wide text-gray-500">
                                                    After
                                                </div>
                                                <div class="mt-1 rounded bg-slate-100 px-3 py-2 text-sm text-slate-800">
                                                    {{ $formatValue($change['after'] ?? null) }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </details>
                    @endif
                </div>
            @empty
                <div class="rounded-lg bg-white p-6 text-center text-sm text-gray-500 shadow">
                    No log entries found.
                </div>
            @endforelse
        </div>

        <div class="mt-6">
            {{ $logs->links() }}
        </div>
    </div>
</body>
</html>