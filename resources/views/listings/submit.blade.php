<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit a Listing</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="kn-page-shell">
    <div class="mx-auto max-w-4xl px-4 py-6 sm:px-6 lg:px-8">
        <p class="mb-4">
            <a href="{{ route('listings.index') }}" class="kn-link-subtle">Back to Directory</a>
        </p>

        <div class="kn-panel rounded-2xl p-5 sm:p-6">
            <h1 class="text-3xl font-bold tracking-tight">
                Submit a Listing
            </h1>

            <p class="mt-2 text-sm leading-6 kn-body-text">
                Use this form to submit a business, individual service, or nonprofit listing for review.
                Submissions are not public until they are approved by an admin.
            </p>

            @if (session('status'))
                <div class="mt-6 rounded-xl border border-emerald-300 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mt-6 rounded border border-red-300 bg-red-50 px-4 py-3 text-red-800">
                    <p class="mb-2 font-semibold">Please fix the following:</p>
                    <ul class="list-disc space-y-1 pl-5 text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('listings.submit.store') }}"
                enctype="multipart/form-data"
                class="mt-6 grid gap-6"
            >
                @csrf

                @include('listings.forms._shared_fields', ['listing' => null])

                <section class="grid gap-4">
                    <h2 class="border-b pb-2 text-lg font-semibold">
                        Supporting Documents <span class="font-normal text-gray-500">(optional)</span>
                    </h2>

                    <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                        You may optionally attach up to 3 files to help verify your business, address,
                        or authority to represent this listing. These files are only sent with the review
                        email and are not meant to be permanently stored by the site.
                    </div>

                    <div>
                        <label for="supporting_documents" class="mb-1 block text-sm font-medium">
                            Upload Documents
                            <span class="font-normal text-gray-500">
                                (PDF, JPG, JPEG, PNG — up to 3 files, 5 MB each)
                            </span>
                        </label>

                        <input
                            id="supporting_documents"
                            name="supporting_documents[]"
                            type="file"
                            multiple
                            accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full rounded border px-3 py-2 @error('supporting_documents') border-red-500 @enderror @error('supporting_documents.*') border-red-500 @enderror"
                        >

                        <p class="mt-1 text-xs text-gray-500">
                            Examples: proof of business, proof of address, supporting verification documents, or other relevant records.
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

                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-700">
                    Submitted listings start as pending review. They are not active, verified, or featured until an admin approves them.
                </div>

                <div>
                    <button type="submit" class="kn-btn-primary">
                        Submit Listing
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>