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
        return $this->belongsToMany(Holiday::class)
            ->join('calendars', 'calendars.id', '=', 'calendar_holiday.calendar_id')
            ->selectRaw("
                CASE
                    WHEN holidays.easter = true AND holidays.operator = '-' THEN Easter(calendars.year) - holidays.difference_start::interval
                    WHEN holidays.easter = true AND holidays.operator = '+' THEN Easter(calendars.year) + holidays.difference_start::interval
                    ELSE TO_TIMESTAMP(calendars.year || '-' || holidays.month || '-' || holidays.day || ' ' || holidays.start_time, 'YYYY-MM-DD HH24:MI:SS')
                END start_at
            ")
            ->selectRaw("
                CASE
                    WHEN holidays.easter = true AND holidays.operator = '-' THEN Easter(calendars.year) - holidays.difference_end::interval
                    WHEN holidays.easter = true AND holidays.operator = '+' THEN Easter(calendars.year) + holidays.difference_end::interval
                    ELSE TO_TIMESTAMP(calendars.year || '-' || holidays.month || '-' || holidays.day || ' ' || holidays.end_time, 'YYYY-MM-DD HH24:MI:SS')
                END end_at
            ");
    }
}
