<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Schedule extends Base
{
    use HasFactory;
    use SoftDeletes;

    public function calendars(): BelongsToMany
    {
        return $this->belongsToMany(Calendar::class);
    }
}
