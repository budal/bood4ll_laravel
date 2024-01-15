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

    public function schedules(): BelongsToMany
    {
        return $this->belongsToMany(Schedule::class);
    }

    public function absences(): BelongsToMany
    {
        return $this->belongsToMany(Absence::class);
    }

    public function getAllAbilities()
    {
        return $this->roles()
            ->leftjoin('ability_role', 'ability_role.role_id', '=', 'roles.id')
            ->leftjoin('abilities', 'abilities.id', '=', 'ability_role.ability_id')
            ->select(
                'abilities.name AS ability',
                'roles.id AS role_id',
                'roles.superadmin',
                'roles.manager',
                'roles.active',
                'roles.lock_on_expire',
                'roles.expires_at',
                'roles.full_access',
                'roles.manage_nested',
                'roles.remove_on_change_unit',
                'roles.owner',
            )
            ->where('roles.active', true)
            ->where(function ($query) {
                $query->where('roles.lock_on_expire', true);
                $query->where('roles.expires_at', '>=', 'NOW()');
                $query->orwhere('roles.lock_on_expire', false);
            });
    }

    public function isSuperAdmin()
    {
        return $this->getAllAbilities()->pluck('superadmin')->contains(true) ? true : false;
    }

    public function isManager()
    {
        return $this->getAllAbilities()->pluck('manager')->contains(true) ? true : false;
    }

    public function hasFullAccess()
    {
        return $this->getAllAbilities()
            ->where('full_access', true)
            ->where('abilities.name', '!=', null)
            ->pluck('ability')
            ->contains(Route::current()->getName());
    }

    public function canManageNested()
    {
        return $this->getAllAbilities()
            ->where('manage_nested', true)
            ->where('abilities.name', '!=', null)
            ->pluck('ability')
            ->contains(Route::current()->getName());
    }

    public function canRemoveOnChangeUnit()
    {
        return $this->getAllAbilities()
            ->where('remove_on_change_unit', true)
            ->where('abilities.name', '!=', null)
            ->pluck('ability')
            ->contains(Route::current()->getName());
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
            $units = $this->units->map->getDescendants()->flatten();
        } else {
            $units = $this->units->pluck('id');
        }

        return $units;
    }
}
