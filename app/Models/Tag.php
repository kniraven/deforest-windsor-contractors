<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
        'normalized_name',
    ];

    public function listings(): BelongsToMany
    {
        return $this->belongsToMany(Listing::class)->orderBy('display_name');
    }
}