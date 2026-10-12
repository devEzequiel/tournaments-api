<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model que representa as premiações de um campeonato.
 *
 * Armazena os vencedores de cada categoria: classificação final
 * (1º, 2º, 3º lugar) e premiações individuais.
 *
 * @property int $id Identificador único
 * @property int $championship_id ID do campeonato
 * @property int|null $first_place ID do time campeão
 * @property int|null $second_place ID do time vice-campeão
 * @property int|null $third_place ID do time terceiro colocado
 * @property int|null $best_player ID do melhor jogador do campeonato
 * @property int|null $golden_boot ID do artilheiro (mais gols)
 * @property int|null $golden_glove ID do melhor goleiro
 * @property int|null $playmaker ID do jogador com mais assistências
 */
class Award extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'championship_id',
        'first_place',
        'second_place',
        'third_place',
        'best_player',
        'golden_boot',
        'golden_glove',
        'playmaker',
    ];

    public function getBestPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'best_player', 'id');
    }

    public function bestPlayer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'best_player', 'id');
    }

    public function getGoldenBoot(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'golden_boot', 'id');
    }

    public function goldenBoot(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'golden_boot', 'id');
    }

    public function getGoldenGlove(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'golden_glove', 'id');
    }

    public function getPlaymaker(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'playmaker', 'id');
    }

    public function playmaker(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'playmaker', 'id');
    }

    public function getChampionship()
    {
        return $this->belongsTo(Championship::class);
    }

    public function getFirstPlace()
    {
        return $this->belongsTo(Team::class, 'first_place', 'id');
    }

    public function getSecondPlace()
    {
        return $this->belongsTo(Team::class, 'second_place', 'id');
    }

    public function getThirdPlace()
    {
        return $this->belongsTo(Team::class, 'third_place', 'id');
    }
}
