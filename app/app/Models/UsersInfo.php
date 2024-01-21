<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class UsersInfo extends Base
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'photo',
        'birthday',
        'naturalness',
        'nationality',
        'cellphone',
        'landline',
        'address',
        'country',
        'postcode',
        'geo',
        'geo_verified',
        'general_record',
        'individual_registration',
        'driver_licence',
        'voter_registration',
        'social_security_card',
        'passaport_number',
        'measurements',
        'dependents',
        'contacts',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
