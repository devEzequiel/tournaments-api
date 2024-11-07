<?php

namespace App\Models;

class Player extends BaseModel
{
    protected $fillable = [
        'name',
        'team_id'
    ];

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TeamPlayer::class, 'team_player', 'player_id', 'team_id');
    }

    public function goals()
    {
        return $this->hasMany(Goal::class, 'scorer_id');
    }

    public function assists()
    {
        return $this->hasMany(Goal::class, 'assist_id');
    }

    public function awards()
    {
        return $this->hasMany(Award::class);
    }
}
