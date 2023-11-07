<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbilityRole extends Model
{
    use HasFactory;

    protected $table = 'ability_role';

    protected $fillable = [
        'role_id',
        'ability_id',
    ];
}
