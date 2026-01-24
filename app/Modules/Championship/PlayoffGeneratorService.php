<?php

namespace App\Modules\Championship;

use App\Models\Championship;
use App\Models\Fixture;
use App\Modules\Championship\ChampionshipAnalyticService;

class PlayoffGeneratorService
{
    private ChampionshipAnalyticService $analyticService;

    public function __construct(ChampionshipAnalyticService $analyticService)
    {
        $this->analyticService = $analyticService;
    }

    /**
     * Gera os jogos de playoff baseado no tipo configurado
     */
    public function generatePlayoffs(int $championshipId): void
    {
        $championship = Championship::findOrFail($championshipId);

        if (!$championship->playoffs || !$championship->playoff_type) {
            return;
        }

        $standings = $this->analyticService->getStandings($championshipId);

        if ($championship->playoff_type === 'final') {
            $this->generateFinalPlayoff($championship, $standings);
        } elseif ($championship->playoff_type === 'semifinal') {
            $this->generateSemifinalPlayoff($championship, $standings);
        }
    }

    /**
     * Gera playoff de final direta (1º vs 2º)
     */
    private function generateFinalPlayoff(Championship $championship, $standings): void
    {
        if ($standings->count() < 2) {
            throw new \Exception('Número insuficiente de times para gerar playoff de final');
        }

        $first = $standings[0];
        $second = $standings[1];

        $this->createPlayoffFixture($championship, $second->team_id, $first->team_id, 'final', 1);

        $this->createPlayoffFixture($championship, $first->team_id, $second->team_id, 'final', 2);
    }

    /**
     * Gera playoff de semifinal (1º vs 4º, 2º vs 3º) + final
     */
    private function generateSemifinalPlayoff(Championship $championship, $standings): void
    {
        if ($standings->count() < 4) {
            throw new \Exception('Número insuficiente de times para gerar playoff de semifinal');
        }

        $first = $standings[0];
        $second = $standings[1];
        $third = $standings[2];
        $fourth = $standings[3];

        $this->createPlayoffFixture($championship, $fourth->team_id, $first->team_id, 'semifinal', 1, 1);
        
        $this->createPlayoffFixture($championship, $first->team_id, $fourth->team_id, 'semifinal', 2, 1);

        $this->createPlayoffFixture($championship, $third->team_id, $second->team_id, 'semifinal', 1, 2);
        
        $this->createPlayoffFixture($championship, $second->team_id, $third->team_id, 'semifinal', 2, 2);
    }

    /**
     * Cria uma fixture de playoff
     */
    private function createPlayoffFixture(
        Championship $championship,
        int $homeTeamId,
        int $awayTeamId,
        string $stage,
        int $gameNumber,
        ?int $seriesNumber = null
    ): void {
        $lastRound = Fixture::where('championship_id', $championship->id)
            ->max('round_number') ?? 0;

        $roundNumber = $lastRound + 1;

        Fixture::create([
            'championship_id' => $championship->id,
            'home_team_id' => $homeTeamId,
            'away_team_id' => $awayTeamId,
            'round_number' => $roundNumber,
            'game_number' => $gameNumber + ($seriesNumber ? ($seriesNumber - 1) * 10 : 0),
            'is_played' => false,
            'is_playoff' => true,
            'playoff_stage' => $stage,
            'playoff_game_number' => $gameNumber,
            'home_goals' => 0,
            'away_goals' => 0,
        ]);
    }

    /**
     * Verifica se é necessário criar terceiro jogo de uma série de playoff
     * Chamado após cada jogo de playoff ser registrado
     */
    public function checkAndCreateDecisiveGame(int $fixtureId): void
    {
        $fixture = Fixture::findOrFail($fixtureId);

        if (!$fixture->is_playoff || !$fixture->is_played) {
            return;
        }

        $championship = $fixture->championship;
        $stage = $fixture->playoff_stage;

        $seriesGames = Fixture::where('championship_id', $championship->id)
            ->where('is_playoff', true)
            ->where('playoff_stage', $stage)
            ->where(function ($query) use ($fixture) {
                $query->where(function ($q) use ($fixture) {
                    $q->where('home_team_id', $fixture->home_team_id)
                      ->where('away_team_id', $fixture->away_team_id);
                })->orWhere(function ($q) use ($fixture) {
                    $q->where('home_team_id', $fixture->away_team_id)
                      ->where('away_team_id', $fixture->home_team_id);
                });
            })
            ->where('is_played', true)
            ->get();

        $team1Id = min($fixture->home_team_id, $fixture->away_team_id);
        $team2Id = max($fixture->home_team_id, $fixture->away_team_id);

        $team1Wins = 0;
        $team2Wins = 0;

        foreach ($seriesGames as $game) {
            if ($game->home_goals > $game->away_goals) {
                if ($game->home_team_id == $team1Id) {
                    $team1Wins++;
                } else {
                    $team2Wins++;
                }
            } elseif ($game->home_goals < $game->away_goals) {
                if ($game->away_team_id == $team1Id) {
                    $team1Wins++;
                } else {
                    $team2Wins++;
                }
            }
        }

        if ($team1Wins === 1 && $team2Wins === 1) {
            $alreadyHasGame3 = Fixture::where('championship_id', $championship->id)
                ->where('is_playoff', true)
                ->where('playoff_stage', $stage)
                ->where('playoff_game_number', 3)
                ->where(function ($query) use ($team1Id, $team2Id) {
                    $query->where(function ($q) use ($team1Id, $team2Id) {
                        $q->where('home_team_id', $team1Id)
                          ->where('away_team_id', $team2Id);
                    })->orWhere(function ($q) use ($team1Id, $team2Id) {
                        $q->where('home_team_id', $team2Id)
                          ->where('away_team_id', $team1Id);
                    });
                })
                ->exists();

            if (!$alreadyHasGame3) {
                $standings = $this->analyticService->getStandings($championship->id);
                $team1Rank = $standings->search(fn($item) => $item->team_id === $team1Id);
                $team2Rank = $standings->search(fn($item) => $item->team_id === $team2Id);

                $homeTeamId = $team1Rank < $team2Rank ? $team1Id : $team2Id;
                $awayTeamId = $homeTeamId === $team1Id ? $team2Id : $team1Id;

                $this->createPlayoffFixture($championship, $homeTeamId, $awayTeamId, $stage, 3);
            }
        }

        if ($stage === 'semifinal' && ($team1Wins === 2 || $team2Wins === 2)) {
            $this->checkAndCreateFinalAfterSemifinals($championship);
        }
    }

    /**
     * Verifica se ambas semifinais terminaram e cria a final
     */
    private function checkAndCreateFinalAfterSemifinals(Championship $championship): void
    {
        $semifinalGames = Fixture::where('championship_id', $championship->id)
            ->where('is_playoff', true)
            ->where('playoff_stage', 'semifinal')
            ->where('is_played', true)
            ->get();

        $series = [];
        foreach ($semifinalGames as $game) {
            $key = min($game->home_team_id, $game->away_team_id) . '-' . max($game->home_team_id, $game->away_team_id);
            if (!isset($series[$key])) {
                $series[$key] = [
                    'team1' => min($game->home_team_id, $game->away_team_id),
                    'team2' => max($game->home_team_id, $game->away_team_id),
                    'team1_wins' => 0,
                    'team2_wins' => 0,
                ];
            }

            if ($game->home_goals > $game->away_goals) {
                if ($game->home_team_id == $series[$key]['team1']) {
                    $series[$key]['team1_wins']++;
                } else {
                    $series[$key]['team2_wins']++;
                }
            } elseif ($game->home_goals < $game->away_goals) {
                if ($game->away_team_id == $series[$key]['team1']) {
                    $series[$key]['team1_wins']++;
                } else {
                    $series[$key]['team2_wins']++;
                }
            }
        }

        $winners = [];
        foreach ($series as $serie) {
            if ($serie['team1_wins'] >= 2) {
                $winners[] = $serie['team1'];
            } elseif ($serie['team2_wins'] >= 2) {
                $winners[] = $serie['team2'];
            }
        }

        if (count($winners) === 2) {
            $finalExists = Fixture::where('championship_id', $championship->id)
                ->where('is_playoff', true)
                ->where('playoff_stage', 'final')
                ->exists();

            if (!$finalExists) {
                $standings = $this->analyticService->getStandings($championship->id);
                $team1Rank = $standings->search(fn($item) => $item->team_id === $winners[0]);
                $team2Rank = $standings->search(fn($item) => $item->team_id === $winners[1]);

                $betterTeam = $team1Rank < $team2Rank ? $winners[0] : $winners[1];
                $worseTeam = $betterTeam === $winners[0] ? $winners[1] : $winners[0];

                $this->createPlayoffFixture($championship, $worseTeam, $betterTeam, 'final', 1);

                $this->createPlayoffFixture($championship, $betterTeam, $worseTeam, 'final', 2);
            }
        }
    }

    /**
     * Retorna o campeão do campeonato (considerando playoffs se houver)
     */
    public function getChampion(int $championshipId): ?int
    {
        $championship = Championship::findOrFail($championshipId);

        if ($championship->playoffs) {
            $finalGames = Fixture::where('championship_id', $championshipId)
                ->where('is_playoff', true)
                ->where('playoff_stage', 'final')
                ->where('is_played', true)
                ->get();

            if ($finalGames->isEmpty()) {
                return null;
            }

            $teams = [];
            foreach ($finalGames as $game) {
                $team1 = min($game->home_team_id, $game->away_team_id);
                $team2 = max($game->home_team_id, $game->away_team_id);

                if (!isset($teams[$team1])) $teams[$team1] = 0;
                if (!isset($teams[$team2])) $teams[$team2] = 0;

                if ($game->home_goals > $game->away_goals) {
                    $teams[$game->home_team_id]++;
                } elseif ($game->home_goals < $game->away_goals) {
                    $teams[$game->away_team_id]++;
                }
            }

            foreach ($teams as $teamId => $wins) {
                if ($wins >= 2) {
                    return $teamId;
                }
            }

            return null;
        }

        $standings = $this->analyticService->getStandings($championshipId);
        return $standings->first()?->team_id;
    }
}
