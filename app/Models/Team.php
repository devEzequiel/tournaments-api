<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Team extends BaseModel
{
    protected $fillable = [
        'name',
        'first_color',
        'second_color'
    ];


    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class,
            'team_player', 'team_id',
            'player_id', 'id', 'id');
    }

    public function fixtures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Fixture::class);
    }
}
