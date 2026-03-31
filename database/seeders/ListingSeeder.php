<?php

namespace Database\Seeders;

use App\Models\Listing;
use App\Models\Tag;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('listing_tag')->delete();
        DB::table('tags')->delete();
        DB::table('listings')->delete();

        $listings = [
            [
                'display_name' => 'Kniraven LLC',
                'listing_type' => 'business',
                'service_type' => 'Web Development',
                'other_service_type' => null,
                'short_description' => 'Local web and software development services based in DeForest.',
                'municipality' => 'DeForest',
                'legal_structure' => 'Single-Member LLC',
                'other_legal_structure' => null,
                'latitude' => 43.2486000,
                'longitude' => -89.3437000,
                'is_owner_local' => true,
                'is_locally_independent' => true,
                'is_active' => true,
                'street_address' => null,
                'postal_code' => null,
                'phone' => null,
                'email' => 'nickolas@kniraven.com',
                'website_url' => 'https://kniraven.com',
                'is_verified' => false,
                'is_featured' => false,
                'internal_notes' => 'Starter business listing for MVP testing.',
                'created_at' => now(),
                'updated_at' => now(),
                'tags' => [
                    'custom websites',
                    'software development',
                    'web apps',
                    'small business support',
                ],
            ],
            [
                'display_name' => 'Nickolas Patino',
                'listing_type' => 'individual',
                'service_type' => 'Web Development',
                'other_service_type' => null,
                'short_description' => 'Local software and web development services in the DeForest area.',
                'municipality' => 'DeForest',
                'legal_structure' => null,
                'other_legal_structure' => null,
                'latitude' => 43.2486000,
                'longitude' => -89.3437000,
                'is_owner_local' => true,
                'is_locally_independent' => true,
                'is_active' => true,
                'street_address' => null,
                'postal_code' => null,
                'phone' => null,
                'email' => 'nickolas@kniraven.com',
                'website_url' => 'https://kniraven.com',
                'is_verified' => false,
                'is_featured' => false,
                'internal_notes' => 'Starter individual listing for MVP testing.',
                'created_at' => now(),
                'updated_at' => now(),
                'tags' => [
                    'freelance',
                    'custom websites',
                    'software help',
                ],
            ],
            [
                'display_name' => 'Windsor Handy Help',
                'listing_type' => 'business',
                'service_type' => 'Handyman',
                'other_service_type' => null,
                'short_description' => 'General home repair and small project help in Windsor.',
                'municipality' => 'Windsor',
                'legal_structure' => 'Sole Proprietorship',
                'other_legal_structure' => null,
                'latitude' => 43.2186000,
                'longitude' => -89.2843000,
                'is_owner_local' => true,
                'is_locally_independent' => true,
                'is_active' => true,
                'street_address' => null,
                'postal_code' => null,
                'phone' => null,
                'email' => null,
                'website_url' => null,
                'is_verified' => false,
                'is_featured' => false,
                'internal_notes' => 'Starter handyman listing for MVP testing.',
                'created_at' => now(),
                'updated_at' => now(),
                'tags' => [
                    'small jobs',
                    'free estimates',
                    'weekend availability',
                    'home repair',
                ],
            ],
            [
                'display_name' => 'DeForest Lawn Care',
                'listing_type' => 'business',
                'service_type' => 'Landscaping',
                'other_service_type' => null,
                'short_description' => 'Lawn care and seasonal yard work for local homes.',
                'municipality' => 'DeForest',
                'legal_structure' => 'Sole Proprietorship',
                'other_legal_structure' => null,
                'latitude' => 43.2478000,
                'longitude' => -89.3360000,
                'is_owner_local' => true,
                'is_locally_independent' => true,
                'is_active' => true,
                'street_address' => null,
                'postal_code' => null,
                'phone' => null,
                'email' => null,
                'website_url' => null,
                'is_verified' => false,
                'is_featured' => false,
                'internal_notes' => 'Starter landscaping listing for MVP testing.',
                'created_at' => now(),
                'updated_at' => now(),
                'tags' => [
                    'lawn mowing',
                    'seasonal cleanup',
                    'yard work',
                    'residential',
                ],
            ],
            [
                'display_name' => 'Sarah Jensen',
                'listing_type' => 'individual',
                'service_type' => 'House Cleaning',
                'other_service_type' => null,
                'short_description' => 'Independent house cleaning services for local homes.',
                'municipality' => 'Windsor',
                'legal_structure' => null,
                'other_legal_structure' => null,
                'latitude' => 43.2149000,
                'longitude' => -89.2815000,
                'is_owner_local' => true,
                'is_locally_independent' => true,
                'is_active' => true,
                'street_address' => null,
                'postal_code' => null,
                'phone' => null,
                'email' => null,
                'website_url' => null,
                'is_verified' => false,
                'is_featured' => false,
                'internal_notes' => 'Starter cleaning listing for MVP testing.',
                'created_at' => now(),
                'updated_at' => now(),
                'tags' => [
                    'housekeeping',
                    'recurring cleaning',
                    'pet friendly',
                ],
            ],
            [
                'display_name' => 'Badger Electrical Services',
                'listing_type' => 'business',
                'service_type' => 'Electrical',
                'other_service_type' => null,
                'short_description' => 'Residential electrical work for local repair and upgrade jobs.',
                'municipality' => 'DeForest',
                'legal_structure' => 'Corporation',
                'other_legal_structure' => null,
                'latitude' => 43.2512000,
                'longitude' => -89.3459000,
                'is_owner_local' => true,
                'is_locally_independent' => false,
                'is_active' => true,
                'street_address' => null,
                'postal_code' => null,
                'phone' => null,
                'email' => null,
                'website_url' => null,
                'is_verified' => false,
                'is_featured' => false,
                'internal_notes' => 'Starter electrical listing for MVP testing.',
                'created_at' => now(),
                'updated_at' => now(),
                'tags' => [
                    'electrical upgrades',
                    'residential service',
                    'insured',
                ],
            ],
        ];

        foreach ($listings as $listingData) {
            $tags = $listingData['tags'];
            unset($listingData['tags']);

            $listing = Listing::create($listingData);
            $listing->tags()->sync($this->resolveTagIds($tags));
        }
    }

    private function resolveTagIds(array $tags): array
    {
        return collect($tags)
            ->map(function ($tag) {
                $displayName = Str::of($tag)
                    ->replaceMatches('/\s+/', ' ')
                    ->trim()
                    ->toString();

                $normalizedName = Str::of($displayName)
                    ->lower()
                    ->toString();

                return Tag::firstOrCreate(
                    ['normalized_name' => $normalizedName],
                    ['name' => $displayName]
                )->id;
            })
            ->all();
    }
}