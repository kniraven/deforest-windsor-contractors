<?php

namespace App\Http\Controllers;

use App\Mail\ListingSubmittedForReview;
use App\Models\AdminActivityLog;
use App\Models\Listing;
use App\Models\Tag;
use App\Support\ListingAnswerInterpreter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class ListingSubmissionController extends Controller
{
    public function create()
    {
        return view('listings.submit', [
            'listingTypes' => config('listings.listing_types'),
            'municipalities' => config('listings.municipalities'),
            'serviceTypes' => config('listings.service_types'),
            'legalStructures' => config('listings.legal_structures'),
            'answerOptions' => config('listings.answer_options'),
        ]);
    }

    public function store(Request $request)
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
            'supporting_documents' => ['nullable', 'array', 'max:3'],
            'supporting_documents.*' => ['file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ]);

        $parsedTags = $this->parseTagsInput($request->input('tags_input'));
        $supportingDocuments = $this->extractSupportingDocuments(
            $request->file('supporting_documents', [])
        );

        unset($validated['tags_input'], $validated['supporting_documents']);

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

        $validated['submission_status'] = 'pending';
        $validated['is_active'] = false;
        $validated['is_verified'] = false;
        $validated['is_featured'] = false;
        $validated['internal_notes'] = null;

        $listing = null;

        DB::transaction(function () use ($validated, $parsedTags, &$listing) {
            $listing = Listing::create($validated);
            $this->syncTags($listing, $parsedTags);
        });

        if ($listing) {
            AdminActivityLog::create([
                'listing_id' => $listing->id,
                'listing_name' => $listing->display_name,
                'user_id' => null,
                'actor_type' => 'public',
                'actor_name' => $listing->display_name,
                'actor_email' => $listing->email,
                'action' => 'submission_created',
                'summary' => 'A public listing was submitted and is waiting for admin review.',
                'changes' => [
                    'submission_status' => [
                        'before' => null,
                        'after' => $listing->submission_status,
                    ],
                    'is_active' => [
                        'before' => null,
                        'after' => $listing->is_active,
                    ],
                    'is_verified' => [
                        'before' => null,
                        'after' => $listing->is_verified,
                    ],
                    'is_featured' => [
                        'before' => null,
                        'after' => $listing->is_featured,
                    ],
                ],
            ]);
        }

        $notificationEmail = config('directory.submission_notification_email');

        if ($listing && filled($notificationEmail)) {
            try {
                Mail::to($notificationEmail)->send(
                    new ListingSubmittedForReview(
                        $listing->load('tags'),
                        $supportingDocuments
                    )
                );
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        return redirect()
            ->route('listings.submit.create')
            ->with('status', 'Thanks. Your listing was submitted for review and is not public yet.');
    }

    private function extractSupportingDocuments(array $uploadedFiles): array
    {
        return collect($uploadedFiles)
            ->filter(function ($file) {
                return $file instanceof UploadedFile && $file->isValid();
            })
            ->map(function (UploadedFile $file) {
                return [
                    'name' => $file->getClientOriginalName(),
                    'mime' => $file->getMimeType() ?: 'application/octet-stream',
                    'content' => file_get_contents($file->getRealPath()),
                ];
            })
            ->filter(function ($fileData) {
                return $fileData['content'] !== false;
            })
            ->values()
            ->all();
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