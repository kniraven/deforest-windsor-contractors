<?php

namespace App\Http\Controllers;

use App\Mail\ListingChangeRequestReceived;
use App\Mail\ListingChangeRequestSubmitted;
use App\Models\AdminActivityLog;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class ListingChangeRequestController extends Controller
{
    public function create(Listing $listing)
    {
        $this->abortIfListingIsNotPublic($listing);

        return view('listings.request', [
            'listing' => $listing,
        ]);
    }

    public function store(Request $request, Listing $listing)
    {
        $this->abortIfListingIsNotPublic($listing);

        $validated = $request->validate([
            'request_type' => ['required', Rule::in(['change', 'takedown'])],
            'requester_name' => ['required', 'string', 'max:255'],
            'requester_email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $notificationEmail = config('directory.submission_notification_email');

        if (filled($notificationEmail)) {
            try {
                Mail::to($notificationEmail)->send(
                    new ListingChangeRequestSubmitted($listing, $validated)
                );
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        try {
            Mail::to($validated['requester_email'])->send(
                new ListingChangeRequestReceived($listing, $validated)
            );
        } catch (Throwable $exception) {
            report($exception);
        }

        AdminActivityLog::create([
            'listing_id' => $listing->id,
            'listing_name' => $listing->display_name,
            'user_id' => null,
            'actor_type' => 'public',
            'actor_name' => $validated['requester_name'],
            'actor_email' => $validated['requester_email'],
            'action' => $validated['request_type'] === 'takedown'
                ? 'takedown_requested'
                : 'change_requested',
            'summary' => $validated['request_type'] === 'takedown'
                ? 'A public takedown request was submitted.'
                : 'A public change request was submitted.',
            'changes' => [
                'request_type' => [
                    'before' => null,
                    'after' => $validated['request_type'],
                ],
                'message' => [
                    'before' => null,
                    'after' => $validated['message'],
                ],
            ],
        ]);

        return redirect()
            ->route('listings.requests.create', $listing)
            ->with('status', 'Thanks. Your request was sent to the directory admin.');
    }

    private function abortIfListingIsNotPublic(Listing $listing): void
    {
        abort_unless(
            $listing->is_active && $listing->submission_status === 'approved',
            404
        );
    }
}