<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Ability extends Base
{
    protected $fillable = [
        'name',
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function scopeSync($query, string $mode, array $list): Ability
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
}
