<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Base
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'active',
        'lock_on_expire',
        'expires_at',
        'full_access',
        'manage_nested',
        'remove_on_change_unit',
    ];

    public function abilities(): BelongsToMany
    {
        return $this->belongsToMany(Ability::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
