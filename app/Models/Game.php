<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends BaseModel
{
    protected $fillable = [
        'championship_id',
        'home_team_id',
        'away_team_id',
        'away_goals',
        'home_goals',
        'fixture'
    ];

    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id', 'id');
    }

    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id', 'id');
    }

    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }
}
