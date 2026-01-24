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
use Illuminate\Support\Facades\DB;
use App\Modules\Championship\PlayoffGeneratorService;
use App\Modules\Championship\ChampionshipAnalyticService;

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
                'decided_by_penalty' => $data['decided_by_penalty'] ?? false,
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

        $champ = Championship::find($fixture->championship_id);

        // Verifica se deve gerar automaticamente a rodada final
        $finalRoundGenerated = $this->checkAndGenerateFinalRound($champ);

        // Verifica se é um jogo de playoff e se precisa gerar terceiro jogo
        if ($fixture->is_playoff) {
            $playoffService = new PlayoffGeneratorService(new ChampionshipAnalyticService());
            $playoffService->checkAndCreateDecisiveGame($fixture->id);
        }

        $has_fixtures = Fixture::query()
            ->where('championship_id', $fixture->championship_id)
            ->where('is_played', false)
            ->get();

        // Se todos os jogos da fase de grupos terminaram e tem playoffs, gerar playoffs
        if (!$has_fixtures->count() && $champ->playoffs && $champ->playoff_type) {
            $regularSeasonEnded = !Fixture::where('championship_id', $champ->id)
                ->where('is_playoff', false)
                ->where('is_played', false)
                ->exists();
            
            $playoffNotStarted = !Fixture::where('championship_id', $champ->id)
                ->where('is_playoff', true)
                ->exists();

            if ($regularSeasonEnded && $playoffNotStarted) {
                $playoffService = new PlayoffGeneratorService(new ChampionshipAnalyticService());
                $playoffService->generatePlayoffs($champ->id);
            }
        }
        
        if (!$has_fixtures->count() && !$champ->playoffs) {
            $champ->update(['finished_at' => now()]);

            $has_awards = $champ->whereHas('awards', function ($query) use ($fixture) {
                $query->where('championship_id', $fixture->championship_id);
            });

            if (!$has_awards->count()) {
                $this->saveAwards($fixture);
            }
        }

        return [
            'success' => true,
            'final_round_generated' => $finalRoundGenerated
        ];
    }

    /**
     * Verifica e gera automaticamente a rodada final se necessário
     * Retorna true se a rodada final foi gerada
     */
    private function checkAndGenerateFinalRound(Championship $championship): bool
    {
        // Verifica se o campeonato tem número ímpar de rodadas
        if ($championship->rounds % 2 === 0 || $championship->rounds === 1) {
            return false;
        }

        $finalRound = $championship->rounds;

        // Verifica se a rodada final já foi gerada
        $finalRoundExists = Fixture::where('championship_id', $championship->id)
            ->where('round_number', $finalRound)
            ->exists();

        if ($finalRoundExists) {
            return false;
        }

        // Verifica se todas as rodadas anteriores foram jogadas
        $unplayedPreviousMatches = Fixture::where('championship_id', $championship->id)
            ->where('round_number', '<', $finalRound)
            ->where('is_played', false)
            ->count();

        if ($unplayedPreviousMatches === 0) {
            // Todas as rodadas anteriores foram jogadas - gera a rodada final
            try {
                \App\Modules\Championship\FinalRoundGeneratorService::generateFinalRound($championship->id);
                return true;
            } catch (\Exception $e) {
                // Log do erro mas não interrompe o fluxo
                \Log::warning("Erro ao gerar rodada final automaticamente: " . $e->getMessage());
                return false;
            }
        }

        return false;
    }

    private function saveAwards(Fixture $fixture)
    {
        $championshipId = $fixture->championship_id;

        // Melhor jogador (Best Player): jogador com a melhor média de notas
        $bestPlayer = DB::table('player_rates')
            ->join('fixtures', 'player_rates.fixture_id', '=', 'fixtures.id')
            ->where('fixtures.championship_id', $championshipId)
            ->select('player_rates.player_id', DB::raw('AVG(player_rates.rate) as average_rate'))
            ->groupBy('player_rates.player_id')
            ->orderByDesc('average_rate')
            ->first();

        // Artilheiro (Golden Boot): jogador com mais gols
        $goldenBoot = DB::table('goals')
            ->join('fixtures', 'goals.fixture_id', '=', 'fixtures.id')
            ->where('fixtures.championship_id', $championshipId)
            ->whereNotNull('goals.scorer_id')
            ->select('goals.scorer_id', DB::raw('COUNT(goals.scorer_id) as goal_count'))
            ->groupBy('goals.scorer_id')
            ->orderByDesc('goal_count')
            ->first();

        // Melhor assistente (Playmaker): jogador com mais assistências
        $playmaker = DB::table('goals')
            ->join('fixtures', 'goals.fixture_id', '=', 'fixtures.id')
            ->where('fixtures.championship_id', $championshipId)
            ->whereNotNull('goals.assist_id')
            ->select('goals.assist_id', DB::raw('COUNT(goals.assist_id) as assist_count'))
            ->groupBy('goals.assist_id')
            ->orderByDesc('assist_count')
            ->first();

        // Verificar se todos os valores foram encontrados antes de salvar no banco de dados
        if ($bestPlayer && $goldenBoot && $playmaker) {
            DB::table('awards')->insert([
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
