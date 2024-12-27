<?php

namespace App\Modules\Team;

use App\Contracts\TeamContract;
use App\Models\Player;
use App\Models\Team;
use App\Services\BaseService;
use Exception;

class TeamService extends BaseService implements TeamContract
{
    public function __construct()
    {
        parent::__construct(new Team());
    }

    public function create($data): bool
    {
        return (bool)$this->model::create($data);
    }

    /**
     * @throws Exception
     */
    public function find(int $id)
    {
        $team = $this->model::query()
            ->where('id', $id)
            ->first();

        if (!$team) {
            throw new Exception('Time não encontrado');
        }

        return $team;
    }

    /**
     * @throws Exception
     */
    public function getCurrentPlayers(int $id)
    {
        $team_players = Team::query()
            ->where('team_id', $id)
            ->get()->map(fn($team_players) => [
                'id' => $team_players->id,
                'name' => $team_players->name,
                'players' => $team_players->pluck('name')
            ])->toArray();

        if (!$team_players) {
            throw new Exception('Nenhum jogador encontrado');
        }

        return $team_players;
    }

    /**
     * @throws Exception
     */
    public function all()
    {
        $team = $this->model::query()
            ->with('players')
            ->get();

        if (!$team)
            throw new Exception('Nenhum time encontrado');

        return $team;
    }

    /**
     * @throws Exception
     */
    public function update($data, $id): bool
    {
        $team = $this->model::find((int)$id);

        if (!$team)
            throw new Exception('Time não encontrado');

        return (bool)$team->update($data);
    }

    /**
     * @throws Exception
     */
    public function delete($id): bool
    {
        $team = $this->model::find($id);

        if (!$team)
            throw new Exception('Time não encontrado');

        return (bool)$team->delete();
    }

    public function findByName(string $name)
    {
        $team = $this->model::query()
            ->where('name', $name)
            ->with(['players' => function ($query) {
                $query->select('players.id', 'players.name');
            }])
            ->select('id', 'name', 'first_color', 'second_color', 'created_at')
            ->first();

        if (!$team) {
            return null;
        }

        $team->players = $this->getPlayerStatsByTeam($team->id);

        return $team;
    }

    private function getPlayerStatsByTeam(int $teamId)
    {
        return Player::query()
            ->selectRaw('
            players.id AS player_id,
            players.name AS player_name,
            COUNT(DISTINCT player_rates.fixture_id) AS matches_played,
            IFNULL(
                (
                    SELECT COUNT(g.id)
                    FROM goals g
                    JOIN fixtures f ON f.id = g.fixture_id
                    WHERE g.scorer_id = players.id AND f.team_id = ?
                ), 0
            ) AS total_goals,
            IFNULL(
                (
                    SELECT COUNT(a.id)
                    FROM goals a
                    JOIN fixtures f ON f.id = a.fixture_id
                    WHERE a.assist_id = players.id AND f.team_id = ?
                ), 0
            ) AS total_assists,
            IFNULL(AVG(player_rates.rate), 0) AS avg_rate
        ', [$teamId, $teamId])
            ->join('team_player', 'team_player.player_id', '=', 'players.id') // Relação com a tabela intermediária
            ->where('team_player.team_id', $teamId) // Filtra os jogadores pelo time específico
            ->where('team_player.current_team', true) // Considera apenas jogadores do time atual
            ->leftJoin('player_rates', 'player_rates.player_id', '=', 'players.id')
            ->leftJoin('fixtures', 'fixtures.id', '=', 'player_rates.fixture_id')
            ->groupBy('players.id', 'players.name')
            ->get();
    }
}
