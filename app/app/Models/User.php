<?php

namespace App\Models;

use App\Policies\UserPolicy;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'active',
        'provider_name',
        'provider_id',
        'provider_avatar',
        'provider_token',
        'provider_refresh_token',
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

    public function getAbilities()
    {
        return $this->roles()
            ->leftjoin('ability_role', 'ability_role.role_id', '=', 'roles.id')
            ->leftjoin('abilities', 'abilities.id', '=', 'ability_role.ability_id')
            ->select('abilities.name AS ability')
            ->where('roles.superadmin', '<>', true)
            ->where('roles.manager', '<>', true)
            ->where('roles.active', true)
            ->where(function ($query) {
                $query->where('roles.lock_on_expire', true);
                $query->where('roles.expires_at', '>=', 'NOW()');
                $query->orwhere('roles.lock_on_expire', false);
            });
    }

    public function isSuperAdmin()
    {
        return $this->roles()->pluck('superadmin')->contains(true);
    }

    public function isManager()
    {
        return $this->roles()->pluck('manager')->contains(true);
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

    public function scopeUnitsIds($filter, $route)
    {
        $userPolicy = new UserPolicy;

        if ($userPolicy->canManageNestedData($this, $route)) {
            $units = $this->units->map->getDescendants()->flatten();
        } else {
            $units = $this->units->pluck('id');
        }

        return $units;
    }

    // protected function emailVerifiedAt(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn (string $value = null) => $value ? date_format(date_create($value), "d/m/Y H:i:s") : '---',
    //     );
    // }
}
