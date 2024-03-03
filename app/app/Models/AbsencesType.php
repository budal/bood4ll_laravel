<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class AbsencesType extends Base
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'owner',
        'active',
        'use_vacation_plan',
        'max_duration',
        'working_days',
        'acquisition_period',
    ];
}
