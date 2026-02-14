<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Model que representa um time no sistema.
 * 
 * Um time pode ter múltiplos jogadores e participar de diversas partidas.
 * As cores são usadas para identificação visual no frontend.
 * 
 * @property int $id Identificador único do time
 * @property string $name Nome do time
 * @property string|null $first_color Cor primária do time (hex)
 * @property string|null $second_color Cor secundária do time (hex)
 * @property \Carbon\Carbon $created_at Data de criação
 * @property \Carbon\Carbon $updated_at Data de atualização
 */
class Team extends BaseModel
{
    protected $fillable = [
        'name',
        'first_color',
        'second_color'
    ];


    /**
     * Retorna todos os jogadores que pertencem a este time.
     * 
     * Relacionamento many-to-many através da tabela pivot 'team_player'.
     * 
     * @return BelongsToMany Relacionamento com os jogadores
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class,
            'team_player', 'team_id',
            'player_id', 'id', 'id');
    }

    /**
     * Retorna todas as partidas em que o time participa.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany Relacionamento com as partidas
     */
    public function fixtures(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Fixture::class);
    }
}
