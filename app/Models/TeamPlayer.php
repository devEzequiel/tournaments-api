<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model que representa o relacionamento entre jogador e time (tabela pivot).
 * 
 * Registra o histórico de passagens de um jogador por diferentes times,
 * incluindo datas de entrada e saída.
 * 
 * @property int $id Identificador único
 * @property int $player_id ID do jogador
 * @property int $team_id ID do time
 * @property bool $current_team Se é o time atual do jogador
 * @property \Carbon\Carbon|null $joined_at Data em que entrou no time
 * @property \Carbon\Carbon|null $left_at Data em que saiu do time
 */
class TeamPlayer extends Model
{
    use HasFactory;

    protected $table = "team_player";

    public $timestamps = false;
    protected $fillable = [
        'player_id',
        'team_id',
        'current_team',
        'joined_at',
        'left_at'
    ];




}
