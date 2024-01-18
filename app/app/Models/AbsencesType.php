<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AbsencesType extends Base
{
    use HasFactory;
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
