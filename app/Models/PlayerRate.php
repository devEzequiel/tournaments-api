<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model que representa a avaliação de um jogador em uma partida.
 * 
 * Permite avaliar o desempenho individual de cada jogador
 * após cada partida disputada.
 * 
 * @property int $id Identificador único
 * @property int $fixture_id ID da partida avaliada
 * @property int $player_id ID do jogador avaliado
 * @property float $rate Nota de avaliação (geralmente de 0 a 10)
 */
class PlayerRate extends Model
{
    use HasFactory;

    protected $table = 'player_rates';
    public $timestamps = false;
    protected $fillable = [
        'fixture_id',
        'player_id',
        'rate'
    ];

    /**
     * Retorna o jogador avaliado.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function player()
    {
        $this->belongsTo(Player::class);
    }

    /**
     * Retorna a partida em que a avaliação foi feita.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function fixture()
    {
        $this->belongsTo(Fixture::class);
    }

}
