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

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function abilities()
    {
        return $this->roles()
            ->where('active', true)
            ->where(function ($query) {
                $query->where('lock_on_expire', false);
                $query->orWhere(function ($query) {
                    $query->where('lock_on_expire', true);
                    $query->where('expires_at', '>=', 'now()');
                });
            })
            ->get()
            ->map->abilities->flatten()->pluck('name');
    }

    public function isSuperAdmin()
    {
        return $this->roles()
            ->where('active', true)
            ->where('superadmin', true)
            ->first() ? true : false;
    }

    public function scopeFilter($query, Request $request, string $prefix = null, array $options = []): void
    {
        $base = new Base();
        $base->scopeFilter($query, $request, $prefix, $options);
    }
}
