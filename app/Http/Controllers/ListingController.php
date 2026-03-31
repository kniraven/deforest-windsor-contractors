<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class ListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Listing::query()
            ->with('tags')
            ->where('is_active', true)
            ->where('submission_status', 'approved');

        $searchTerm = trim((string) $request->input('q'));

        if ($searchTerm !== '') {
            $search = '%' . $searchTerm . '%';

            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('display_name', 'like', $search)
                    ->orWhere('listing_type', 'like', $search)
                    ->orWhere('service_type', 'like', $search)
                    ->orWhere('other_service_type', 'like', $search)
                    ->orWhere('short_description', 'like', $search)
                    ->orWhere('municipality', 'like', $search)
                    ->orWhere('legal_structure', 'like', $search)
                    ->orWhere('other_legal_structure', 'like', $search)
                    ->orWhereHas('tags', function ($tagQuery) use ($search) {
                        $tagQuery->where('name', 'like', $search);
                    });
            });
        }

        $selectedListingTypes = collect((array) $request->input('listing_type', []))
            ->filter()
            ->values();

        if ($selectedListingTypes->isNotEmpty()) {
            $query->whereIn('listing_type', $selectedListingTypes->all());
        }

        $selectedLocalPriorities = collect((array) $request->input('local_priority', []))
            ->filter()
            ->values();

        if ($selectedLocalPriorities->contains('independent-local')) {
            $query->where('is_locally_independent', true);
        }

        if ($selectedLocalPriorities->contains('owner-local')) {
            $query->where('is_owner_local', true);
        }

        $sort = $request->input('sort', 'local_priority');

        /*
        |--------------------------------------------------------------------------
        | Public homepage ordering rule
        |--------------------------------------------------------------------------
        |
        | Featured listings should always rise to the top first.
        | After that, the user's selected sort determines the order within each
        | featured / non-featured group.
        |
        */
        $query->orderByDesc('is_featured');

        switch ($sort) {
            case 'name_az':
                $query->orderBy('display_name');
                break;

            case 'name_za':
                $query->orderByDesc('display_name');
                break;

            case 'municipality_az':
                $query->orderBy('municipality')
                    ->orderBy('display_name');
                break;

            case 'listing_type_az':
                $query->orderBy('listing_type')
                    ->orderBy('display_name');
                break;

            case 'local_priority':
            default:
                $query->orderByRaw("
                        CASE
                            WHEN is_locally_independent = 1 AND is_owner_local = 1 THEN 0
                            WHEN is_locally_independent = 1 THEN 1
                            WHEN is_owner_local = 1 THEN 2
                            ELSE 3
                        END
                    ")
                    ->orderBy('display_name');
                break;
        }

        $listings = $query->get();

        $mapListings = $listings
            ->filter(function ($listing) {
                return $listing->latitude !== null && $listing->longitude !== null;
            })
            ->map(function ($listing) {
                return [
                    'name' => $listing->display_name,
                    'latitude' => (float) $listing->latitude,
                    'longitude' => (float) $listing->longitude,
                    'url' => route('listings.show', $listing),
                ];
            })
            ->values();

        return view('listings.index', [
            'listings' => $listings,
            'mapListings' => $mapListings,
        ]);
    }

    public function show(Listing $listing)
    {
        abort_unless(
            $listing->is_active && $listing->submission_status === 'approved',
            404
        );

        return view('listings.show', [
            'listing' => $listing->load('tags'),
        ]);
    }
}