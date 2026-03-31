<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Listing extends Model
{
    protected $fillable = [
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

    protected $casts = [
        'is_owner_local' => 'boolean',
        'is_locally_independent' => 'boolean',
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_featured' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
    ];

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)->orderBy('name');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(AdminActivityLog::class)->latest();
    }
}