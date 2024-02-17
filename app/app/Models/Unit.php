<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
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

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function getDescendants()
    {
        return $this->descendantsAndSelf->pluck('id');
    }

    public function getTotalUsers()
    {
        return Unit::join('unit_user', 'units.id', '=', 'unit_user.unit_id')
            ->whereIn('units.id', $this->descendantsAndSelf->pluck('id'))
            ->count('unit_user.user_id');
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

    protected function usersCount(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => "Local: " . $value,
        );
    }

    protected function usersAllCount(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => "Total: " . $value,
        );
    }
}
