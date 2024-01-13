<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Calendar extends Base
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'owner',
        'active',
        'year',
    ];

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class);
    }

    public function holidays(): BelongsToMany
    {
        return $this->belongsToMany(Holiday::class);
    }
}
