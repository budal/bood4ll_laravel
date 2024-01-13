<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Base
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'active',
        'starts_at',
        'ends_at',
    ];

    public function calendars(): BelongsToMany
    {
        return $this->belongsToMany(Calendar::class);
    }

    protected function start(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => ucfirst($value) . 'q',
        );
    }

    // public function getStartAttribute()
    // {
    //     return $this->day . '/' . $this->month;
    // }
}
