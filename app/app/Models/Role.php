<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Request;

class Role extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'active',
        'remove_on_expire',
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

    public function scopeFilter($query, Request $request, string $prefix = null, string $orderBy = 'name'): void
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
                $query->where('name', 'ilike', '%'.$search.'%');
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
}
