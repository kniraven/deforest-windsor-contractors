<section class="grid gap-4">
    <h2 class="text-lg font-semibold border-b pb-2">Admin Notes and Status</h2>

    <div>
        <label for="submission_status" class="block text-sm font-medium mb-1">Submission Status</label>
        <select
            id="submission_status"
            name="submission_status"
            class="w-full border rounded px-3 py-2 @error('submission_status') border-red-500 @enderror"
        >
            @foreach ($submissionStatuses as $submissionStatus)
                <option value="{{ $submissionStatus }}" @selected(old('submission_status', $listing->submission_status ?? 'approved') === $submissionStatus)>
                    {{ ucfirst($submissionStatus) }}
                </option>
            @endforeach
        </select>
        @error('submission_status')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="internal_notes" class="block text-sm font-medium mb-1">Internal Notes <span class="text-gray-500 font-normal">(optional)</span></label>
        <textarea
            id="internal_notes"
            name="internal_notes"
            class="w-full border rounded px-3 py-2 @error('internal_notes') border-red-500 @enderror"
            rows="4"
            placeholder="Private notes for admin use only"
        >{{ old('internal_notes', $listing->internal_notes ?? '') }}</textarea>
        @error('internal_notes')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid sm:grid-cols-3 gap-3">
        <label class="flex items-center gap-2 border rounded px-3 py-2">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $listing->is_active ?? true))>
            <span>Active</span>
        </label>

        <label class="flex items-center gap-2 border rounded px-3 py-2">
            <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified', $listing->is_verified ?? false))>
            <span>Verified</span>
        </label>

        <label class="flex items-center gap-2 border rounded px-3 py-2">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $listing->is_featured ?? false))>
            <span>Featured</span>
        </label>
    </div>
</section>