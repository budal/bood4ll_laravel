<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Base
{
    use HasFactory;
    use SoftDeletes;

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

    private $descendants;

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->where('primary', true);
    }

    public function children(): HasMany
    {
        return $this->hasMany(Self::class, 'parent_id', 'id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }





    public function hasChildren()
    {
        if ($this->children->count()) {
            return true;
        }
        return false;
    }

    public function findDescendants(Unit $unit)
    {
        $this->descendants[] = $unit->id;

        if ($unit->hasChildren()) {
            foreach ($unit->children as $child) {
                $this->findDescendants($child);
            }
        }
    }

    public function getDescendants()
    {
        $this->findDescendants($this);
        return $this->descendants;
    }

    function getDepth($category, $level = 0)
    {
        if ($category->parent_id > 0) {
            if ($category->parent) {
                $level++;
                return $this->getDepth($category->parent, $level);
            }
        }
        return $level;
    }







    public function childrenWithUsersCount(): HasMany
    {
        return $this->children()->withCount('users')->with('childrenWithUsersCount');
    }

    public function getAllChildren()
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
        return $this->belongsTo(Self::class, 'parent_id');
    }

    public function parentRecursive()
    {
        return $this->parent()->with('parentRecursive');
    }

    public function getParentsNames()
    {
        if ($this->parent) {
            return $this->parent->getParentsNames() . ' > ' . $this->name;
        } else {
            return $this->name;
        }
    }

    public function getParentsNicknames()
    {
        if ($this->parent) {
            return $this->parent->getParentsNames() . ' > ' . $this->nickname;
        } else {
            return $this->nickname;
        }
    }
}
