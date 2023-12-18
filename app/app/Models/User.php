<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Route;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasUUids;
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted()
    {
        // static::creating(fn(User $user) => $user->uuid = (string) Uuid::uuid4());
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class);
    }

    public function unitsChildren()
    {
        return Unit::get();
    }

    public function abilities()
    {
        return $this->roles()
            ->select('roles.id', 'roles.name', 'abilities.name AS ability', 'roles.superadmin', 'roles.manager', 'roles.full_access', 'roles.lock_on_expire', 'roles.expires_at')
            ->leftjoin('ability_role', 'ability_role.role_id', '=', 'roles.id')
            ->leftjoin('abilities', 'abilities.id', '=', 'ability_role.ability_id')
            ->where('active', true);
    }

    public function isSuperAdmin()
    {
        return $this->abilities()->pluck('superadmin')->contains(true) ? true : false;
    }

    public function isManager()
    {
        return $this->abilities()->pluck('manager')->contains(true) ? true : false;
    }

    public function hasFullAccess()
    {
        return $this->abilities()->where('full_access', true)->pluck('ability')->contains(Route::current()->getName()) ? true : false;
    }

    public function canManageNested()
    {
        return $this->abilities()->where('manage_nested', true)->pluck('ability')->contains(Route::current()->getName()) ? true : false;
    }

    public function unitsClassified(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class)
            ->where('primary', true)
            ->select('shortpath as name')
            ->orderBy('shortpath');
    }

    public function unitsWorking()
    {
        return $this->belongsToMany(Unit::class)
            ->where('primary', false)
            ->select('shortpath as name')
            ->orderBy('shortpath');
    }

    public function scopeFilter($query, Request $request, string $prefix = null, array $options = []): void
    {
        $base = new Base();
        $base->scopeFilter($query, $request, $prefix, $options);
    }

    public function scopeUnitsIds()
    {
        if ($this->canManageNested() === true) {
            return $this->units()->get()->flatten()->pluck('id')->union(
                $this->units()->get()->map->getAllChildren()->flatten()->pluck('id')
            );
        } else {
            return $this->units()->get()->flatten()->pluck('id');
        }
    }
}
