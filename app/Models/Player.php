<?php

namespace App\Models;

/**
 * Model que representa um jogador no sistema.
 * 
 * Um jogador pode pertencer a diferentes times ao longo do tempo,
 * marcar gols, dar assistências e receber avaliações de desempenho.
 * 
 * @property int $id Identificador único do jogador
 * @property string $name Nome do jogador
 * @property \Carbon\Carbon $created_at Data de criação
 * @property \Carbon\Carbon $updated_at Data de atualização
 */
class Player extends BaseModel
{
    protected $fillable = [
        'name'
    ];

    /**
     * Retorna o(s) time(s) do jogador através da tabela pivot.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TeamPlayer::class, 'team_player', 'player_id', 'team_id');
    }

    /**
     * Retorna os gols marcados pelo jogador, agrupados por jogador.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function goals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class, 'scorer_id')
            ->selectRaw('player_id, count(*) as goals')
            ->groupBy('player_id');
    }

    /**
     * Retorna as assistências do jogador, agrupadas por jogador.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function assists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class, 'assist_id')
            ->selectRaw('player_id, count(*) as assists')
            ->groupBy('player_id');
    }

    /**
     * Retorna o time atual do jogador.
     * 
     * Filtra pela flag current_team = 1 na tabela pivot.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentTeam(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TeamPlayer::class, 'team_id', 'id')
            ->where('current_team', 1);
    }

    /**
     * Retorna os times anteriores do jogador.
     * 
     * Histórico de times pelos quais o jogador passou.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function oldTeams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeamPlayer::class, 'team_id', 'id')
            ->where('current_team', 0);
    }

    /**
     * Retorna os gols do jogador agrupados por time.
     * 
     * Útil para análise de performance por equipe.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getGoalsByTeam(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class, 'scorer_id')
            ->selectRaw('team_id, count(*) as goals')
            ->groupBy('team_id');
    }

    /**
     * Retorna as assistências do jogador agrupadas por time.
     * 
     * Útil para análise de performance por equipe.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getAssistsByTeam(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class, 'assist_id')
            ->selectRaw('team_id, count(*) as assists')
            ->groupBy('team_id');
    }

    public function getRateByTeam(int $team_id): \Illuminate\Database\Eloquent\Relations\HasMany
    {
//        return $this->hasMany(PlayerRate::class, 'player_id', 'id')
//            ->selectRaw('team_id, avg(rate) as rate')
//            ->whereHas('team_player', function ($query) use ($team_id) {
//                $query->selectRaw('joined_at,, left current_team')
//            })
//            ->groupBy('team_id');
    }

    /**
     * Retorna a média de avaliação (rating) do jogador.
     * 
     * Calcula a média de todas as avaliações recebidas.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getRate(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlayerRate::class, 'player_id', 'id')
            ->selectRaw('avg(rate) as rate')
            ->groupBy('player_id');
    }

    /**
     * Retorna as premiações individuais do jogador.
     * 
     * Conta quantas vezes foi: melhor jogador, artilheiro,
     * melhor goleiro e melhor assistente.
     * 
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getAwards()
    {
        return $this->hasMany(Award::class, 'player_id', 'id')
            ->selectRaw("
        player_id,
        SUM(IF(best_player = id, 1, 0)) as best_player_count,
        SUM(IF(golden_boot = id, 1, 0)) as golden_boot_count,
        SUM(IF(golden_glove = id, 1, 0)) as golden_glove_count,
        SUM(IF(playmaker = id, 1, 0)) as playmaker_count
    ")
            ->groupBy('player_id');
    }
}
