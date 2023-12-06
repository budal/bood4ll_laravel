<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Ramsey\Uuid\Uuid;

class User extends Authenticatable
{
    use HasUUids, HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class);
    }
    
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }
    
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function scopeFilter($query, Request $request, string $prefix = null, string $orderBy = 'name'): void
    {
        $filters = collect($request->query)->toArray();
        
        $search = array_filter($filters, function ($key) use ($prefix) { 
            return (strpos($key, $prefix 
                ? ($prefix . '_' . 'search') 
                : 'search') !== false
            );
        }, ARRAY_FILTER_USE_KEY);
        $filterSearch = reset($search);

        $trash = array_filter($filters, function ($key) use ($prefix) { 
            return (strpos($key, $prefix 
                ? ($prefix . '_' . 'trash') 
                : 'trash') !== false
            ); 
        }, ARRAY_FILTER_USE_KEY);
        $filterTrash = reset($trash);

        $sort = array_filter($filters, function ($key) use ($prefix) { 
            return (strpos($key, $prefix 
                ? ($prefix . '_' . 'sorted') 
                : 'sorted') !== false
            ); 
        }, ARRAY_FILTER_USE_KEY);
        $filterSort = reset($sort);

        $query->when($filterSearch ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'ilike', '%'.$search.'%')
                    ->orWhere('email', 'ilike', '%'.$search.'%');
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
        });
    }

    public function appendRequest($query, Request $request, string $prefix = null): void
    {
        $query->appends(
            $request->all('users_search', 'users_sorted', 'users_trashed')
        );
    }
}
