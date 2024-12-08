<?php

namespace App\Models;

class Team extends BaseModel
{
    protected $fillable = [
        'name',
        'first_color',
        'second_color'
    ];

    public function players(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TeamPlayer::class, 'team_player', 'team_id', 'player_id');
    }

    public function fixtures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Fixture::class);
    }
}
