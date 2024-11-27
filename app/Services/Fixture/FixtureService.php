<?php

namespace App\Services\Fixture;

use App\Models\Championship;
use App\Models\Fixture;
use App\Models\Goal;
use App\Models\PlayerRate;
use App\Services\BaseService;

class FixtureService extends BaseService
{
    private Championship $championship;

    public function __construct(Championship $championship)
    {
        parent::__construct(new Fixture());
        $this->championship = $championship;

    }

    public function getAllFixtures(int $championship_id)
    {
        return $this->model::query()
            ->with('homeTeam', 'awayTeam')
            ->where('championship_id', $championship_id)
            ->get();
    }

    public function playMatch(array $data)
    {
        $fixture = $this->model::find($data['fixture_id']);
        $fixture->update(
            [
                'home_goals' => $data['home_goals'],
                'away_goals' => $data['away_goals']
            ]
        );

        foreach ($data['goals'] as $goal) {
            Goal::create([
                'fixture_id' => $goal['fixture_id'],
                'scorer_id' => $goal['scorer_id'],
                'assist_id' => $goal['assist_id'],
                'pk' => $goal['pk'],
                'own_goal' => $goal['own_goal'],
            ]);
        }

        foreach ($data['rates'] as $rate) {
            PlayerRate::create([
                'fixture_id' => $rate['fixture_id'],
                'player_id' => $rate['player_id'],
                'rate' => $rate['rate']
            ]);
        }

        return true;
    }

    public function processPlayoffs()
    {
        if (!$this->championship->playoffs) {
            return; // Apenas processa se playoffs estiverem habilitados
        }

        // Obtém os 4 primeiros colocados com base nos resultados
        $classifiedTeams = $this->getClassifiedTeams($championship, 4);

        if (count($classifiedTeams) < 4) {
            throw new \Exception('Não há times suficientes para os playoffs.');
        }

        // Cria as semifinais (playoff_round = 2)
        $this->createPlayoffFixtures($championship, $classifiedTeams);
    }

    public function getAllFixtures(int $championship_id)
    {
    }

    private
    function getTopTeams(Championship $championship, $limit)
    {
        // Obtenha os times classificados com base em critérios como pontuação, saldo de gols etc.
        return Team::select('teams.*')
            ->join('group_standings', 'teams.id', '=', 'group_standings.team_id')
            ->where('group_standings.championship_id', $championship->id)
            ->orderBy('group_standings.points', 'desc') // Ordenação por pontuação (ajuste conforme a regra)
            ->limit($limit)
            ->get();
    }

    private
    function createPlayoffFixtures(Championship $championship, $classifiedTeams)
    {
        // Semifinais
        Fixture::create([
            'championship_id' => $championship->id,
            'home_team_id' => $classifiedTeams[0]->id,
            'away_team_id' => $classifiedTeams[3]->id,
            'playoff_round' => 2, // Semifinal
        ]);

        Fixture::create([
            'championship_id' => $championship->id,
            'home_team_id' => $classifiedTeams[1]->id,
            'away_team_id' => $classifiedTeams[2]->id,
            'playoff_round' => 2, // Semifinal
        ]);
    }
}
}
