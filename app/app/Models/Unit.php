<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Unit extends Model
{
    use HasApiTokens;
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
            return $this->parent->getParentsNames() . ' / ' . $this->name;
        } else {
            return $this->name;
        }
    }

    public function getParentsNicknames()
    {
        if ($this->parent) {
            return $this->parent->getParentsNames() . ' / ' . $this->nickname;
        } else {
            return $this->nickname;
        }
    }

    // protected function usersCount(): Attribute
    // {
    //     return Attribute::make(
    //         get: fn (string $value) => "Local: " . $value,
    //     );
    // }
}

// return $query->select('units.*')
// ->selectSub(function ($subquery) {
//     $subquery->selectRaw('COUNT(unit_user.user_id)')
//         ->from('unit_user')
//         ->leftjoin('units', 'unit_user.unit_id', '=', 'units.id')
//         ->whereIn('units.id', function ($query) {
//             $query->select('id')
//                 ->from('units')
//                 ->whereRaw('units.id = units.id OR units.parent_id = units.id');
//         })
//         // ->whereRaw('unit_id IN (
//         //     SELECT (json_array_elements(u.children_id::json)::text)::bigint FROM units u WHERE u.id = units.id
//         // )')
//         ->whereRaw('units.id = units.id OR units.parent_id = units.id');
// }, 'all_users');