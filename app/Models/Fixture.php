<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Fixture extends BaseModel
{
    protected $fillable = [
        'championship_id',
        'home_team_id',
        'away_team_id',
        'round_number',
        'game_number'
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
