<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Listing</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 text-gray-900">
    <div class="max-w-4xl mx-auto p-6">
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p>
                <a href="{{ route('admin.listings.index') }}" class="text-blue-600 underline">Back to Manage Listings</a>
            </p>

            <div class="flex flex-wrap items-center gap-3 text-sm">
                <span class="text-gray-500">{{ auth()->user()->email }}</span>

                <a href="{{ route('listings.index') }}" class="inline-flex items-center rounded border px-4 py-2">
                    View Homepage
                </a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded border border-red-300 px-4 py-2 text-red-700">
                        Log Out
                    </button>
                </form>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-3xl font-bold mb-2">Create Listing</h1>
            <p class="text-sm text-gray-600 mb-6">Add a new business, individual, or nonprofit listing to the directory.</p>

            @if ($errors->any())
                <div class="mb-6 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                    <p class="font-semibold mb-2">Please fix the following:</p>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.listings.store') }}" class="grid gap-6">
                @csrf

                @include('listings.forms._shared_fields', ['listing' => null])
                @include('listings.forms._admin_fields', ['listing' => null])

                <div>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save Listing</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>