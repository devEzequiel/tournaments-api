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

        $standings = $this->analyticService->getStanding($championshipId);

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

        // Ida e Volta na Final
        $this->createPlayoffFixture($championship, $second->team_id, $first->team_id, 'final', 1);
        $this->createPlayoffFixture($championship, $first->team_id, $second->team_id, 'final', 2);
    }

    /**
     * Gera playoff de semifinal (1º vs 4º, 2º vs 3º) + final
     * Ordem: Alterna entre as duas séries
     * Jogo 1: 3º vs 2º (ida série 1)
     * Jogo 2: 4º vs 1º (ida série 2)
     * Jogo 3: 2º vs 3º (volta série 1)
     * Jogo 4: 1º vs 4º (volta série 2)
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

        // Jogo 1: 3º vs 2º (ida série 1)
        $this->createPlayoffFixture($championship, $third->team_id, $second->team_id, 'semifinal', 1, 1);
        
        // Jogo 2: 4º vs 1º (ida série 2)
        $this->createPlayoffFixture($championship, $fourth->team_id, $first->team_id, 'semifinal', 1, 2);
        
        // Jogo 3: 2º vs 3º (volta série 1)
        $this->createPlayoffFixture($championship, $second->team_id, $third->team_id, 'semifinal', 2, 1);
        
        // Jogo 4: 1º vs 4º (volta série 2)
        $this->createPlayoffFixture($championship, $first->team_id, $fourth->team_id, 'semifinal', 2, 2);
    }

    /**
     * Cria uma fixture de playoff
     */
    private function createPlayoffFixture(
        Championship $championship,
        ?int $homeTeamId,
        ?int $awayTeamId,
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
        $team1AwayGoals = 0;
        $team2AwayGoals = 0;

        foreach ($seriesGames as $game) {
            // Contabilizar gols fora de casa
            if ($game->home_team_id == $team1Id) {
                $team2AwayGoals += $game->away_goals;
            } else {
                $team1AwayGoals += $game->away_goals;
            }
            
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

        // Empate em vitórias (1-1)? Verificar gols fora
        if ($team1Wins === 1 && $team2Wins === 1) {
            // Se gols fora também estão empatados, criar jogo 3
            if ($team1AwayGoals === $team2AwayGoals) {
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
                    $standings = $this->analyticService->getStanding($championship->id);
                    $team1Rank = $standings->search(fn($item) => $item->team_id === $team1Id);
                    $team2Rank = $standings->search(fn($item) => $item->team_id === $team2Id);

                    $homeTeamId = $team1Rank < $team2Rank ? $team1Id : $team2Id;
                    $awayTeamId = $homeTeamId === $team1Id ? $team2Id : $team1Id;

                    $this->createPlayoffFixture($championship, $homeTeamId, $awayTeamId, $stage, 3);
                }
            }
            // Se gols fora diferentes, já há um vencedor (não precisa de jogo 3)
        }

        // Se alguém venceu a semifinal (2 vitórias), verifica se já pode criar a final
        if ($stage === 'semifinal') {
             // For single-elimination or best-of-series where one team is decisive
             // We trigger updateFinalWithWinner widely
             $this->updateFinalWithWinner($championship, $team1Id);
        }
    }

    /**
     * Cria as partidas de final quando ambas as semifinais terminarem
     */
    private function updateFinalWithWinner(Championship $championship, int $winnerId): void
    {
        \Log::info('Verificando criação de final', [
            'championship_id' => $championship->id,
            'winner_id' => $winnerId
        ]);

        // Buscar TODOS os jogos de semifinal (jogados e não jogados)
        $allSemifinalGames = Fixture::where('championship_id', $championship->id)
            ->where('is_playoff', true)
            ->where('playoff_stage', 'semifinal')
            ->orderBy('id', 'asc')
            ->get();

        $semifinalGames = $allSemifinalGames->where('is_played', true);

        \Log::info('Jogos de semifinal', [
            'total' => $allSemifinalGames->count(),
            'jogados' => $semifinalGames->count()
        ]);

        // Agrupa os jogos por série (confronto), mantendo a ordem de criação
        $series = [];
        $seriesOrder = []; // Para manter a ordem de criação
        
        foreach ($allSemifinalGames as $game) {
            $key = min($game->home_team_id, $game->away_team_id) . '-' . max($game->home_team_id, $game->away_team_id);
            if (!isset($series[$key])) {
                $series[$key] = [
                    'team1' => min($game->home_team_id, $game->away_team_id),
                    'team2' => max($game->home_team_id, $game->away_team_id),
                    'team1_wins' => 0,
                    'team2_wins' => 0,
                    'team1_goals' => 0,
                    'team2_goals' => 0,
                    'team1_away_goals' => 0,
                    'team2_away_goals' => 0,
                    'games_played' => 0,
                    'total_games' => 0,
                    'winner' => null,
                ];
                $seriesOrder[] = $key; // Adiciona na ordem de criação
            }
            
            $series[$key]['total_games']++;
            
            if (!$game->is_played) {
                continue;
            }
            
            $series[$key]['games_played']++;

            // Contabilizar gols e gols fora de casa
            if ($game->home_team_id == $series[$key]['team1']) {
                $series[$key]['team1_goals'] += $game->home_goals;
                $series[$key]['team2_goals'] += $game->away_goals;
                $series[$key]['team2_away_goals'] += $game->away_goals; // team2 jogou fora
            } else {
                $series[$key]['team1_goals'] += $game->away_goals;
                $series[$key]['team2_goals'] += $game->home_goals;
                $series[$key]['team1_away_goals'] += $game->away_goals; // team1 jogou fora
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

        \Log::info('Séries de semifinal', ['series' => $series, 'ordem' => $seriesOrder]);

        // Determina o vencedor de cada série na ordem de criação
        $winners = [];
        foreach ($seriesOrder as $key) {
            $serie = $series[$key];
            
            // Verifica se a série está completa e tem um vencedor
            // Uma série está completa quando:
            // 1. Um time tem 2 vitórias (independente de quantos jogos foram jogados), OU
            // 2. Jogaram os 2 jogos base E critério de gols fora decidiu, OU
            // 3. Jogaram o jogo 3 decisivo
            
            $serieWinner = null;
            
            // Se tem 2 vitórias, série está decidida (independente da ordem dos jogos)
            if ($serie['team1_wins'] >= 2) {
                $serieWinner = $serie['team1'];
            } elseif ($serie['team2_wins'] >= 2) {
                $serieWinner = $serie['team2'];
            } 
            // Se jogaram pelo menos 2 jogos
            elseif ($serie['games_played'] >= 2) {
                // Se empataram em vitórias (1-1 ou 0-0)
                if ($serie['team1_wins'] == $serie['team2_wins']) {
                    // Se 1-1, verifica critério de gols fora
                    if ($serie['team1_wins'] == 1) {
                        if ($serie['team1_away_goals'] > $serie['team2_away_goals']) {
                            $serieWinner = $serie['team1'];
                        } elseif ($serie['team2_away_goals'] > $serie['team1_away_goals']) {
                            $serieWinner = $serie['team2'];
                        }
                        // Se gols fora empatados, aguarda jogo 3
                        elseif ($serie['games_played'] >= 3) {
                            // Jogo 3 foi jogado, determina pelo placar agregado
                            if ($serie['team1_goals'] > $serie['team2_goals']) {
                                $serieWinner = $serie['team1'];
                            } elseif ($serie['team2_goals'] > $serie['team1_goals']) {
                                $serieWinner = $serie['team2'];
                            } else {
                                // Empate total - usa melhor classificação
                                $standings = $this->analyticService->getStanding($championship->id);
                                $team1Rank = $standings->search(fn($item) => $item->team_id === $serie['team1']);
                                $team2Rank = $standings->search(fn($item) => $item->team_id === $serie['team2']);
                                $serieWinner = $team1Rank < $team2Rank ? $serie['team1'] : $serie['team2'];
                            }
                        }
                    }
                    // Se 0-0 após 2 jogos (dois empates), usa saldo de gols total ou continua sem vencedor
                    // Isso é raro, mas pode acontecer se ambos jogos terminarem 0-0 ou mesmo placar
                }
                // Se tem vitórias diferentes mas jogaram só 2 jogos
                else {
                    // Um time tem 1 vitória e o outro 0
                    // A série ainda não está completa - pode haver um jogo 3 ou o outro time pode vencer o próximo
                    // Não marca vencedor ainda
                }
            }
            
            if ($serieWinner) {
                $winners[] = $serieWinner;
            }
        }

        \Log::info('Vencedores das semifinais', [
            'winners_count' => count($winners),
            'winners' => $winners
        ]);

        // SOMENTE criar a final se AMBAS as séries tiverem vencedor definido
        if (count($winners) === 2) {
            $finalExists = Fixture::where('championship_id', $championship->id)
                ->where('is_playoff', true)
                ->where('playoff_stage', 'final')
                ->exists();

            \Log::info('Status da final', ['final_exists' => $finalExists]);

            if (!$finalExists) {
                $standings = $this->analyticService->getStanding($championship->id);
                $team1Rank = $standings->search(fn($item) => $item->team_id === $winners[0]);
                $team2Rank = $standings->search(fn($item) => $item->team_id === $winners[1]);

                $betterTeam = $team1Rank < $team2Rank ? $winners[0] : $winners[1];
                $worseTeam = $betterTeam === $winners[0] ? $winners[1] : $winners[0];

                \Log::info('Criando partidas de final', [
                    'better_team' => $betterTeam,
                    'worse_team' => $worseTeam
                ]);

                // Ida e Volta na Final
                $this->createPlayoffFixture($championship, $worseTeam, $betterTeam, 'final', 1);
                $this->createPlayoffFixture($championship, $betterTeam, $worseTeam, 'final', 2);

                \Log::info('Partidas de final criadas com sucesso');
            }
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
                $standings = $this->analyticService->getStanding($championship->id);
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

            // Agrupa por time para contar vitórias e gols
            $teams = [];
            foreach ($finalGames as $game) {
                $team1 = min($game->home_team_id, $game->away_team_id);
                $team2 = max($game->home_team_id, $game->away_team_id);

                if (!isset($teams[$team1])) {
                    $teams[$team1] = ['wins' => 0, 'goals' => 0, 'away_goals' => 0];
                }
                if (!isset($teams[$team2])) {
                    $teams[$team2] = ['wins' => 0, 'goals' => 0, 'away_goals' => 0];
                }

                // Contabiliza vitórias
                if ($game->home_goals > $game->away_goals) {
                    $teams[$game->home_team_id]['wins']++;
                } elseif ($game->home_goals < $game->away_goals) {
                    $teams[$game->away_team_id]['wins']++;
                }

                // Contabiliza gols totais e gols fora
                if ($game->home_team_id == $team1) {
                    $teams[$team1]['goals'] += $game->home_goals;
                    $teams[$team2]['goals'] += $game->away_goals;
                    $teams[$team2]['away_goals'] += $game->away_goals;
                } else {
                    $teams[$team2]['goals'] += $game->home_goals;
                    $teams[$team1]['goals'] += $game->away_goals;
                    $teams[$team1]['away_goals'] += $game->away_goals;
                }
            }

            // Verifica se algum time tem 2 vitórias
            foreach ($teams as $teamId => $stats) {
                if ($stats['wins'] >= 2) {
                    return $teamId;
                }
            }

            // Se empate 1-1 em vitórias, usa critério de gols fora
            $teamIds = array_keys($teams);
            if (count($teamIds) === 2) {
                $team1Id = $teamIds[0];
                $team2Id = $teamIds[1];
                
                if ($teams[$team1Id]['wins'] === $teams[$team2Id]['wins']) {
                    // Critério de gols fora
                    if ($teams[$team1Id]['away_goals'] > $teams[$team2Id]['away_goals']) {
                        return $team1Id;
                    } elseif ($teams[$team2Id]['away_goals'] > $teams[$team1Id]['away_goals']) {
                        return $team2Id;
                    }
                    
                    // Se gols fora empatados, usa saldo total de gols
                    if ($teams[$team1Id]['goals'] > $teams[$team2Id]['goals']) {
                        return $team1Id;
                    } elseif ($teams[$team2Id]['goals'] > $teams[$team1Id]['goals']) {
                        return $team2Id;
                    }
                }
            }

            return null;
        }

        $standings = $this->analyticService->getStanding($championshipId);
        return $standings->first()?->team_id;
    }
}
