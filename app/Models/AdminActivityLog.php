<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminActivityLog extends Model
{
    protected $fillable = [
        'listing_id',
        'listing_name',
        'user_id',
        'actor_type',
        'actor_name',
        'actor_email',
        'action',
        'summary',
        'changes',
    ];

    protected $casts = [
        'changes' => 'array',
    ];

    public function listing(): BelongsTo
    {
        return $this->belongsTo(Listing::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}