<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\ListingSubmissionDecision;
use App\Models\AdminActivityLog;
use App\Models\Listing;
use App\Models\Tag;
use App\Support\ListingAnswerInterpreter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $allowedSorts = [
            'display_name',
            'listing_type',
            'service_type',
            'municipality',
            'submission_status',
            'local_priority',
            'created_at',
        ];

        $currentSort = $request->input('sort', 'display_name');
        $currentDirection = strtolower($request->input('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($currentSort, $allowedSorts, true)) {
            $currentSort = 'display_name';
        }

        $query = Listing::with('tags');

        switch ($currentSort) {
            case 'listing_type':
            case 'municipality':
            case 'submission_status':
            case 'created_at':
            case 'display_name':
                $query->orderBy($currentSort, $currentDirection);
                break;

            case 'service_type':
                $query->orderBy('service_type', $currentDirection)
                    ->orderBy('other_service_type', $currentDirection)
                    ->orderBy('display_name');
                break;

            case 'local_priority':
                $query->orderByRaw("
                    CASE
                        WHEN is_locally_independent = 1 AND is_owner_local = 1 THEN 0
                        WHEN is_locally_independent = 1 THEN 1
                        WHEN is_owner_local = 1 THEN 2
                        ELSE 3
                    END " . strtoupper($currentDirection)
                )->orderBy('display_name');
                break;
        }

        $listings = $query->get();

        return view('admin.listings.index', [
            'listings' => $listings,
            'currentSort' => $currentSort,
            'currentDirection' => $currentDirection,
        ]);
    }

    public function create()
    {
        return view('admin.listings.create', $this->formOptions());
    }

    public function store(Request $request)
    {
        $validated = $this->validateListing($request, true);
        $validated = $this->applySubmissionStatusSideEffects($validated, null);
        $parsedTags = $this->parseTagsInput($request->input('tags_input'));

        $listing = null;

        DB::transaction(function () use ($validated, $parsedTags, &$listing) {
            $listing = Listing::create($validated);
            $this->syncTags($listing, $parsedTags);
            $listing->load('tags');
        });

        if ($listing) {
            $changes = $this->buildChangeSet(
                [],
                $listing->only($this->trackedFields()),
                [],
                $listing->tags->pluck('name')->sort()->values()->all()
            );

            $this->createAdminLog(
                $listing->id,
                $listing->display_name,
                'admin_created',
                'Admin created this listing.',
                $changes
            );
        }

        return redirect()->route('admin.listings.index');
    }

    public function edit(Listing $listing)
    {
        return view('admin.listings.edit', array_merge(
            $this->formOptions(),
            ['listing' => $listing->load('tags')]
        ));
    }

    public function update(Request $request, Listing $listing)
    {
        $listing->load('tags');

        $originalSubmissionStatus = $listing->submission_status;
        $originalAttributes = $listing->only($this->trackedFields());
        $originalTags = $listing->tags->pluck('name')->sort()->values()->all();

        $validated = $this->validateListing($request, false);
        $validated = $this->applySubmissionStatusSideEffects($validated, $originalSubmissionStatus);
        $parsedTags = $this->parseTagsInput($request->input('tags_input'));

        DB::transaction(function () use ($listing, $validated, $parsedTags) {
            $listing->update($validated);
            $this->syncTags($listing, $parsedTags);
        });

        $listing->refresh()->load('tags');

        $updatedAttributes = $listing->only($this->trackedFields());
        $updatedTags = $listing->tags->pluck('name')->sort()->values()->all();

        $changes = $this->buildChangeSet(
            $originalAttributes,
            $updatedAttributes,
            $originalTags,
            $updatedTags
        );

        if (!empty($changes)) {
            $this->createAdminLog(
                $listing->id,
                $listing->display_name,
                'admin_updated',
                'Admin updated this listing.',
                $changes
            );
        }

        $this->sendSubmissionDecisionNotificationIfNeeded(
            $listing,
            $originalSubmissionStatus
        );

        return redirect()->route('admin.listings.index');
    }

    public function destroy(Listing $listing)
    {
        $listing->load('tags');

        $originalAttributes = $listing->only($this->trackedFields());
        $originalTags = $listing->tags->pluck('name')->sort()->values()->all();
        $listingId = $listing->id;
        $listingName = $listing->display_name;

        $changes = $this->buildChangeSet(
            $originalAttributes,
            [],
            $originalTags,
            []
        );

        $this->createAdminLog(
            $listingId,
            $listingName,
            'admin_deleted',
            'Admin deleted this listing.',
            $changes
        );

        $listing->delete();

        return redirect()->route('admin.listings.index');
    }

    private function formOptions(): array
    {
        return [
            'listingTypes' => config('listings.listing_types'),
            'municipalities' => config('listings.municipalities'),
            'serviceTypes' => config('listings.service_types'),
            'legalStructures' => config('listings.legal_structures'),
            'answerOptions' => config('listings.answer_options'),
            'submissionStatuses' => config('listings.submission_statuses'),
        ];
    }

    private function validateListing(Request $request, bool $isCreate): array
    {
        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'listing_type' => ['required', Rule::in(config('listings.listing_types'))],
            'service_type' => ['required', Rule::in(config('listings.service_types'))],
            'other_service_type' => [
                Rule::requiredIf($request->input('service_type') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'short_description' => ['required', 'string'],
            'tags_input' => ['nullable', 'string', 'max:1000'],
            'municipality' => ['required', Rule::in(config('listings.municipalities'))],
            'submission_status' => ['required', Rule::in(config('listings.submission_statuses'))],
            'legal_structure' => ['nullable', Rule::in(config('listings.legal_structures'))],
            'other_legal_structure' => [
                Rule::requiredIf($request->input('legal_structure') === 'Other'),
                'nullable',
                'string',
                'max:255',
            ],
            'latitude' => ['nullable', 'numeric', 'required_with:longitude'],
            'longitude' => ['nullable', 'numeric', 'required_with:latitude'],
            'local_connection_answer' => ['nullable', Rule::in(array_keys(config('listings.answer_options')))],
            'independent_operation_answer' => ['nullable', Rule::in(array_keys(config('listings.answer_options')))],
            'parent_affiliation_answer' => ['nullable', Rule::in(array_keys(config('listings.answer_options')))],
            'street_address' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'website_url' => ['nullable', 'url', 'max:255'],
            'internal_notes' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
            'is_verified' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        unset($validated['tags_input']);

        if (($validated['service_type'] ?? null) !== 'Other') {
            $validated['other_service_type'] = null;
        }

        if (($validated['legal_structure'] ?? null) !== 'Other') {
            $validated['other_legal_structure'] = null;
        }

        if (($validated['listing_type'] ?? null) === 'individual') {
            $validated['parent_affiliation_answer'] = null;
        }

        $validated['is_owner_local'] = ListingAnswerInterpreter::deriveOwnerLocal(
            $validated['local_connection_answer'] ?? null
        );

        $validated['is_locally_independent'] = ListingAnswerInterpreter::deriveLocallyIndependent(
            $validated['listing_type'],
            $validated['independent_operation_answer'] ?? null,
            $validated['parent_affiliation_answer'] ?? null
        );

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_verified'] = $request->boolean('is_verified');
        $validated['is_featured'] = $request->boolean('is_featured');

        if ($isCreate && empty($validated['submission_status'])) {
            $validated['submission_status'] = 'approved';
        }

        return $validated;
    }

    private function applySubmissionStatusSideEffects(array $validated, ?string $originalSubmissionStatus): array
    {
        $newSubmissionStatus = $validated['submission_status'] ?? null;

        if ($newSubmissionStatus !== $originalSubmissionStatus) {
            if ($newSubmissionStatus === 'approved') {
                $validated['is_active'] = true;
            }

            if (in_array($newSubmissionStatus, ['pending', 'rejected'], true)) {
                $validated['is_active'] = false;
            }
        }

        return $validated;
    }

    private function parseTagsInput(?string $tagsInput): array
    {
        if ($tagsInput === null || trim($tagsInput) === '') {
            return [];
        }

        $rawTags = explode(',', $tagsInput);
        $parsedTags = [];

        foreach ($rawTags as $rawTag) {
            $displayName = $this->normalizeDisplayTag($rawTag);

            if ($displayName === '') {
                continue;
            }

            if (Str::length($displayName) > 50) {
                throw ValidationException::withMessages([
                    'tags_input' => 'Each tag must be 50 characters or less.',
                ]);
            }

            $normalizedName = $this->normalizeTag($displayName);

            if ($normalizedName === '') {
                continue;
            }

            $parsedTags[$normalizedName] = [
                'name' => $displayName,
                'normalized_name' => $normalizedName,
            ];
        }

        if (count($parsedTags) > 10) {
            throw ValidationException::withMessages([
                'tags_input' => 'You can enter up to 10 tags.',
            ]);
        }

        return array_values($parsedTags);
    }

    private function syncTags(Listing $listing, array $parsedTags): void
    {
        if (empty($parsedTags)) {
            $listing->tags()->sync([]);

            return;
        }

        $tagIds = [];

        foreach ($parsedTags as $tagData) {
            $tag = Tag::firstOrCreate(
                ['normalized_name' => $tagData['normalized_name']],
                ['name' => $tagData['name']]
            );

            $tagIds[] = $tag->id;
        }

        $listing->tags()->sync($tagIds);
    }

    private function sendSubmissionDecisionNotificationIfNeeded(
        Listing $listing,
        ?string $originalSubmissionStatus
    ): void {
        if (!filled($listing->email)) {
            return;
        }

        $newSubmissionStatus = $listing->submission_status;

        if ($newSubmissionStatus === $originalSubmissionStatus) {
            return;
        }

        if (!in_array($newSubmissionStatus, ['approved', 'rejected'], true)) {
            return;
        }

        try {
            Mail::to($listing->email)->send(
                new ListingSubmissionDecision($listing, $newSubmissionStatus)
            );
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function createAdminLog(
        ?int $listingId,
        ?string $listingName,
        string $action,
        string $summary,
        ?array $changes = null
    ): void {
        $user = auth()->user();

        AdminActivityLog::create([
            'listing_id' => $listingId,
            'listing_name' => $listingName,
            'user_id' => $user?->id,
            'actor_type' => 'admin',
            'actor_name' => $user?->name,
            'actor_email' => $user?->email,
            'action' => $action,
            'summary' => $summary,
            'changes' => empty($changes) ? null : $changes,
        ]);
    }

    private function trackedFields(): array
    {
        return [
            'display_name',
            'listing_type',
            'service_type',
            'other_service_type',
            'short_description',
            'municipality',
            'submission_status',
            'legal_structure',
            'other_legal_structure',
            'latitude',
            'longitude',
            'local_connection_answer',
            'independent_operation_answer',
            'parent_affiliation_answer',
            'is_owner_local',
            'is_locally_independent',
            'is_active',
            'street_address',
            'postal_code',
            'phone',
            'email',
            'website_url',
            'is_verified',
            'is_featured',
            'internal_notes',
        ];
    }

    private function buildChangeSet(
        array $beforeAttributes,
        array $afterAttributes,
        array $beforeTags = [],
        array $afterTags = []
    ): array {
        $changes = [];

        foreach ($this->trackedFields() as $field) {
            $beforeValue = $beforeAttributes[$field] ?? null;
            $afterValue = $afterAttributes[$field] ?? null;

            if ($beforeValue != $afterValue) {
                $changes[$field] = [
                    'before' => $beforeValue,
                    'after' => $afterValue,
                ];
            }
        }

        sort($beforeTags);
        sort($afterTags);

        if ($beforeTags !== $afterTags) {
            $changes['tags'] = [
                'before' => array_values($beforeTags),
                'after' => array_values($afterTags),
            ];
        }

        return $changes;
    }

    private function normalizeDisplayTag(string $tag): string
    {
        return Str::of($tag)
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }

    private function normalizeTag(string $tag): string
    {
        return Str::of($tag)
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->lower()
            ->toString();
    }
}