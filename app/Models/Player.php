<?php

namespace App\Models;

class Player extends BaseModel
{
    protected $fillable = [
        'name',
        'team_id'
    ];

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(TeamPlayer::class, 'team_player', 'player_id', 'team_id');
    }

    public function goals(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class, 'scorer_id')
            ->selectRaw('player_id, count(*) as goals')
            ->groupBy('player_id');
    }

    public function assists(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class, 'assist_id')
            ->selectRaw('player_id, count(*) as assists')
            ->groupBy('player_id');
    }

    public function currentTeam(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(TeamPlayer::class, 'team_id', 'id')
            ->where('current_team', 1);
    }

    public function oldTeams(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TeamPlayer::class, 'team_id', 'id')
            ->where('current_team', 0);
    }

    public function getGoalsByTeam(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Goal::class, 'scorer_id')
            ->selectRaw('team_id, count(*) as goals')
            ->groupBy('team_id');
    }

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

    public function getRate(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PlayerRate::class, 'player_id', 'id')
            ->selectRaw('avg(rate) as rate')
            ->groupBy('player_id');
    }

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
