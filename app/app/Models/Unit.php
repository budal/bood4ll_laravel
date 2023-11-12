<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
 

class Unit extends Model
{
    use HasFactory, SoftDeletes;

    private $return_count = 0;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
    
    public function parent()
    {
        return $this->belongsTo(Unit::class, 'parent_id');
    }

    public function getParentsNames() {
        if($this->parent) {
            return $this->parent->getParentsNames(). " > " . $this->name;
        } else {
            return $this->name;
        }
    }
    
    public function parentRecursive() {
        return $this->parent()->with('parentRecursive');
    }
    
    public function children()
    {
        return $this->hasMany(Unit::class, 'parent_id');
    }

    public function childrenRecursive() {
        return $this->children()->with('childrenRecursive');
    }
     
    public function resolveRouteBinding($value, $field = null)
    {
        return $this->where($field ?? 'id', $value)->withTrashed()->firstOrFail();
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'ilike', '%'.$search.'%');
            });
        })->when($filters['trashed'] ?? null, function ($query, $trashed) {
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
