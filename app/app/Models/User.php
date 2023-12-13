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
        return $this->roles->map->abilities->flatten()->pluck('name');
    }

    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function scopeFilter($query, Request $request, string $prefix = null, string $orderBy = 'users.name'): void
    {
        $filters = collect($request->query)->toArray();

        $search = array_filter($filters, function ($key) use ($prefix) {
            return strpos($key, $prefix
                ? ($prefix.'_search')
                : 'search') !== false
            ;
        }, ARRAY_FILTER_USE_KEY);
        $filterSearch = reset($search);

        $trash = array_filter($filters, function ($key) use ($prefix) {
            return strpos($key, $prefix
                ? ($prefix.'_trash')
                : 'trash') !== false
            ;
        }, ARRAY_FILTER_USE_KEY);
        $filterTrash = reset($trash);

        $sort = array_filter($filters, function ($key) use ($prefix) {
            return strpos($key, $prefix
                ? ($prefix.'_sorted')
                : 'sorted') !== false
            ;
        }, ARRAY_FILTER_USE_KEY);
        $filterSort = reset($sort);

        $query->when($filterSearch ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('users.name', 'ilike', '%'.$search.'%')
                    ->orWhere('users.email', 'ilike', '%'.$search.'%');
            });
        })->when($filterTrash ?? null, function ($query, $trashed) {
            if ($trashed === 'both') {
                $query->withTrashed();
            } elseif ($trashed === 'trashed') {
                $query->onlyTrashed();
            }
        })->when($filterSort ? $filterSort : $orderBy, function ($query, $sort) {
            $sort_order = 'ASC';

            if (strncmp($sort, '-', 1) === 0) {
                $sort_order = 'DESC';
                $sort = substr($sort, 1);
            }

            $query->orderBy($sort, $sort_order);
            // $query->orderBy($prefix ? "$prefix.$sort" : $sort, $sort_order);
        });
    }

    public function appendRequest($query, Request $request, string $prefix = null): void
    {
        $query->appends(
            $request->all('users_search', 'users_sorted', 'users_trashed')
        );
    }
}
