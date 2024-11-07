<?php

namespace App\Models;

class Team extends BaseModel
{
    protected $fillable = [
        'name',
        'first_color',
        'second_color'
    ];

    public function players(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Player::class);
    }
}
