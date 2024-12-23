<?php

namespace App\Modules\Team;

use App\Contracts\Analytic\TeamAnalyticContract;
use App\Models\Team;
use App\Services\BaseService;
use Exception;

class TeamAnalyticService extends BaseService implements TeamAnalyticContract
{
    public function __construct()
    {
        parent::__construct(new Team());
    }

    /**
     * @throws Exception
     */
    public function getCurrentPlayersData(int $id)
    {

        $team = $this->model::find($id);
        if (!$team) {
            throw new Exception('Time não encontrado');
        }

        $team_players_data = $team->players()->with([
            'fixtures' => function ($query) {
                $query->whereColumn('home_team_id', '=', 'team_id')
                    ->orWhereColumn('away_team_id', '=', 'team_id');
            },
            'fixtures.goals',
            'playerRates' => function ($query) {
                $query->selectRaw('player_id, AVG(rating) as average_rating')
                    ->groupBy('player_id');
            }
        ])->get()->map(function ($player) {
            $totalGoals = $player->fixtures->sum(function ($fixture) use ($player) {
                return $fixture->goals->where('scorer_id', $player->id)->count();
            });

            $totalAssists = $player->fixtures->sum(function ($fixture) use ($player) {
                return $fixture->goals->where('assist_id', $player->id)->count();
            });

            $totalMatches = $player->fixtures->count();

            $averagePlayerRate = optional($player->playerRates->first())->average_rating;

            return [
                'player_id' => $player->id,
                'player_name' => $player->name,
                'total_goals' => $totalGoals,
                'total_assists' => $totalAssists,
                'total_matches' => $totalMatches,
                'average_player_rate' => $averagePlayerRate,
            ];
        });

        if ($team_players_data->isEmpty()) {
            throw new Exception('Nenhum jogador encontrado');
        }

        return $team_players_data;
    }

    /**
     * @throws Exception
     */
    public function getPlayersData(int $id)
    {
        $team = $this->model::find($id);

        $pastAndCurrentPlayersData = $team->with(['players' => function ($query) use ($id) {
            $query->where('team_player.team_id', $id);
        }])->with([
            'players.fixtures' => function ($query) {
                $query->whereColumn('home_team_id', '=', 'team_id')
                    ->orWhereColumn('away_team_id', '=', 'team_id');
            },
            'players.fixtures.goals',
            'players.playerRates' => function ($query) {
                $query->selectRaw('player_id, AVG(rating) as average_rating')
                    ->groupBy('player_id');
            }
        ])->get()->map(function ($player) {
            $totalGoals = $player->fixtures->sum(function ($fixture) use ($player) {
                return $fixture->goals->where('scorer_id', $player->id)->count();
            });

            $totalAssists = $player->fixtures->sum(function ($fixture) use ($player) {
                return $fixture->goals->where('assist_id', $player->id)->count();
            });

            $totalMatches = $player->fixtures->count();

            $averagePlayerRate = optional($player->playerRates->first())->average_rating;

            return [
                'player_id' => $player->id,
                'player_name' => $player->name,
                'total_goals' => $totalGoals,
                'total_assists' => $totalAssists,
                'total_matches' => $totalMatches,
                'average_player_rate' => $averagePlayerRate,
                'current_team' => $player->pivot->current_team,
            ];
        });

        if ($pastAndCurrentPlayersData->isEmpty()) {
            throw new Exception('Nenhum jogador encontrado');
        }

        return $pastAndCurrentPlayersData;
    }
}
