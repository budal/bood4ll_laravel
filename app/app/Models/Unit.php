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
    use \Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

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
        return $this->belongsToMany(User::class);
    }

    // public function children(): HasMany
    // {
    //     return $this->hasMany(Self::class, 'parent_id', 'id');
    // }

    // public function descendants()
    // {
    //     return $this->children()->with('descendants');
    // }

    // public function parent(): BelongsTo
    // {
    //     return $this->belongsTo(Self::class, 'parent_id');
    // }

    // public function ancestors()
    // {
    //     return $this->parent()->with('ancestors');
    // }

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
