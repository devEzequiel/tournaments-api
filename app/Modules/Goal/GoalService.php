<?php

namespace App\Modules\Goal;

use App\Contracts\Analytic\GoalContract;
use App\Models\Goal;
use App\Models\PlayerRate;
use App\Models\TeamPlayer;
use Illuminate\Support\Facades\DB;
use App\Services\BaseService;

class GoalService extends BaseService implements GoalContract
{
    public function __construct()
    {
        parent::__construct(new Goal::class);
    }

    public function getAll(string $orderBy = 'goals')
    {
        return $this->queryBaseStats([], $orderBy)->get();
    }

    public function getByChampionship(int $championshipId, string $orderBy = 'goals')
    {
        return $this->queryBaseStats([['goal.fixture_id', '=', $championshipId]], $orderBy)->get();
    }

    public function getByTeam(int $teamId, string $orderBy = 'goals')
    {
        $goalsQuery = $this->buildGoalsQuery()
            ->join('team_player', 'scorer_id', '=', 'team_player.player_id')
            ->where('team_player.team_id', $teamId);

        $assistsQuery = $this->buildAssistsQuery()
            ->join('team_player', 'assist_id', '=', 'team_player.player_id')
            ->where('team_player.team_id', $teamId);

        return $this->combineStatsAndRatings($goalsQuery, $assistsQuery, $orderBy);
    }

    public function getByCurrentTeam(int $teamId, string $orderBy = 'goals')
    {
        $goalsQuery = $this->buildGoalsQuery()
            ->join('team_player', 'scorer_id', '=', 'team_player.player_id')
            ->where('team_player.team_id', $teamId)
            ->where('team_player.current_team', true);

        $assistsQuery = $this->buildAssistsQuery()
            ->join('team_player', 'assist_id', '=', 'team_player.player_id')
            ->where('team_player.team_id', $teamId)
            ->where('team_player.current_team', true);

        return $this->combineStatsAndRatings($goalsQuery, $assistsQuery, $orderBy);
    }

    public function getByPreviousTeam(int $teamId, string $orderBy = 'goals')
    {
        $goalsQuery = $this->buildGoalsQuery()
            ->join('team_player', 'scorer_id', '=', 'team_player.player_id')
            ->where('team_player.team_id', $teamId)
            ->where('team_player.current_team', false);

        $assistsQuery = $this->buildAssistsQuery()
            ->join('team_player', 'assist_id', '=', 'team_player.player_id')
            ->where('team_player.team_id', $teamId)
            ->where('team_player.current_team', false);

        return $this->combineStatsAndRatings($goalsQuery, $assistsQuery, $orderBy);
    }

    private function buildGoalsQuery()
    {
        return Goal::query()->selectRaw('
            scorer_id AS player_id,
            COUNT(*) AS goals,
            SUM(CASE WHEN pk = true THEN 1 ELSE 0 END) AS pk_goals
        ')
            ->groupBy('scorer_id');
    }

    private function buildAssistsQuery()
    {
        return Goal::query()->selectRaw('
            assist_id AS player_id,
            COUNT(*) AS assists,
            0 AS pk_goals
        ')
            ->whereNotNull('assist_id')
            ->groupBy('assist_id');
    }

    private function combineStatsAndRatings($goalsQuery, $assistsQuery, string $orderBy)
    {
        $statsSubQuery = $goalsQuery
            ->unionAll($assistsQuery)
            ->selectRaw('
                player_id,
                SUM(goals) AS goals,
                SUM(assists) AS assists,
                SUM(pk_goals) AS pk_goals
            ')
            ->groupBy('player_id');

        return DB::table(DB::raw("({$statsSubQuery->toSql()}) as stats"))
            ->mergeBindings($statsSubQuery->getQuery())
            ->leftJoin('player_rates', 'stats.player_id', '=', 'player_rates.player_id')
            ->selectRaw('
                stats.player_id,
                stats.goals,
                stats.assists,
                stats.pk_goals,
                AVG(player_rates.rate) AS average_rate
            ')
            ->groupBy('stats.player_id', 'stats.goals', 'stats.assists', 'stats.pk_goals')
            ->orderBy($orderBy, 'desc')
            ->get();
    }
}
