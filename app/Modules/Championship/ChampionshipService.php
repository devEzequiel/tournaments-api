<?php

namespace App\Modules\Championship;

use App\Contracts\ChampionshipContract;
use App\Models\Championship;
use App\Models\Fixture;
use App\Services\BaseService;
use Exception;

class ChampionshipService extends BaseService implements ChampionshipContract
{

    protected array $with = ['teams', 'fixtures'];

    public function __construct()
    {
        parent::__construct(new Championship());
    }

    /**
     * @throws Exception
     */
    public function create($data): bool
    {
        $champ = $this->model::create($data);
        self::createFixtures($data['teams'], $champ);
        return true;
    }

    /**
     * @throws Exception
     */
    public function find(int $championship_id)
    {
        $championship = $this->model::query()
            ->where('id', $championship_id)
            ->get();

        if (!$championship) {
            throw new Exception('Campeonato não encontrado');
        }

        return $championship;
    }

    /**
     * @throws Exception
     */
    public function all()
    {
        $championship = $this->model::query()
            ->get();

        if (!$championship) throw new Exception('Nenhum campeonato encontrado');

        return $championship;
    }

    /**
     * @throws Exception
     */
    public function update($data, $championship_id): bool
    {
        $championship = $this->model::find((int)$championship_id);

        if (!$championship) throw new Exception('Campeonato não encontrado');

        return (bool)$championship->update($data);
    }

    /**
     * @throws Exception
     */
    public function delete(int $championship_id): bool
    {
        $championship = $this->model::find($championship_id);

        if (!$championship) throw new Exception('Campeonato não encontrado');

        return (bool)$championship->delete();
    }

    /**
     * @throws Exception
     */
    public function getFixtures(int $championship_id)
    {
        $fixtures = Fixture::query()
            ->where('championship_id', $championship_id)
            ->orderBy('round_number', 'ASC')
            ->orderBy('game_number', 'ASC')
            ->with('awayTeam', 'homeTeam')
            ->get()->toArray();

        if (!$fixtures) throw new Exception('Nenhum confronto encontrado');

        return $fixtures;
    }

    private static function createFixtures(array $teams, Championship $champ)
    {
        $scheduler = new RoundRobinScheduler();
        $scheduler->setTeams($teams) // Define os times
                        ->shuffle() // Embaralha os times
                        ->setRounds($champ->rounds); // Define 3 turnos completos

        $schedule = $scheduler->build();

        $gameNumber = 1; // Inicializa o número do jogo

        foreach ($schedule as $round => $matches) {
            $data = [];
            $data['championship_id'] = $champ->id;
            $data['round_number'] = $round;

            foreach ($matches as $match) {
                $data['game_number'] = $gameNumber; // Número do jogo
                $data['home_team_id'] = $match[0]; // Time "da casa"
                $data['away_team_id'] = $match[1]; // Time "visitante"
                Fixture::create($data); // Salva no banco de dados

                $gameNumber++; // Incrementa o número do jogo
            }
        }

    }

    public function getStanding(int $championship_id)
    {
        // Query para calcular a classificação dos times
        $standings = Fixture::query()
            ->selectRaw('
            home_team_id AS team_id,
            SUM(CASE WHEN home_score > away_score THEN 3 WHEN home_score = away_score THEN 1 ELSE 0 END) AS points,
            SUM(CASE WHEN home_score > away_score THEN 1 ELSE 0 END) AS wins,
            SUM(CASE WHEN home_score = away_score THEN 1 ELSE 0 END) AS draws,
            SUM(CASE WHEN home_score < away_score THEN 1 ELSE 0 END) AS losses,
            SUM(home_score - away_score) AS goal_diff,
            SUM(home_score) AS goals_scored,
            SUM(away_score) AS goals_conceded,
            0 AS goals_away,
            COUNT(*) AS played
        ')
            ->where('championship_id', $championship_id)
            ->groupBy('home_team_id')
            ->unionAll(
                Fixture::query()
                    ->selectRaw('
                    away_team_id AS team_id,
                    SUM(CASE WHEN away_score > home_score THEN 3 WHEN away_score = home_score THEN 1 ELSE 0 END) AS points,
                    SUM(CASE WHEN away_score > home_score THEN 1 ELSE 0 END) AS wins,
                    SUM(CASE WHEN away_score = home_score THEN 1 ELSE 0 END) AS draws,
                    SUM(CASE WHEN away_score < home_score THEN 1 ELSE 0 END) AS losses,
                    SUM(away_score - home_score) AS goal_diff,
                    SUM(away_score) AS goals_scored,
                    SUM(home_score) AS goals_conceded,
                    SUM(away_score) AS goals_away,
                    COUNT(*) AS played
                ')
                    ->where('championship_id', $championship_id)
                    ->groupBy('away_team_id')
            )
            ->groupBy('team_id')
            ->orderByDesc('points')
            ->orderByDesc('wins')
            ->orderByDesc('goal_diff')
            ->orderByDesc('goals_away')
            ->get();

        // Query para calcular os jogos restantes
        $remainingGames = Fixture::query()
            ->selectRaw('
            home_team_id AS team_id,
            COUNT(*) AS remaining
        ')
            ->where('championship_id', $championship_id)
            ->where('is_played', false)
            ->groupBy('home_team_id')
            ->unionAll(
                Fixture::query()
                    ->selectRaw('
                    away_team_id AS team_id,
                    COUNT(*) AS remaining
                ')
                    ->where('championship_id', $championship_id)
                    ->where('is_played', false)
                    ->groupBy('away_team_id')
            )
            ->groupBy('team_id')
            ->get();

        foreach ($standings as $index => &$team) {
            $teamRemaining = $remainingGames->firstWhere('team_id', $team->team_id);
            $team->remaining = $teamRemaining ? $teamRemaining->remaining : 0;
            $team->rank = $index + 1;
        }

        return $standings;
    }
}
