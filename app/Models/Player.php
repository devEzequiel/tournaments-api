<?php

namespace App\Models;

class Player extends BaseModel
{
    protected $fillable = [
        'name',
        'team_id'
    ];

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function previousTeams()
    {
        return $this->hasMany(PreviousPlayerTeam::class);
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
