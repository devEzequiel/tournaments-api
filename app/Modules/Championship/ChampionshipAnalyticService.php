<?php

namespace App\Modules\Championship;

use App\Models\BaseModel;
use App\Models\Championship;
use App\Models\Fixture;
use App\Models\Player;
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
        SUM(standings_summary.total_games) AS total_games
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
                0 AS goals_away,
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
            ->join('teams', 'teams.id', '=', 'standings_summary.team_id') // Juntando as informações da tabela 'teams'
            ->groupBy('standings_summary.team_id', 'teams.name', 'teams.first_color', 'teams.second_color') // Agrupamos pelos campos de 'teams' também
            ->orderByDesc('points')
            ->orderByDesc('wins')
            ->orderByDesc('goal_diff')
            ->orderByDesc('goals_away')
            ->get();

        return $standings;
    }

    public function getPlayersStats(int $champ_id)
    {
        // Estatísticas detalhadas por jogador
        $players = Player::query()
            ->selectRaw('
            players.id AS player_id,
            players.name AS player_name,
            COUNT(DISTINCT player_rates.fixture_id) AS matches_played,
            IFNULL(SUM(CASE WHEN goals.scorer_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_goals,
            IFNULL(SUM(CASE WHEN assists.assist_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_assists,
            IFNULL(AVG(player_rates.rate), 0) AS avg_rate
        ')
            ->leftJoin('goals', 'goals.scorer_id', '=', 'players.id') // Gols marcados
            ->leftJoin('goals AS assists', 'assists.assist_id', '=', 'players.id') // Assistências
            ->leftJoin('player_rates', 'player_rates.player_id', '=', 'players.id') // Taxas dos jogadores
            ->leftJoin('fixtures', 'fixtures.id', '=', 'player_rates.fixture_id') // Vinculação com fixtures e campeonato
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
//            'golden_glove' => Player::query()
//                ->select('players.id', 'players.name', DB::raw('AVG(player_rates.rate) as avg_rate'))
//                ->join('player_rates', 'player_rates.player_id', '=', 'players.id')
//                ->join('fixtures', 'fixtures.id', '=', 'player_rates.fixture_id')
//                ->where('fixtures.championship_id', $champ_id)
//                ->where('players.gk', true) // Apenas goleiros
//                ->groupBy('players.id', 'players.name')
//                ->orderByDesc('avg_rate') // Ordenação pela média do rate
//                ->limit(3) // Limita ao top 3
//                ->get()
        ];

        return [
            'players' => $players,
            'awards' => $awards
        ];
    }
}
