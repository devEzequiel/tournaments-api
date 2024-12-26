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
            ->join('teams', 'teams.id', '=', 'standings_summary.team_id') // Junta informações dos times
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
        // Busca todos os confrontos e soma os gols de cada time no campeonato
        $results = Fixture::query()
            ->selectRaw('
            LEAST(home_team_id, away_team_id) AS team1_id, -- Sempre o menor ID será o primeiro
            GREATEST(home_team_id, away_team_id) AS team2_id, -- Sempre o maior ID será o segundo
            SUM(CASE WHEN home_team_id < away_team_id THEN home_goals ELSE away_goals END) AS team1_goals,
            SUM(CASE WHEN home_team_id < away_team_id THEN away_goals ELSE home_goals END) AS team2_goals
        ')
            ->where('championship_id', $championship_id) // Filtra pelo campeonato
            ->where('is_played', true) // Considera apenas jogos já realizados
            ->groupBy('team1_id', 'team2_id') // Garante o agrupamento por confrontos entre os mesmos times
            ->get();

        // Lista única de todos os times participantes
        $teams = $results
            ->pluck('team1_id', 'team1_id')
            ->merge($results->pluck('team2_id', 'team2_id'))
            ->unique()
            ->mapWithKeys(function ($id) {
                return [$id => Team::find($id)->name]; // Faz o mapeamento dos IDs para os nomes
            });

        $matrix = [];

        // Construção da matriz com os resultados cruzados
        foreach ($teams as $team_id_row => $team_name_row) {
            $row = [];

            foreach ($teams as $team_id_col => $team_name_col) {
                // Caso "A vs A" (auto-confrontos)
                if ($team_id_row === $team_id_col) {
                    $row[$team_name_col] = null;
                    continue;
                }

                // Busca os confrontos entre os dois times
                $match = $results->first(function ($item) use ($team_id_row, $team_id_col) {
                    return ($item->team1_id === $team_id_row && $item->team2_id === $team_id_col) ||
                        ($item->team1_id === $team_id_col && $item->team2_id === $team_id_row);
                });

                // Preenche com os gols acumulados ou "0-0" se não houver confrontos
                if ($match) {
                    if ($match->team1_id === $team_id_row) { // Verifica quem é o team1
                        $row[$team_name_col] = "{$match->team1_goals}-{$match->team2_goals}";
                    } else { // Caso contrário, o team2 foi identificado como team1
                        $row[$team_name_col] = "{$match->team2_goals}-{$match->team1_goals}";
                    }
                } else {
                    $row[$team_name_col] = "0-0"; // Jogadores nunca se enfrentaram
                }
            }

            $matrix[$team_name_row] = $row;
        }

        return [
            'teams' => $teams, // Lista de times (IDs e nomes)
            'matrix' => $matrix, // Matriz cruzada de resultados
        ];
    }

    public function getHead2Head(array $data)
    {
        $team1_id = $data['team1_id'];
        $team2_id = $data['team2_id'];
        $championship_id = $data['championship_id'];

        // Buscar os confrontos entre os dois times no campeonato
        $clashes = Fixture::query()
            ->where('championship_id', $championship_id) // Limitar ao campeonato especificado
            ->where(function ($query) use ($team1_id, $team2_id) {
                // Verifica se os times estavam em confronto (independentemente de mandante/visitante)
                $query->where(function ($q) use ($team1_id, $team2_id) {
                    $q->where('home_team_id', $team1_id)
                        ->where('away_team_id', $team2_id);
                })->orWhere(function ($q) use ($team1_id, $team2_id) {
                    $q->where('home_team_id', $team2_id)
                        ->where('away_team_id', $team1_id);
                });
            })
            ->orderBy('game_number', 'asc') // Organizar por número do jogo
            ->orderBy('round_number', 'asc') // Organizar por rodada
            ->select(
                'home_team_id',
                'away_team_id',
                'home_goals',
                'away_goals',
                'is_played'
            )
            ->get();

        // Reorganizar os resultados para garantir que o team1 seja sempre o time da casa
        $clashes->transform(function ($clash) use ($team1_id, $team2_id) {
            // Ajustar para que team1 seja sempre 'home_team' e team2 seja 'away_team'
            if ($clash->home_team_id !== $team1_id) {
                // Inverte os campos de time e placar
                $clash->home_team_id = $team1_id;
                $clash->away_team_id = $team2_id;

                // Também inverte os gols casa e visitante
                $temp_home_goals = $clash->home_goals;
                $clash->home_goals = $clash->away_goals;
                $clash->away_goals = $temp_home_goals;
            }

            // Preencher gols como 0-0 caso o jogo não tenha sido realizado
            if (!$clash->is_played) {
                $clash->home_goals = 0;
                $clash->away_goals = 0;
            }

            return $clash;
        });

        return $clashes;
    }

    public function getPlayersStats(int $champ_id)
    {
        // Estatísticas detalhadas por jogador
        $players = Player::query()
            ->selectRaw('
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
            ) AS total_assists,
            IFNULL(AVG(player_rates.rate), 0) AS avg_rate
        ', [$champ_id, $champ_id]) // Passa o ID do Campeonato para as subqueries
            ->leftJoin('player_rates', 'player_rates.player_id', '=', 'players.id') // Liga jogadores às taxas
            ->leftJoin('fixtures', 'fixtures.id', '=', 'player_rates.fixture_id') // Vincula partidas ao campeonato
            ->where('fixtures.championship_id', $champ_id) // Filtra pelo campeonato
            ->groupBy('players.id', 'players.name')
            ->get();

        // Calcula os "top 1, 2 e 3" para cada prêmio
        $awards = [
            // Melhor Jogador (maior média de rate)
            'best_player' => Player::query()
                ->select('players.id', 'players.name', DB::raw('AVG(player_rates.rate) as avg_rate'))
                ->join('player_rates', 'player_rates.player_id', '=', 'players.id')
                ->join('fixtures', 'fixtures.id', '=', 'player_rates.fixture_id')
                ->where('fixtures.championship_id', $champ_id)
                ->groupBy('players.id', 'players.name')
                ->orderByDesc('avg_rate') // Ordenação pela média do rate
                ->limit(3) // Limita ao top 3
                ->get(),

            // Artilheiro (mais gols)
            'golden_boot' => Player::query()
                ->select('players.id', 'players.name', DB::raw('COUNT(goals.id) as total_goals'))
                ->join('goals', 'goals.scorer_id', '=', 'players.id')
                ->join('fixtures', 'fixtures.id', '=', 'goals.fixture_id')
                ->where('fixtures.championship_id', $champ_id)
                ->groupBy('players.id', 'players.name')
                ->orderByDesc('total_goals') // Ordenação pelo número de gols
                ->limit(3) // Limita ao top 3
                ->get(),

            // Mais Assistências (playmaker)
            'playmaker' => Player::query()
                ->select('players.id', 'players.name', DB::raw('COUNT(assists.id) as total_assists'))
                ->join('goals AS assists', 'assists.assist_id', '=', 'players.id')
                ->join('fixtures', 'fixtures.id', '=', 'assists.fixture_id')
                ->where('fixtures.championship_id', $champ_id)
                ->groupBy('players.id', 'players.name')
                ->orderByDesc('total_assists') // Ordenação pelo número de assistências
                ->limit(3) // Limita ao top 3
                ->get(),

            // Melhor Goleiro (maior rate entre goleiros)
            'golden_glove' => Player::query()
                ->select('players.id', 'players.name', DB::raw('AVG(player_rates.rate) as avg_rate'))
                ->join('player_rates', 'player_rates.player_id', '=', 'players.id')
                ->join('fixtures', 'fixtures.id', '=', 'player_rates.fixture_id')
                ->where('fixtures.championship_id', $champ_id)
                ->where('players.gk', true) // Apenas goleiros
                ->groupBy('players.id', 'players.name')
                ->orderByDesc('avg_rate') // Ordenação pela média do rate
                ->limit(3) // Limita ao top 3
                ->get()
        ];

        return [
            'players' => $players->take(10),
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
        ', [$data['championship_id'], $data['championship_id']]) // Passa o championship_id para as subqueries
            ->leftJoin('player_rates', 'player_rates.player_id', '=', 'players.id') // Liga jogadores aos jogos (player_rates)
            ->leftJoin('fixtures', 'fixtures.id', '=', 'player_rates.fixture_id') // Liga player_rates -> fixtures
            ->leftJoin('team_player', function ($join) {
                $join->on('team_player.player_id', '=', 'players.id')
                    ->where(function ($query) {
                        $query->where('team_player.current_team', true) // Jogadores no time atualmente
                        ->orWhere(function ($query) {
                            // Jogadores nos times em períodos históricos
                            $query->whereColumn('team_player.left_at', '>=', 'fixtures.played_at')
                                ->whereColumn('team_player.joined_at', '<=', 'fixtures.played_at');
                        });
                    });
            })
            ->leftJoin('teams', 'teams.id', '=', 'team_player.team_id') // Liga jogadores ao time
            ->where('fixtures.championship_id', $data['championship_id']) // Filtra pelo campeonato
            ->where('teams.id', $data['team_id']) // Filtra pelo time informado
            ->groupBy('teams.id', 'teams.name', 'players.id', 'players.name') // Agrupa por time e jogador
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
