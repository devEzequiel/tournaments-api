<?php

namespace App\Modules\Player;

use App\Contracts\Analytic\PlayerAnalyticContract;
use App\Models\Player;
use App\Services\BaseService;
use Illuminate\Support\Facades\DB;

class PlayerAnalyticService implements PlayerAnalyticContract
{
    public function getPlayersAnalytics()
    {
        return DB::table('players')
            ->select(
                'players.id',
                'players.name',
                'teams.id as team_id',
                'teams.name as team_name',
                'teams.first_color',
                'teams.second_color',
                DB::raw('COALESCE(player_matches.matches, 0) as matches'), // Número total de partidas
                DB::raw('COALESCE(player_goals.goals, 0) as goals'),       // Número total de gols
                DB::raw('COALESCE(player_assists.assists, 0) as assists'), // Número total de assistências
                DB::raw('COALESCE(ROUND(player_rates.average_rate, 1), 0) as average_rate'), // Nota média arredondada
                DB::raw('COALESCE(best_player_awards.count, 0) as best_player_awards'), // Total de prêmios de melhor jogador
                DB::raw('COALESCE(golden_boot_awards.count, 0) as golden_boot_awards'), // Total de títulos de artilheiro
                DB::raw('COALESCE(playmaker_awards.count, 0) as playmaker_awards')      // Total de prêmios de melhor assistente
            )
            // Join com o time atual do jogador
            ->leftJoin('team_player', function ($join) {
                $join->on('players.id', '=', 'team_player.player_id')
                    ->where('team_player.current_team', '=', true);
            })
            ->leftJoin('teams', 'teams.id', '=', 'team_player.team_id')

            // Subquery: Número de partidas (player_rates)
            ->leftJoinSub(
                DB::table('player_rates')
                    ->select('player_id', DB::raw('COUNT(*) as matches'))
                    ->groupBy('player_id'),
                'player_matches',
                'players.id',
                '=',
                'player_matches.player_id'
            )

            // Subquery: Número de gols
            ->leftJoinSub(
                DB::table('goals')
                    ->select('scorer_id', DB::raw('COUNT(*) as goals'))
                    ->groupBy('scorer_id'),
                'player_goals',
                'players.id',
                '=',
                'player_goals.scorer_id'
            )

            // Subquery: Número de assistências
            ->leftJoinSub(
                DB::table('goals')
                    ->select('assist_id', DB::raw('COUNT(*) as assists'))
                    ->groupBy('assist_id'),
                'player_assists',
                'players.id',
                '=',
                'player_assists.assist_id'
            )

            // Subquery: Nota média (player_rates)
            ->leftJoinSub(
                DB::table('player_rates')
                    ->select('player_id', DB::raw('AVG(rate) as average_rate'))
                    ->groupBy('player_id'),
                'player_rates',
                'players.id',
                '=',
                'player_rates.player_id'
            )

            // Subquery: Awards - Best Player
            ->leftJoinSub(
                DB::table('awards')
                    ->select('best_player', DB::raw('COUNT(*) as count'))
                    ->groupBy('best_player'),
                'best_player_awards',
                'players.id',
                '=',
                'best_player_awards.best_player'
            )

            // Subquery: Awards - Golden Boot
            ->leftJoinSub(
                DB::table('awards')
                    ->select('golden_boot', DB::raw('COUNT(*) as count'))
                    ->groupBy('golden_boot'),
                'golden_boot_awards',
                'players.id',
                '=',
                'golden_boot_awards.golden_boot'
            )

            // Subquery: Awards - Playmaker
            ->leftJoinSub(
                DB::table('awards')
                    ->select('playmaker', DB::raw('COUNT(*) as count'))
                    ->groupBy('playmaker'),
                'playmaker_awards',
                'players.id',
                '=',
                'playmaker_awards.playmaker'
            )

            ->groupBy(
                'players.id',
                'players.name',
                'teams.id',
                'teams.name',
                'teams.first_color',
                'teams.second_color',
                'player_matches.matches',
                'player_goals.goals',
                'player_assists.assists',
                'player_rates.average_rate',
                'best_player_awards.count',
                'golden_boot_awards.count',
                'playmaker_awards.count'
            )
            ->orderBy('players.id')
            ->get();
    }
}
