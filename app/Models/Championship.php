<?php

namespace App\Models;

class Championship extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'rounds',
        'playoffs'
    ];

    public function players(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function teams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Team::class);
    }
}
