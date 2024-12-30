<?php

namespace App\Modules\Player;

use App\Contracts\Analytic\PlayerAnalyticContract;
use App\Models\Player;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class PlayerAnalyticService implements PlayerAnalyticContract
{
    public function getPlayersAnalytics()
    {
        return Player::query()
            ->select(
                'players.id',
                'players.name',
                'teams.id as team_id',
                'teams.name as team_name',
                'teams.first_color',
                'teams.second_color',
                DB::raw('COUNT(DISTINCT player_rates.id) as matches'),
                DB::raw('COUNT(DISTINCT goals.id) as goals'),
                DB::raw('COUNT(DISTINCT assists.id) as assists'),
                DB::raw('ROUND(AVG(player_rates.rate), 1) as average_rate'), // Arredonda para 1 casa decimal
                DB::raw('COUNT(CASE WHEN awards.best_player = players.id THEN 1 END) as best_player_awards'),
                DB::raw('COUNT(CASE WHEN awards.golden_boot = players.id THEN 1 END) as golden_boot_awards'),
                DB::raw('COUNT(CASE WHEN awards.playmaker = players.id THEN 1 END) as playmaker_awards')
            )
            ->leftJoin('team_player', function ($join) {
                $join->on('players.id', '=', 'team_player.player_id')
                    ->where('team_player.current_team', '=', true);
            })
            ->leftJoin('teams', 'teams.id', '=', 'team_player.team_id')
            ->leftJoin('player_rates', 'players.id', '=', 'player_rates.player_id')
            ->leftJoin('goals', function ($join) {
                $join->on('players.id', '=', 'goals.scorer_id');
            })
            ->leftJoin('goals as assists', 'players.id', '=', 'assists.assist_id')
            ->leftJoin('awards', function ($join) {
                $join->on('players.id', '=', 'awards.best_player')
                    ->orOn('players.id', '=', 'awards.golden_boot')
                    ->orOn('players.id', '=', 'awards.playmaker');
            })
            ->groupBy('players.id', 'players.name', 'teams.id')
            ->orderBy('players.id')
            ->get();
    }
}
