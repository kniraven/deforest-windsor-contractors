<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\Listing;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $actionOptions = [
            'submission_created' => 'Public Submission Created',
            'change_requested' => 'Public Change Requested',
            'takedown_requested' => 'Public Takedown Requested',
            'admin_created' => 'Admin Created Listing',
            'admin_updated' => 'Admin Updated Listing',
            'admin_deleted' => 'Admin Deleted Listing',
        ];

        $selectedListingId = $request->filled('listing_id')
            ? (int) $request->input('listing_id')
            : null;

        $selectedAction = trim((string) $request->input('action'));

        $query = AdminActivityLog::query()
            ->with(['listing', 'user'])
            ->latest();

        if ($selectedListingId) {
            $query->where('listing_id', $selectedListingId);
        }

        if ($selectedAction !== '' && array_key_exists($selectedAction, $actionOptions)) {
            $query->where('action', $selectedAction);
        } else {
            $selectedAction = '';
        }

        $logs = $query->paginate(50)->withQueryString();

        $selectedListingName = null;

        if ($selectedListingId) {
            $selectedListing = Listing::find($selectedListingId);

            $selectedListingName = $selectedListing?->display_name
                ?? $logs->first()?->listing_name
                ?? ('Listing #' . $selectedListingId);
        }

        return view('admin.logs.index', [
            'logs' => $logs,
            'actionOptions' => $actionOptions,
            'selectedListingId' => $selectedListingId,
            'selectedListingName' => $selectedListingName,
            'selectedAction' => $selectedAction,
        ]);
    }
}