<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @method static create(array $data)
 */
class Fixture extends BaseModel
{
    public $timestamps = false;
    protected $fillable = [
        'championship_id',
        'home_team_id',
        'away_team_id',
        'round_number',
        'game_number',
        'home_goals',
        'away_goals',
        'playoff_round', //1 final, 2 semi, 3 terceiro lugar, 4 quartas
        'is_played', //0 to play, 1 played
        'played_at'
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
