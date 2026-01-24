<?php

namespace App\Modules\Championship;

use App\Models\BaseModel;
use App\Models\Championship;
use App\Models\Fixture;
use App\Models\Player;
use App\Models\Team;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class ChampionshipAnalyticService extends BaseService
{
    public function __construct(Championship $model)
    {
        parent::__construct($model);
    }

    public function getStanding(int $championship_id)
    {
        $standings = Fixture::query()
            ->selectRaw('
            teams.name,
            teams.first_color,
            teams.second_color,
            standings_summary.team_id,
            SUM(standings_summary.points) AS points,
            SUM(standings_summary.wins) AS wins,
            SUM(standings_summary.draws) AS draws,
            SUM(standings_summary.losses) AS losses,
            SUM(standings_summary.goal_diff) AS goal_diff,
            SUM(standings_summary.goals_scored) AS goals_scored,
            SUM(standings_summary.goals_conceded) AS goals_conceded,
            SUM(standings_summary.goals_away) AS goals_away,
            SUM(standings_summary.played) AS played,
            SUM(standings_summary.total_games) AS total_games,
            RANK() OVER (
                ORDER BY
                    SUM(standings_summary.points) DESC,
                    SUM(standings_summary.wins) DESC,
                    SUM(standings_summary.goal_diff) DESC,
                    SUM(standings_summary.goals_away) DESC
            ) AS position
        ')
            ->fromSub(function ($query) use ($championship_id) {
                $query->selectRaw('
                home_team_id AS team_id,
                SUM(CASE WHEN home_goals > away_goals AND is_played = true THEN 3 WHEN home_goals = away_goals AND is_played = true THEN 1 ELSE 0 END) AS points,
                SUM(CASE WHEN home_goals > away_goals AND is_played = true THEN 1 ELSE 0 END) AS wins,
                SUM(CASE WHEN home_goals = away_goals AND is_played = true THEN 1 ELSE 0 END) AS draws,
                SUM(CASE WHEN home_goals < away_goals AND is_played = true THEN 1 ELSE 0 END) AS losses,
                SUM(CASE WHEN is_played = true THEN home_goals - away_goals ELSE 0 END) AS goal_diff,
                SUM(CASE WHEN is_played = true THEN home_goals ELSE 0 END) AS goals_scored,
                SUM(CASE WHEN is_played = true THEN away_goals ELSE 0 END) AS goals_conceded,
                SUM(CASE WHEN is_played = true THEN home_goals ELSE 0 END) AS goals_away,
                COUNT(CASE WHEN is_played = true THEN 1 ELSE NULL END) AS played,
                COUNT(*) AS total_games
            ')
                    ->from('fixtures')
                    ->where('championship_id', $championship_id)
                    ->groupBy('home_team_id')
                    ->unionAll(
                        Fixture::query()
                            ->selectRaw('
                        away_team_id AS team_id,
                        SUM(CASE WHEN away_goals > home_goals AND is_played = true THEN 3 WHEN away_goals = home_goals AND is_played = true THEN 1 ELSE 0 END) AS points,
                        SUM(CASE WHEN away_goals > home_goals AND is_played = true THEN 1 ELSE 0 END) AS wins,
                        SUM(CASE WHEN away_goals = home_goals AND is_played = true THEN 1 ELSE 0 END) AS draws,
                        SUM(CASE WHEN away_goals < home_goals AND is_played = true THEN 1 ELSE 0 END) AS losses,
                        SUM(CASE WHEN is_played = true THEN away_goals - home_goals ELSE 0 END) AS goal_diff,
                        SUM(CASE WHEN is_played = true THEN away_goals ELSE 0 END) AS goals_scored,
                        SUM(CASE WHEN is_played = true THEN home_goals ELSE 0 END) AS goals_conceded,
                        SUM(CASE WHEN is_played = true THEN away_goals ELSE 0 END) AS goals_away,
                        COUNT(CASE WHEN is_played = true THEN 1 ELSE NULL END) AS played,
                        COUNT(*) AS total_games
                    ')
                            ->from('fixtures')
                            ->where('championship_id', $championship_id)
                            ->groupBy('away_team_id')
                    );
            }, 'standings_summary')
            ->join('teams', 'teams.id', '=', 'standings_summary.team_id')
            ->groupBy(
                'standings_summary.team_id',
                'teams.name',
                'teams.first_color',
                'teams.second_color'
            )
            ->orderByRaw('
            points DESC,
            wins DESC,
            goal_diff DESC,
            goals_away DESC
        ')
            ->get();

        return $standings;
    }

    public function getCrossedResults(int $championship_id)
    {
        $championship = Championship::find($championship_id);
        
        $results = Fixture::query()
            ->selectRaw('
            LEAST(home_team_id, away_team_id) AS team1_id,
            GREATEST(home_team_id, away_team_id) AS team2_id,
            SUM(CASE WHEN home_team_id < away_team_id THEN home_goals ELSE away_goals END) AS team1_goals,
            SUM(CASE WHEN home_team_id < away_team_id THEN away_goals ELSE home_goals END) AS team2_goals,
            MAX(is_playoff) AS has_playoff
        ')
            ->where('championship_id', $championship_id)
            ->groupBy('team1_id', 'team2_id')
            ->get();

        $teams = $results
            ->pluck('team1_id', 'team1_id')
            ->merge($results->pluck('team2_id', 'team2_id'))
            ->unique()
            ->mapWithKeys(function ($id) {
                return [$id => Team::find($id)->name];
            });

        $finalRoundPredictions = [];
        if ($championship && $championship->rounds % 2 === 1 && $championship->rounds > 1) {
            $finalRound = $championship->rounds;
            
            $finalRoundExists = Fixture::where('championship_id', $championship_id)
                ->where('round_number', $finalRound)
                ->exists();
            
            if (!$finalRoundExists) {
                foreach ($teams as $team1Id => $team1Name) {
                    foreach ($teams as $team2Id => $team2Name) {
                        if ($team1Id >= $team2Id) continue;
                        
                        $previousMatches = Fixture::where('championship_id', $championship_id)
                            ->where('round_number', '<', $finalRound)
                            ->where(function ($query) use ($team1Id, $team2Id) {
                            $query->where(function ($q) use ($team1Id, $team2Id) {
                                $q->where('home_team_id', $team1Id)
                                  ->where('away_team_id', $team2Id);
                            })->orWhere(function ($q) use ($team1Id, $team2Id) {
                                $q->where('home_team_id', $team2Id)
                                  ->where('away_team_id', $team1Id);
                            });
                        })
                        ->where('is_played', true)
                        ->get();

                    if ($previousMatches->isEmpty()) continue;

                    $team1Wins = 0;
                    $team2Wins = 0;
                    $team1Goals = 0;
                    $team2Goals = 0;
                    $team1AwayGoals = 0;
                    $team2AwayGoals = 0;

                    foreach ($previousMatches as $match) {
                        if ($match->home_team_id === $team1Id) {
                            $team1Goals += $match->home_goals;
                            $team2Goals += $match->away_goals;
                            $team2AwayGoals += $match->away_goals;
                            
                            if ($match->home_goals > $match->away_goals) {
                                $team1Wins++;
                            } elseif ($match->home_goals < $match->away_goals) {
                                $team2Wins++;
                            }
                        } else {
                            $team2Goals += $match->home_goals;
                            $team1Goals += $match->away_goals;
                            $team1AwayGoals += $match->away_goals;
                            
                            if ($match->home_goals > $match->away_goals) {
                                $team2Wins++;
                            } elseif ($match->home_goals < $match->away_goals) {
                                $team1Wins++;
                            }
                        }
                    }

                    $homeTeamId = $team1Id;
                    if ($team2Wins > $team1Wins) {
                        $homeTeamId = $team2Id;
                    } elseif ($team1Wins === $team2Wins) {
                        if ($team2Goals > $team1Goals) {
                            $homeTeamId = $team2Id;
                        } elseif ($team1Goals === $team2Goals && $team2AwayGoals > $team1AwayGoals) {
                            $homeTeamId = $team2Id;
                        }
                    }

                    $finalRoundPredictions[$team1Id . '-' . $team2Id] = $homeTeamId;
                    }
                }
            }
        }

        $matrix = [];

        foreach ($teams as $team_id_row => $team_name_row) {
            $row = [];

            foreach ($teams as $team_id_col => $team_name_col) {
                if ($team_id_row === $team_id_col) {
                    $row[$team_name_col] = null;
                    continue;
                }

                $match = $results->first(function ($item) use ($team_id_row, $team_id_col) {
                    return ($item->team1_id === $team_id_row && $item->team2_id === $team_id_col) ||
                        ($item->team1_id === $team_id_col && $item->team2_id === $team_id_row);
                });

                if ($match) {
                    if ($match->team1_id === $team_id_row) {
                        $scoreDisplay = "{$match->team1_goals}-{$match->team2_goals}";
                    } else {
                        $scoreDisplay = "{$match->team2_goals}-{$match->team1_goals}";
                    }
                    
                    if ($match->has_playoff) {
                        $scoreDisplay .= ' 🏆';
                    }
                    
                    if (!empty($finalRoundPredictions)) {
                        $key1 = min($team_id_row, $team_id_col) . '-' . max($team_id_row, $team_id_col);
                        if (isset($finalRoundPredictions[$key1])) {
                            $homeTeam = $finalRoundPredictions[$key1];
                            if ($homeTeam === $team_id_row) {
                                $scoreDisplay .= ' 🏠';
                            }
                        }
                    }
                    
                    $row[$team_name_col] = $scoreDisplay;
                } else {
                    $row[$team_name_col] = "0-0";
                }
            }

            $matrix[$team_name_row] = $row;
        }

        return [
            'teams' => $teams,
            'matrix' => $matrix,
        ];
    }

    public function getHead2Head(array $data)
    {
        $team1_id = $data['team1_id'];
        $team2_id = $data['team2_id'];
        $championship_id = $data['championship_id'];

        // Buscar informações do campeonato
        $championship = Championship::find($championship_id);

        // Buscar os times
        $team1 = Team::find($team1_id);
        $team2 = Team::find($team2_id);

        // Buscar os confrontos entre os dois times no campeonato
        $clashes = Fixture::query()
            ->where('championship_id', $championship_id)
            ->where(function ($query) use ($team1_id, $team2_id) {
                $query->where(function ($q) use ($team1_id, $team2_id) {
                    $q->where('home_team_id', $team1_id)
                        ->where('away_team_id', $team2_id);
                })->orWhere(function ($q) use ($team1_id, $team2_id) {
                    $q->where('home_team_id', $team2_id)
                        ->where('away_team_id', $team1_id);
                });
            })
            ->orderBy('round_number', 'asc')
            ->orderBy('game_number', 'asc')
            ->select(
                'home_team_id',
                'away_team_id',
                'home_goals',
                'away_goals',
                'is_played',
                'round_number'
            )
            ->get();

        // Reorganizar os resultados e adicionar nomes dos times
        $clashes->transform(function ($clash) use ($team1, $team2) {
            // Adiciona os nomes dos times
            if ($clash->home_team_id === $team1->id) {
                $clash->home_team = $team1->name;
                $clash->away_team = $team2->name;
            } else {
                $clash->home_team = $team2->name;
                $clash->away_team = $team1->name;
            }

            // Preencher gols como null caso o jogo ainda não tenha sido realizado
            if (!$clash->is_played) {
                $clash->home_goals = null;
                $clash->away_goals = null;
            }

            // Adiciona o campo round
            $clash->round = $clash->round_number;

            return $clash;
        });

        // Se for campeonato com rodadas ímpares, adicionar confronto projetado para rodada final
        if ($championship && $championship->rounds % 2 === 1 && $championship->rounds > 1) {
            $finalRound = $championship->rounds;
            
            // Verifica se já existe confronto na rodada final
            $hasFinalRound = $clashes->contains(function ($clash) use ($finalRound) {
                return $clash->round_number === $finalRound;
            });

            // Se não existe, calcular quem jogaria em casa
            if (!$hasFinalRound) {
                // Calcular estatísticas para determinar mando de campo
                $team1Wins = 0;
                $team2Wins = 0;
                $team1Goals = 0;
                $team2Goals = 0;
                $team1AwayGoals = 0;
                $team2AwayGoals = 0;

                foreach ($clashes as $clash) {
                    if ($clash->is_played) {
                        if ($clash->home_team_id === $team1_id) {
                            $team1Goals += $clash->home_goals;
                            $team2Goals += $clash->away_goals;
                            $team2AwayGoals += $clash->away_goals;
                            
                            if ($clash->home_goals > $clash->away_goals) {
                                $team1Wins++;
                            } elseif ($clash->home_goals < $clash->away_goals) {
                                $team2Wins++;
                            }
                        } else {
                            $team2Goals += $clash->home_goals;
                            $team1Goals += $clash->away_goals;
                            $team1AwayGoals += $clash->away_goals;
                            
                            if ($clash->home_goals > $clash->away_goals) {
                                $team2Wins++;
                            } elseif ($clash->home_goals < $clash->away_goals) {
                                $team1Wins++;
                            }
                        }
                    }
                }

                // Determina quem jogaria em casa usando os mesmos critérios do FinalRoundGeneratorService
                $homeTeamId = $team1_id;
                
                if ($team2Wins > $team1Wins) {
                    $homeTeamId = $team2_id;
                } elseif ($team1Wins === $team2Wins) {
                    if ($team2Goals > $team1Goals) {
                        $homeTeamId = $team2_id;
                    } elseif ($team1Goals === $team2Goals) {
                        if ($team2AwayGoals > $team1AwayGoals) {
                            $homeTeamId = $team2_id;
                        }
                    }
                }

                // Adiciona confronto projetado
                $projectedMatch = new \stdClass();
                $projectedMatch->home_team_id = $homeTeamId;
                $projectedMatch->away_team_id = $homeTeamId === $team1_id ? $team2_id : $team1_id;
                $projectedMatch->home_team = $homeTeamId === $team1_id ? $team1->name : $team2->name;
                $projectedMatch->away_team = $homeTeamId === $team1_id ? $team2->name : $team1->name;
                $projectedMatch->home_goals = null;
                $projectedMatch->away_goals = null;
                $projectedMatch->is_played = false;
                $projectedMatch->round_number = $finalRound;
                $projectedMatch->round = $finalRound;
                $projectedMatch->isProjected = true;

                $clashes->push($projectedMatch);
            }
        }

        return $clashes;
    }

    public function getPlayersStats(int $champ_id)
    {
        // Busca todos os times que participam do campeonato
        $teamsInChampionship = DB::table('fixtures')
            ->where('championship_id', $champ_id)
            ->selectRaw('DISTINCT home_team_id as team_id')
            ->union(
                DB::table('fixtures')
                    ->where('championship_id', $champ_id)
                    ->selectRaw('DISTINCT away_team_id as team_id')
            )
            ->pluck('team_id');

        // Estatísticas de TODOS os jogadores dos times do campeonato
        $players = Player::query()
            ->selectRaw('
            players.id AS player_id,
            players.name AS player_name,
            teams.first_color AS team_first_color,
            teams.second_color AS team_second_color,
            teams.name AS team_name,
            IFNULL(
                (
                    SELECT COUNT(DISTINCT pr.fixture_id)
                    FROM player_rates pr
                    JOIN fixtures f ON f.id = pr.fixture_id
                    WHERE pr.player_id = players.id AND f.championship_id = ?
                ), 0
            ) AS matches_played,
            IFNULL(
                (
                    SELECT COUNT(g.id)
                    FROM goals g
                    JOIN fixtures f ON f.id = g.fixture_id
                    WHERE g.scorer_id = players.id AND f.championship_id = ?
                ), 0
            ) AS total_goals,
            IFNULL(
                (
                    SELECT COUNT(a.id)
                    FROM goals a
                    JOIN fixtures f ON f.id = a.fixture_id
                    WHERE a.assist_id = players.id AND f.championship_id = ?
                ), 0
            ) AS total_assists,
            IFNULL(
                (
                    SELECT AVG(pr.rate)
                    FROM player_rates pr
                    JOIN fixtures f ON f.id = pr.fixture_id
                    WHERE pr.player_id = players.id AND f.championship_id = ?
                ), 0
            ) AS avg_rate
        ', [$champ_id, $champ_id, $champ_id, $champ_id])
            ->join('team_player', 'team_player.player_id', '=', 'players.id')
            ->join('teams', 'teams.id', '=', 'team_player.team_id')
            ->whereIn('team_player.team_id', $teamsInChampionship)
            ->where('team_player.current_team', true)
            ->groupBy('players.id', 'players.name', 'teams.first_color', 'teams.second_color', 'teams.name')
            ->get();

        // Calcula os "top 1, 2 e 3" para cada prêmio
        $awards = [
            // Melhor Jogador (maior média de rate) - só quem foi avaliado
            'best_player' => Player::query()
                ->selectRaw('
                    players.id,
                    players.name,
                    AVG(player_rates.rate) as avg_rate
                ')
                ->join('player_rates', 'player_rates.player_id', '=', 'players.id')
                ->join('fixtures', 'fixtures.id', '=', 'player_rates.fixture_id')
                ->where('fixtures.championship_id', $champ_id)
                ->groupBy('players.id', 'players.name')
                ->orderByDesc('avg_rate')
                ->limit(3)
                ->get(),

            // Artilheiro (mais gols) - qualquer jogador que marcou, mesmo sem player_rate
            'golden_boot' => Player::query()
                ->selectRaw('
                    players.id,
                    players.name,
                    COUNT(goals.id) as total_goals
                ')
                ->join('goals', 'goals.scorer_id', '=', 'players.id')
                ->join('fixtures', 'fixtures.id', '=', 'goals.fixture_id')
                ->where('fixtures.championship_id', $champ_id)
                ->groupBy('players.id', 'players.name')
                ->having('total_goals', '>', 0)
                ->orderByDesc('total_goals')
                ->limit(3)
                ->get(),

            // Mais Assistências (playmaker) - qualquer jogador que assistiu, mesmo sem player_rate
            'playmaker' => Player::query()
                ->selectRaw('
                    players.id,
                    players.name,
                    COUNT(goals.id) as total_assists
                ')
                ->join('goals', 'goals.assist_id', '=', 'players.id')
                ->join('fixtures', 'fixtures.id', '=', 'goals.fixture_id')
                ->where('fixtures.championship_id', $champ_id)
                ->groupBy('players.id', 'players.name')
                ->having('total_assists', '>', 0)
                ->orderByDesc('total_assists')
                ->limit(3)
                ->get(),
        ];

        return [
            'players' => $players,
            'awards' => $awards
        ];
    }

    public function getTeamStats(array $data)
    {
        // Query principal
        $results = Player::query()
            ->selectRaw('
            teams.id AS team_id,
            teams.name AS team_name,
            players.id AS player_id,
            players.name AS player_name,
            COUNT(DISTINCT player_rates.fixture_id) AS matches_played,
            IFNULL(
                (
                    SELECT COUNT(g.id)
                    FROM goals g
                    JOIN fixtures f ON f.id = g.fixture_id
                    WHERE g.scorer_id = players.id AND f.championship_id = ?
                ), 0
            ) AS total_goals,
            IFNULL(
                (
                    SELECT COUNT(a.id)
                    FROM goals a
                    JOIN fixtures f ON f.id = a.fixture_id
                    WHERE a.assist_id = players.id AND f.championship_id = ?
                ), 0
            ) AS total_assists
        ', [$data['championship_id'], $data['championship_id']])
            ->leftJoin('player_rates', 'player_rates.player_id', '=', 'players.id')
            ->leftJoin('fixtures', 'fixtures.id', '=', 'player_rates.fixture_id')
            ->leftJoin('team_player', function ($join) {
                $join->on('team_player.player_id', '=', 'players.id')
                    ->where(function ($query) {
                        $query->where('team_player.current_team', true)
                            ->orWhere(function ($subQuery) {
                            $query->whereColumn('team_player.left_at', '>=', 'fixtures.played_at')
                                ->whereColumn('team_player.joined_at', '<=', 'fixtures.played_at');
                        });
                    });
            })
            ->leftJoin('teams', 'teams.id', '=', 'team_player.team_id')
            ->where('fixtures.championship_id', $data['championship_id'])
            ->where('teams.id', $data['team_id'])
            ->groupBy('teams.id', 'teams.name', 'players.id', 'players.name')
            ->get();

        // Formata os resultados
        if ($results->isEmpty()) {
            return null; // Retorna null se não houver registros
        }

        // Estrutura de saída formatada
        $teamData = [
            'team' => [
                'id' => $results->first()->team_id,
                'name' => $results->first()->team_name,
            ],
            'players' => $results->map(function ($row) {
                return [
                    'player_id' => $row->player_id,
                    'player_name' => $row->player_name,
                    'matches_played' => $row->matches_played,
                    'total_goals' => $row->total_goals,
                    'total_assists' => $row->total_assists,
                ];
            })->toArray()
        ];

        return $teamData;
    }
}
