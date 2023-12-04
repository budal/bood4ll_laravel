<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;
 
class Unit extends Model
{
    use HasFactory, SoftDeletes;

    private $return_count = 0;

    protected $fillable = [
        'name',
        'nickname',
        'founded',
        'parent_id',
        'active',
        'expires',
        'cellphone',
        'landline',
        'email',
        'country',
        'state',
        'city',
        'address',
        'complement',
        'postcode',
        'geo',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
    
    public function children(): HasMany
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }

    public function childrenWithUsersCount(): HasMany
    {
        return $this->children()->withCount('users', 'children');
    }

    public function childrenRecursive() 
    {
        return $this->childrenWithUsersCount()->with('childrenRecursive')->selectRaw("1 as users_nested");
    }

    public function getAllChildren ()
    {
        $sections = collect();

        foreach ($this->childrenWithUsersCount as $section) {
            $sections->push($section);
            $sections = $sections->merge($section->getAllChildren());
        }

        return $sections;
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }
    
    public function parentRecursive() 
    {
        return $this->parent()->with('parentRecursive');
    }
        
    public function getParentsNames() 
    {
        if ($this->parent) {
            return $this->parent->getParentsNames(). " > " . $this->name;
        } else {
            return $this->name;
        }
    }
    
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function scopeFilter($query, array $filters)
    {
        $search = array_filter($filters, function ($key){ return(strpos($key,'search') !== false); }, ARRAY_FILTER_USE_KEY);
        $filterSearch = reset($search);

        $trash = array_filter($filters, function ($key){ return(strpos($key,'trash') !== false); }, ARRAY_FILTER_USE_KEY);
        $filterTrash = reset($trash);

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
        });
    }

    public function scopeSort($query, string $attribute = null): void
    {
        $sort_order = 'ASC';

        if (strncmp($attribute, '-', 1) === 0) {
            $sort_order = 'DESC';
            $attribute = substr($attribute, 1);
        }

        $query->orderBy($attribute, $sort_order);
    }
}
