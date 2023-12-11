<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ability extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function scopeFilter($query, array $filters)
    {
        $query->when($filters['search'] ?? null, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('name', 'ilike', '%'.$search.'%');
            });
        });
    }

    public function scopeSync($query, string $mode, array $list)
    {
        $query->when($mode, function ($query, $mode) use ($list) {
            if ($mode === 'on') {
                $this->total = 0;
                foreach ($list as $name) {
                    if (Ability::updateOrCreate(['name' => $name])) {
                        ++$this->total;
                    }
                }
            } elseif ($mode === 'off') {
                $this->total = Ability::whereIn('name', $list)->delete();
            } elseif ($mode === 'toggle') {
                $this->name = $list[0];

                if ($ability = Ability::where('name', $this->name)->first()) {
                    if ($ability->delete()) {
                        $this->action = 'delete';
                    }
                } else {
                    if (Ability::updateOrCreate(['name' => $this->name])) {
                        $this->action = 'insert';
                    }
                }
            }
        });

        return $this;
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
