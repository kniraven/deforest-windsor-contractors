<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Listing Update</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="kn-page-shell">
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <p class="mb-4">
            <a href="{{ route('listings.show', $listing) }}" class="kn-link-subtle">Back to Listing</a>
        </p>

        <div class="kn-panel rounded-2xl p-5 sm:p-6">
            <h1 class="text-3xl font-bold tracking-tight">
                Request an Update or Takedown
            </h1>

            <p class="mt-2 text-sm leading-6 kn-body-text">
                Use this form if you need something changed on <strong class="text-slate-100">{{ $listing->display_name }}</strong> or if you want to request that it be removed from the directory.
            </p>

            @if (session('status'))
                <div class="mt-6 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                    <p class="font-semibold mb-2">Please fix the following:</p>
                    <ul class="list-disc pl-5 space-y-1 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('listings.requests.store', $listing) }}" enctype="multipart/form-data" class="mt-6 grid gap-6">
                @csrf

                <div>
                    <label for="request_type" class="block text-sm font-medium mb-1">Request Type</label>
                    <select
                        id="request_type"
                        name="request_type"
                        class="kn-select px-4 py-2.5 text-base @error('request_type') border-red-500 @enderror"
                    >
                        <option value="">Choose one</option>
                        <option value="change" @selected(old('request_type') === 'change')>Request a change</option>
                        <option value="takedown" @selected(old('request_type') === 'takedown')>Request a takedown</option>
                    </select>
                    @error('request_type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div>
                        <label for="requester_name" class="block text-sm font-medium mb-1">Your Name</label>
                        <input
                            id="requester_name"
                            name="requester_name"
                            type="text"
                            class="kn-input px-4 py-2.5 text-base @error('requester_name') border-red-500 @enderror"
                            value="{{ old('requester_name') }}"
                            placeholder="Your full name"
                        >
                        @error('requester_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="requester_email" class="block text-sm font-medium mb-1">Your Email</label>
                        <input
                            id="requester_email"
                            name="requester_email"
                            type="email"
                            class="kn-input px-4 py-2.5 text-base @error('requester_email') border-red-500 @enderror"
                            value="{{ old('requester_email') }}"
                            placeholder="you@example.com"
                        >
                        @error('requester_email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="message" class="block text-sm font-medium mb-1">Details</label>
                    <textarea
                        id="message"
                        name="message"
                        rows="7"
                        class="kn-input px-4 py-2.5 text-base @error('message') border-red-500 @enderror"
                        placeholder="Explain what needs to be changed, corrected, or removed."
                    >{{ old('message') }}</textarea>
                    <p class="mt-1 text-xs kn-muted-text">
                        For takedown requests, explain your connection to the listing and why it should be removed.
                    </p>
                    @error('message')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <section class="grid gap-4">
                    <h2 class="text-lg font-semibold">
                        Supporting Documents <span class="text-sm font-normal text-slate-400">(optional)</span>
                    </h2>

                    <div class="rounded-xl border border-slate-700 bg-slate-900/40 px-4 py-3 text-sm text-slate-300">
                        You may optionally attach up to 3 files to help show that you have authority to request this change or takedown.
                        These files are only sent with the admin review email and are not meant to be permanently stored by the site.
                    </div>

                    <div>
                        <label for="supporting_documents" class="block text-sm font-medium mb-1">
                            Upload Documents <span class="text-slate-400 font-normal">(PDF, JPG, JPEG, PNG — up to 3 files, 5 MB each)</span>
                        </label>

                        <input
                            id="supporting_documents"
                            name="supporting_documents[]"
                            type="file"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="kn-input px-4 py-2.5 text-base @error('supporting_documents') border-red-500 @enderror @error('supporting_documents.*') border-red-500 @enderror"
                        >

                        <p class="mt-1 text-xs kn-muted-text">
                            Examples: proof of business ownership, proof of address, or documentation showing you are authorized to request the change.
                        </p>

                        @error('supporting_documents')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror

                        @foreach ($errors->get('supporting_documents.*') as $messages)
                            @foreach ($messages as $message)
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @endforeach
                        @endforeach
                    </div>
                </section>

                <div>
                    <button type="submit" class="kn-btn-primary">
                        Send Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>