<?php

namespace App\Services\Analytics;

use App\Contracts\Analytic\PlayerAnalyticContract;
use App\Models\Player;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class PlayerAnalyticService extends BaseService implements PlayerAnalyticContract
{

    public function __construct()
    {
        parent::__construct(new Player::class);
    }

    public static function getPlayersData()
    {
        return Player::query()
            ->leftJoin('team_player', function ($join) {
                $join->on('players.id', '=', 'team_player.player_id')
                    ->where('team_player.current_team', '=', 1);
            })
            ->leftJoin('teams', 'teams.id', '=', 'team_player.team_id')
            ->leftJoin('fixtures as home_fixtures', function ($join) {
                $join->on('teams.id', '=', 'home_fixtures.home_team_id')
                    ->where('home_fixtures.is_played', '=', 1);
            })
            ->leftJoin('fixtures as away_fixtures', function ($join) {
                $join->on('teams.id', '=', 'away_fixtures.away_team_id')
                    ->where('away_fixtures.is_played', '=', 1);
            })
            ->leftJoin('goals as g', 'g.scorer_id', '=', 'players.id')
            ->leftJoin('goals as a', 'a.assist_id', '=', 'players.id')
            ->leftJoin('player_rates', 'player_rates.player_id', '=', 'players.id')
            ->leftJoin('awards', function ($join) {
                $join->on('awards.best_player', '=', 'players.id')
                    ->orOn('awards.golden_boot', '=', 'players.id')
                    ->orOn('awards.golden_glove', '=', 'players.id')
                    ->orOn('awards.playmaker', '=', 'players.id');
            })
            ->select(
                'players.id',
                'players.name',
                'teams.name as team_name',
                DB::raw('COUNT(DISTINCT g.id) as total_goals'),
                DB::raw('COUNT(DISTINCT a.id) as total_assists'),
                DB::raw('AVG(player_rates.rate) as average_rate'),
                DB::raw('COUNT(DISTINCT awards.best_player) as best_player_awards'),
                DB::raw('COUNT(DISTINCT awards.golden_boot) as golden_boot_awards'),
                DB::raw('COUNT(DISTINCT awards.golden_glove) as golden_glove_awards'),
                DB::raw('COUNT(DISTINCT awards.playmaker) as playmaker_awards'),
                DB::raw('COUNT(DISTINCT home_fixtures.id) + COUNT(DISTINCT away_fixtures.id) as games_played'),
                DB::raw('IFNULL(COUNT(DISTINCT g.id) / NULLIF((COUNT(DISTINCT home_fixtures.id) + COUNT(DISTINCT away_fixtures.id)), 0), 0) as goals_per_game')
            )
            ->groupBy('players.id', 'players.name', 'teams.name')
            ->get();
    }
}
