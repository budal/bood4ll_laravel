<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\SoftDeletes;

class Holiday extends Base
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'active',
        'starts_at',
        'ends_at',
    ];

    protected function startAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => "Start: " . date_format(date_create($value), "d/m/y H:i"),
        );
    }

    protected function endAt(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => "End: " . date_format(date_create($value), "d/m/y H:i"),
        );
    }

    public function scopeGetDates($query, $year = null): void
    {
        $year = $year ?? date("Y");

        $query->selectRaw("
        CASE
            WHEN holidays.easter = true AND holidays.operator = '-' THEN Easter(" . $year . ") - holidays.difference_start::interval
            WHEN holidays.easter = true AND holidays.operator = '+' THEN Easter(" . $year . ") + holidays.difference_start::interval
            ELSE TO_TIMESTAMP(" . $year . " || '-' || holidays.month || '-' || holidays.day || ' ' || holidays.start_time, 'YYYY-MM-DD HH24:MI:SS')
        END start_at
    ")
            ->selectRaw("
        CASE
            WHEN holidays.easter = true AND holidays.operator = '-' THEN Easter(" . $year . ") - holidays.difference_end::interval
            WHEN holidays.easter = true AND holidays.operator = '+' THEN Easter(" . $year . ") + holidays.difference_end::interval
            ELSE TO_TIMESTAMP(" . $year . " || '-' || holidays.month || '-' || holidays.day || ' ' || holidays.end_time, 'YYYY-MM-DD HH24:MI:SS')
        END end_at
    ");
    }
}
