<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model que representa um gol marcado em uma partida.
 * 
 * Registra quem marcou, quem deu a assistência, e se foi
 * gol de pênalti ou gol contra.
 * 
 * @property int $id Identificador único do gol
 * @property int $fixture_id ID da partida em que o gol foi marcado
 * @property int $scorer_id ID do jogador que marcou o gol
 * @property int|null $assist_id ID do jogador que deu a assistência
 * @property bool $pk Se foi gol de pênalti (penalty kick)
 * @property bool $own_goal Se foi gol contra
 */
class Goal extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'fixture_id',
        'scorer_id',
        'assist_id',
        'pk',
        'own_goal'
    ];
}
