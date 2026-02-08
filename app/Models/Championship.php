<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model que representa um campeonato no sistema.
 * 
 * Um campeonato pode ter múltiplas partidas (fixtures), times participantes,
 * e premiações ao final. Suporta tanto fase de pontos corridos quanto playoffs.
 * 
 * @property int $id Identificador único do campeonato
 * @property string $name Nome do campeonato
 * @property string|null $description Descrição do campeonato
 * @property int $rounds Número de rodadas (turnos) do campeonato
 * @property bool $playoffs Se o campeonato possui fase de playoffs
 * @property string|null $playoff_type Tipo de playoff ('final', 'semifinal')
 * @property int|null $playoff_rounds Número de rodadas dos playoffs
 * @property \Carbon\Carbon|null $started_at Data de início do campeonato
 * @property \Carbon\Carbon|null $finished_at Data de término do campeonato
 */
class Championship extends BaseModel
{

    public $timestamps = false;
    protected $fillable = [
        'name',
        'description',
        'rounds',
        'playoffs',
        'playoff_type',
        'playoff_rounds',
        'started_at',
        'finished_at',
    ];

    /**
     * Retorna todas as partidas (fixtures) do campeonato.
     * 
     * @return HasMany Relacionamento com as partidas
     */
    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    /**
     * Retorna todas as premiações do campeonato.
     * 
     * Inclui: campeão, vice, terceiro lugar, artilheiro,
     * melhor jogador, melhor goleiro e melhor assistente.
     * 
     * @return HasMany Relacionamento com as premiações
     */
    public function awards()
    {
        return $this->hasMany(Award::class);
    }

    /**
     * Retorna todas as partidas ainda não realizadas do campeonato.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de fixtures pendentes
     */
    public function getUnplayedFixtures()
    {
        return $this->fixtures()->where('is_played', false)->get();
    }

    /**
     * Retorna todas as partidas já realizadas do campeonato.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de fixtures concluídas
     */
    public function getPlayedFixtures()
    {
        return $this->fixtures()->where('is_played', true)->get();
    }
}
