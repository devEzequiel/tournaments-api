<?php

namespace App\Services\Player;

use App\Contracts\PlayerContract;
use App\Models\Player;
use App\Models\TeamPlayer;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;

class PlayerService extends BaseService implements PlayerContract
{
    public function __construct()
    {
        parent::__construct(new Player());
    }

    public function create($data): bool
    {
        $player = $this->model::create($data);
        $pivot_data = [
            'team_id' => $data['team_id'],
            'player_id' => $player->id,
            'current_team' => true,
            'joined_at' => now()
        ];
        TeamPlayer::create($pivot_data);
        return true;
    }

    /**
     * @throws Exception
     *
     * return data from a especific player
     */
    public function find(int $id)
    {
        $player = DB::table('players')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->leftJoin('goals as scored_goals', 'players.id', '=', 'scored_goals.scorer_id')
            ->leftJoin('goals as assists', 'players.id', '=', 'assists.assist_id')
            ->leftJoin('awards', function ($join) {
                $join->on('players.id', '=', 'awards.golden_boot')
                    ->orOn('players.id', '=', 'awards.best_player')
                    ->orOn('players.id', '=', 'awards.playmaker')
                    ->orOn('players.id', '=', 'awards.golden_glove');
            })
            ->where('players.id', $id)
            ->select(
                'players.id',
                'players.name',
                'teams.name as team_name',
                DB::raw('COUNT(DISTINCT scored_goals.id) as total_goals'),
                DB::raw('COUNT(DISTINCT assists.id) as total_assists'),
                DB::raw('COUNT(DISTINCT awards.golden_boot) as golden_boot_awards'),
                DB::raw('COUNT(DISTINCT awards.best_player) as best_player_awards'),
                DB::raw('COUNT(DISTINCT awards.playmaker) as playmaker_awards'),
                DB::raw('COUNT(DISTINCT awards.golden_glove) as golden_glove_awards')
            )
            ->groupBy('players.id', 'players.name')
            ->get();
        ;

        if (!$player) {
            throw new Exception('jogador não encontrado');
        }

        return $player;
    }

    /**
     * @throws Exception
     * return data to every players
     */
    public function all()
    {
        $players = DB::table('players')
            ->leftJoin('goals as scored_goals', 'players.id', '=', 'scored_goals.scorer_id')
            ->leftJoin('teams', 'players.team_id', '=', 'teams.id')
            ->leftJoin('goals as assists', 'players.id', '=', 'assists.assist_id')
            ->leftJoin('awards', function ($join) {
                $join->on('players.id', '=', 'awards.golden_boot')
                    ->orOn('players.id', '=', 'awards.best_player')
                    ->orOn('players.id', '=', 'awards.playmaker')
                    ->orOn('players.id', '=', 'awards.golden_glove');
            })
            ->select(
                'players.id',
                'players.name',
                'teams.name as team_name',
                DB::raw('COUNT(DISTINCT scored_goals.id) as total_goals'),
                DB::raw('COUNT(DISTINCT assists.id) as total_assists'),
                DB::raw('COUNT(DISTINCT awards.golden_boot) as golden_boot_awards'),
                DB::raw('COUNT(DISTINCT awards.best_player) as best_player_awards'),
                DB::raw('COUNT(DISTINCT awards.playmaker) as playmaker_awards'),
                DB::raw('COUNT(DISTINCT awards.golden_glove) as golden_glove_awards')
            )
            ->groupBy('players.id')
            // ->orderByDesc('total_goals')
            ->get();
        ;

        if (!$players)
            throw new Exception('Nenhum jogador encontrado');

        return $players;
    }

    /**
     * @throws Exception
     *
     * update player name or another data (no team)
     */
    public function update($data, $id): bool
    {
        $player = $this->model::find((int) $data['id']);

        if (!$player)
            throw new Exception('Jogador não encontrado');

        return (bool) $player->update($data);
    }

    /**
     * @throws Exception
     *
     * update team player
     */
    public function changeTeam($data): bool
    {
        $previous_team = TeamPlayer::where('player_id', $data['player_id'])
            ->where('current_team', true)
            ->first();

        if (!$previous_team) {
            throw new Exception('Relação não encontrado');
        }

        $previous_team->update([
            'current_team' => false,
            'left_at' => now()
        ]);

        TeamPlayer::create(
            [
                'team_id' => $data['new_team_id'],
                'player_id' => $data['player_id '],
                'current_team' => true,
                'joined_at' => now()
            ]
        );

        return true;
    }

    /**
     * @throws Exception
     *
     * delete player
     *
     */
    public function delete($id): bool
    {
        $player = $this->model::find($id);

        if (!$player)
            throw new Exception('Jogador não encontrado');

        return (bool) $player->delete();
    }

    public function getStatsByTeam(array $data)
    {
        $timestamps = TeamPlayer::where('player_id', $data['player_id'])
            ->where('team_id', $data['player_id'])
            ->first();

        $startDate = $timestamps->joined_at;
        $endDate = $timestamps->left_at;

        $playerStats = DB::table('players')
            ->leftJoin('goals', 'players.id', '=', 'goals.scorer_id')
            ->leftJoin('fixtures as goal_fixtures', 'goals.fixture_id', '=', 'goal_fixtures.id')
            ->leftJoin('assists', 'players.id', '=', 'assists.assist_id')
            ->leftJoin('fixtures as assist_fixtures', 'assists.fixture_id', '=', 'assist_fixtures.id')
            ->leftJoin('awards', function ($join) {
                $join->on('players.id', '=', 'awards.best_player')
                    ->orOn('players.id', '=', 'awards.golden_boot')
                    ->orOn('players.id', '=', 'awards.playmaker')
                    ->orOn('players.id', '=', 'awards.golden_glove');
            })
            ->leftJoin('championships', 'awards.championship_id', '=', 'championships.id')
            ->select(
                'players.name',
                DB::raw('COUNT(DISTINCT goals.id) as total_goals'),
                DB::raw('COUNT(DISTINCT assists.id) as total_assists'),
                DB::raw('COUNT(DISTINCT awards.best_player) as best_player_awards'),
                DB::raw('COUNT(DISTINCT awards.golden_boot) as golden_boot_awards'),
                DB::raw('COUNT(DISTINCT awards.playmaker) as playmaker_awards'),
                DB::raw('COUNT(DISTINCT awards.golden_glove) as golden_glove_awards')
            )
            ->where('players.id', $data['player_id'])
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('goal_fixtures.timestamp', [$startDate, $endDate])
                    ->orWhereBetween('assist_fixtures.timestamp', [$startDate, $endDate])
                    ->orWhere(function ($subquery) use ($startDate, $endDate) {
                        $subquery->where('championships.started_at', '<=', $endDate)
                            ->where('championships.finished_at', '>=', $startDate);
                    });
            })
            ->groupBy('players.id', 'players.name')
            ->first();

        if (!$playerStats) {
            return [];
        }

        return $playerStats;
    }
}
