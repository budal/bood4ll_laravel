<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class VacationPlan extends Base
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'period',
        'owner',
        'year',
        'starts_at',
        'ends_at',
        'implantation',
    ];

    protected function startAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => date_format(date_create($value), "d/m/Y"),
        );
    }

    protected function endAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => date_format(date_create($value), "d/m/Y"),
        );
    }

    protected function implantation(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => date_format(date_create($value), "m/Y"),
        );
    }
}
