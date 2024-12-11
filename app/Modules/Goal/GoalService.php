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
        return $this->getStatistics(null, null, null, $orderBy);
    }

    public function getByChampionship(int $championshipId, string $orderBy = 'goals')
    {
        return $this->getStatistics('goal.fixture_id', $championshipId, null, $orderBy);
    }

    public function getByTeam(int $teamId, string $orderBy = 'goals')
    {
        return $this->getStatistics('team_player.team_id', $teamId, null, $orderBy);
    }

    public function getByCurrentTeam(int $teamId, string $orderBy = 'goals')
    {
        return $this->getStatistics('team_player.team_id', $teamId, true, $orderBy);
    }

    public function getByPreviousTeam(int $teamId, string $orderBy = 'goals')
    {
        return $this->getStatistics('team_player.team_id', $teamId, false, $orderBy);
    }

    private function getStatistics(?string $filterColumn, ?int $filterValue, ?bool $isCurrentTeam, string $orderBy): \Illuminate\Support\Collection
    {
        // Consulta para obter todos os gols (incluindo gols de pênalti)
        $goalsQuery = Goal::query()
            ->selectRaw('
                scorer_id AS player_id,
                COUNT(*) AS goals,
                SUM(CASE WHEN pk = true THEN 1 ELSE 0 END) AS pk_goals
            ')
            ->groupBy('scorer_id');

        // Consulta para obter assistências
        $assistsQuery = Goal::query()
            ->selectRaw('
                assist_id AS player_id,
                COUNT(*) AS assists,
                0 AS pk_goals
            ')
            ->whereNotNull('assist_id')
            ->groupBy('assist_id');

        // Adicionando condição de filtro para campeonatos, equipes ou times específicos (opcional)
        if ($filterColumn && $filterValue) {
            if ($filterColumn === 'goal.fixture_id') {
                $goalsQuery->where('goal.fixture_id', $filterValue);
                $assistsQuery->where('goal.fixture_id', $filterValue);
            } elseif ($filterColumn === 'team_player.team_id') {
                // Relaciona equipe via `team_player`
                $goalsQuery->join('team_player', 'scorer_id', '=', 'team_player.player_id')
                    ->where('team_player.team_id', $filterValue);

                $assistsQuery->join('team_player', 'assist_id', '=', 'team_player.player_id')
                    ->where('team_player.team_id', $filterValue);

                // Adiciona lógica para equipes atuais ou anteriores
                if (!is_null($isCurrentTeam)) {
                    $goalsQuery->where('team_player.current_team', $isCurrentTeam);
                    $assistsQuery->where('team_player.current_team', $isCurrentTeam);
                }
            }
        }

        // Combina os resultados de gols e assistências
        $statsSubQuery = $goalsQuery
            ->unionAll($assistsQuery)
            ->selectRaw('
                player_id,
                SUM(goals) AS goals,
                SUM(assists) AS assists,
                SUM(pk_goals) AS pk_goals
            ')
            ->groupBy('player_id');

        // Adiciona a média das ratings dos jogadores
        $stats = DB::table(DB::raw("({$statsSubQuery->toSql()}) as stats"))
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

        return $stats;
    }
}
