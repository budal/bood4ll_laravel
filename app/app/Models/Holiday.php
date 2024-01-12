<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Holiday extends Base
{
    use HasFactory;

    public function calendars(): BelongsToMany
    {
        return $this->belongsToMany(Calendar::class);
    }
}
