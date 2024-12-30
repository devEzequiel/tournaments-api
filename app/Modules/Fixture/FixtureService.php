<?php

namespace App\Modules\Fixture;

use App\Contracts\FixtureContract;
use App\Models\Award;
use App\Models\Championship;
use App\Models\Fixture;
use App\Models\Goal;
use App\Models\PlayerRate;
use App\Models\Team;
use App\Services\BaseService;
use App\Services\Fixture\Exception;

class FixtureService extends BaseService implements FixtureContract
{
    private Championship $championship;

    public function __construct(Championship $championship)
    {
        parent::__construct(new Fixture());
        $this->championship = $championship;
    }

    /**
     * @throws Exception
     */
    public function findFixture(int $fixture_id)
    {
        $fixtures = Fixture::query()
            ->where('id', $fixture_id)
            ->with('awayTeam', 'homeTeam')
            ->get();

        if (!$fixtures) {
            throw new Exception('Confronto não encontrado');
        }

        return $fixtures;
    }

    public function getAllFixtures(int $championship_id): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model::query()
            ->with('homeTeam', 'awayTeam')
            ->where('championship_id', $championship_id)
            ->get();
    }

    public function getFixturesWithBasicInfo(int $championship_id): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model::query()
            ->join('teams as home_team', 'fixtures.home_team_id', '=', 'home_team.id')
            ->join('teams as away_team', 'fixtures.away_team_id', '=', 'away_team.id')
            ->select([
                'fixtures.id',
                'home_team.name as home_team_name',
                'away_team.name as away_team_name',
                'home_team.first_color as home_team_first_color',
                'home_team.second_color as home_team_second_color',
                'away_team.first_color as away_team_first_color',
                'away_team.second_color as away_team_second_color',
                'fixtures.round_number',
                'fixtures.game_number',
                'fixtures.home_goals',
                'fixtures.away_goals',
                'fixtures.played_at',
            ])
            ->orderBy('fixtures.round_number', 'ASC')
            ->orderBy('fixtures.game_number', 'ASC')
            ->where('fixtures.championship_id', $championship_id)
            ->get();
    }

    public
    function getUnplayedFixtures(int $championship_id): \Illuminate\Database\Eloquent\Collection|array
    {
        return $this->model::query()
            ->join('teams as home_team', 'fixtures.home_team_id', '=', 'home_team.id')
            ->join('teams as away_team', 'fixtures.away_team_id', '=', 'away_team.id')
            ->select([
                'fixtures.id',
                'home_team.name as home_team_name',
                'home_team.id as home_team_id',
                'away_team.name as away_team_name',
                'away_team.id as away_team_id',
                'home_team.first_color as home_team_color',
                'home_team.second_color as home_team_second_color',
                'away_team.first_color as away_team_color',
                'away_team.second_color as away_team_second_color',
                'fixtures.round_number',
                'fixtures.game_number',
            ])
            ->orderBy('fixtures.round_number', 'ASC')
            ->orderBy('fixtures.game_number', 'ASC')
            ->where('fixtures.championship_id', $championship_id)
            ->where('fixtures.is_played', false)
            ->get();
    }

    public function playMatch(array $data)
    {
        $fixture = $this->model::find($data['fixture_id']);
        $fixture->update(
            [
                'home_goals' => $data['home_goals'],
                'away_goals' => $data['away_goals'],
                'is_played' => true,
                'played_at' => now(),
            ]
        );

        if (!is_null($fixture->playoff_round) && $fixture->playoff_round > 1) {
            return $this->createNextPlayoffRound(
                $fixture->championship, $fixture->championship->teams, $fixture->playoff_round
            );
        }

        if (isset($data['goals']) && count($data['goals']) > 0) {
            //apaga todos antes de adicionar
            Goal::query()->where('fixture_id', $fixture->id)->delete();

            foreach ($data['goals'] as $goal) {
                Goal::query()
                    ->create([
                        'fixture_id' => $fixture->id,
                        'scorer_id' => $goal['scorer_id'] ?? null,
                        'assist_id' => $goal['assist_id'] ?? null
                    ]);
            }
        }

        if (isset($data['rates']) && count($data['rates']) > 0) {
            //Apaga todas as notas dos jogadores antes de adicionar, pra evitar duplicatas
            PlayerRate::query()->where('fixture_id', $fixture->id)->delete();

            foreach ($data['rates'] as $rate) {
                PlayerRate::query()
                    ->create([
                        'fixture_id' => $fixture->id,
                        'player_id' => $rate['player_id'],
                        'rate' => $rate['rate']
                    ]);
            }
        }

        $champ = $this->championship::find($fixture->championship_id);

        $has_fixtures = $champ->whereHas('fixtures', function ($query) use ($fixture) {
            $query->where('is_played', false);
        });

        if (!$has_fixtures->count() && $champ->playoffs) {
            $this->processPlayoffs($champ);
        }

        if (!$has_fixtures->count() && !$champ->playoffs) {
            $has_awards = $champ->whereHas('awards', function ($query) use ($fixture) {
                $query->where('championship_id', $fixture->championship_id);
            });

            if (!$has_awards->count()) {
                $this->saveAwards($fixture);
            }
        }

        return true;
    }

    private function saveAwards(Fixture $fixture)
    {
        $championshipId = $fixture->championship_id;

        // Melhores notas - Melhor jogador (Best Player)
        $bestPlayer = PlayerRate::query()
            ->select('player_id')
            ->whereHas('awards', function ($query) use ($championshipId) {
                $query->where('championship_id', $championshipId);
            })
            ->groupBy('player_id')
            ->selectRaw('AVG(rate) as average_rate, player_id')
            ->orderByDesc('average_rate')
            ->first();

        // Artilheiro (Golden Boot)
        $goldenBoot = Goal::query()
            ->select('scorer_id')
            ->whereHas('fixture', function ($query) use ($championshipId) {
                $query->where('championship_id', $championshipId);
            })
            ->whereNotNull('scorer_id')
            ->groupBy('scorer_id')
            ->selectRaw('COUNT(scorer_id) as goal_count, scorer_id')
            ->orderByDesc('goal_count')
            ->first();

        // Melhor assistente (Playmaker)
        $playmaker = Goal::query()
            ->select('assist_id')
            ->whereHas('fixture', function ($query) use ($championshipId) {
                $query->where('championship_id', $championshipId);
            })
            ->whereNotNull('assist_id')
            ->groupBy('assist_id')
            ->selectRaw('COUNT(assist_id) as assist_count, assist_id')
            ->orderByDesc('assist_count')
            ->first();
dd($bestPlayer, $goldenBoot, $playmaker);
        // Verificar se todos os valores foram encontrados antes de salvar
        if ($bestPlayer && $goldenBoot && $playmaker) {
            Award::query()->create([
                'championship_id' => $championshipId,
                'best_player' => $bestPlayer->player_id,
                'golden_boot' => $goldenBoot->scorer_id,
                'playmaker' => $playmaker->assist_id,
            ]);
        }
    }

    private function processPlayoffs($championship): void
    {
        $classifiedTeams = $this->getTopTeams($championship, $this->determinePlayoffTeamsCount($championship->rounds));

        $this->createPlayoffFixtures($championship, $classifiedTeams);
    }

    private
    function getTopTeams(Championship $championship, $limit)
    {
        return Team::select('teams.*')
            ->join('fixtures as home_fixtures', function ($join) use ($championship) {
                $join->on('teams.id', '=', 'home_fixtures.home_team_id')
                    ->where('home_fixtures.championship_id', $championship->id)
                    ->where('home_fixtures.is_played', true);
            })
            ->leftJoin('fixtures as away_fixtures', function ($join) use ($championship) {
                $join->on('teams.id', '=', 'away_fixtures.away_team_id')
                    ->where('away_fixtures.championship_id', $championship->id)
                    ->where('away_fixtures.is_played', true);
            })
            ->selectRaw('
        teams.*,
        COALESCE(SUM(
            CASE
                WHEN home_fixtures.home_goals > home_fixtures.away_goals THEN 3
                WHEN home_fixtures.home_goals = home_fixtures.away_goals THEN 1
                ELSE 0
            END
        ) + SUM(
            CASE
                WHEN away_fixtures.away_goals > away_fixtures.home_goals THEN 3
                WHEN away_fixtures.away_goals = away_fixtures.home_goals THEN 1
                ELSE 0
            END
        ), 0) AS points,
        COALESCE(SUM(home_fixtures.home_goals - home_fixtures.away_goals), 0) + COALESCE(SUM(away_fixtures.away_goals - away_fixtures.home_goals), 0) AS goal_difference,
        COALESCE(SUM(away_fixtures . away_goals), 0) as away_goals_scored
    ')
            ->groupBy('teams.id')
            ->orderByDesc('points')
            ->orderByDesc('goal_difference')
            ->orderByDesc('away_goals_scored')
            ->limit($limit)
            ->get();
    }

    private
    function createPlayoffFixtures(Championship $championship, $classifiedTeams): void
    {

        $firstIndex = 0;
        $lastIndex = count($classifiedTeams) - 1;

        while ($firstIndex < $lastIndex) {
            $homeTeam = $classifiedTeams[$firstIndex];
            $awayTeam = $classifiedTeams[$lastIndex];

            // Create a new fixture for the playoffs with homeTeam and awayTeam
            $this->model::create([
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'championship_id' => $championship->id,
                'is_played' => false,
                'playoff_round' => 1,
            ]);

            $firstIndex++;
            $lastIndex--;
        }

        return;
    }


    private function createNextPlayoffRound(Championship $championship, $classifiedTeams, int $currentRound)
    {
        $nextRound = $currentRound + 1;
        $firstIndex = 0;
        $lastIndex = count($classifiedTeams) - 1;

        while ($firstIndex < $lastIndex) {
            $homeTeam = $classifiedTeams[$firstIndex];
            $awayTeam = $classifiedTeams[$lastIndex];

            // Create a new fixture for the next round of playoffs with homeTeam and awayTeam
            $this->model::create([
                'home_team_id' => $homeTeam->id,
                'away_team_id' => $awayTeam->id,
                'championship_id' => $championship->id,
                'is_played' => false,
                'playoff_round' => $nextRound,
            ]);

            $firstIndex++;
            $lastIndex--;
        }

        return true;
    }

    private function determinePlayoffTeamsCount(int $playoff_rounds): int
    {
        switch ($playoff_rounds) {
            case 1:
                return 2; // Final
            case 2:
                return 4; // Semifinals
            case 3:
                return 8; // Quarterfinals
            default:
                throw new \InvalidArgumentException('Invalid number of playoff rounds.');
        }
    }
}
