<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Base
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'owner',
        'active',
        'lock_on_expire',
        'starts_at',
        'ends_at',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
