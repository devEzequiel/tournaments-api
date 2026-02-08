<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Model que representa uma partida (fixture) no sistema.
 * 
 * Uma fixture é um confronto entre dois times em um campeonato.
 * Pode ser uma partida de fase de grupos ou de playoffs.
 * 
 * @property int $id Identificador único da partida
 * @property int $championship_id ID do campeonato
 * @property int $home_team_id ID do time mandante
 * @property int $away_team_id ID do time visitante
 * @property int $round_number Número da rodada
 * @property int $game_number Número do jogo na rodada
 * @property int|null $home_goals Gols do time mandante
 * @property int|null $away_goals Gols do time visitante
 * @property int|null $playoff_round Rodada do playoff (1=final, 2=semi, etc)
 * @property bool $is_played Se a partida já foi realizada
 * @property bool $is_playoff Se é jogo de playoff
 * @property string|null $playoff_stage Estágio do playoff ('semifinal', 'final')
 * @property int|null $playoff_game_number Número do jogo no playoff (1, 2, 3)
 * @property bool $decided_by_penalty Se foi decidido por pênaltis
 * @property \Carbon\Carbon|null $played_at Data em que a partida foi realizada
 * 
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
        'is_playoff', // Se é jogo de playoff
        'playoff_stage', // 'semifinal', 'final'
        'playoff_game_number', // 1, 2, 3
        'decided_by_penalty', // Se foi decidido por pênaltis
        'played_at'
    ];

    /**
     * Retorna o time mandante da partida.
     * 
     * @return BelongsTo Relacionamento com o time da casa
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id', 'id');
    }

    /**
     * Retorna o time visitante da partida.
     * 
     * @return BelongsTo Relacionamento com o time visitante
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id', 'id');
    }

    /**
     * Retorna o campeonato ao qual a partida pertence.
     * 
     * @return BelongsTo Relacionamento com o campeonato
     */
    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }
}
