<?php

namespace App\Services\Player;

use App\Contracts\PlayerContract;
use App\Models\Player;
use App\Models\PreviousPlayerTeam;
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
        return (bool) $this->model::create($data);
    }

    /**
     * @throws Exception
     */
    public function find(int $id)
    {
        $player = $this->model::query()
            ->where('id', $id)
            ->with('team')
            ->get()->map(fn($player) => [
                'id' => $player->id,
                'name' => $player->name,
                'team_name' => $player->team->name ?? null
            ]);

        if (!$player) {
            throw new Exception('Jogador não encontrado');
        }

        return $player;
    }

    /**
     * @throws Exception
     */
    public function all()
    {
        $player = DB::table('players')
            ->leftJoin('goals as scored_goals', 'players.id', '=', 'scored_goals.scorer_id')
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
                'players.',
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

        if (!$player)
            throw new Exception('Nenhum jogador encontrado');

        return $player;
    }

    /**
     * @throws Exception
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
     */
    public function changeTeam($data): bool
    {
        $player = $this->model::find((int) $data['player_id']);

        if (!$player)
            throw new Exception('Jogador não encontrado');

        $previous_team = $player->team->id;
        PreviousPlayerTeam::create(['player_id' => $player->id, 'team_id' => $previous_team]);

        return (bool) $player->update($data);
    }

    /**
     * @throws Exception
     */
    public function delete($id): bool
    {
        $player = $this->model::find($id);

        if (!$player)
            throw new Exception('Jogador não encontrado');

        return (bool) $player->delete();
    }

    public function getCurrentTeamStats(int $id)
    {
        $playerStats = DB::table('players')
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

        if (!$playerStats) {
            return [];
        }

        return $playerStats;
    }
}
