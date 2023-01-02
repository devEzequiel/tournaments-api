<?php

namespace App\Services\Team;

use App\Contracts\PlayerContract;
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
            ->with('players')
            ->get()->map(fn($team) => [
                'id' => $team->id,
                'name' => $team->name,
                'players' => $team->players->pluck('name')
            ]);

        if (!$team) {
            throw new Exception('Time não encontrado');
        }

        return $team;
    }

    /**
     * @throws Exception
     */
    public function all()
    {
        $team = $this->model::query()
            ->with('players')
            ->get()->map(fn($team) => [
                'id' => $team->id,
                'name' => $team->name,
                'players' => $team->players->pluck('name') ?? null
            ]);

        if (!$team) throw new Exception('Nenhum time encontrado');

        return $team;
    }

    /**
     * @throws Exception
     */
    public function update($data, $id): bool
    {
        $team = $this->model::find((int)$id);

        if (!$team) throw new Exception('Time não encontrado');

        return (bool)$team->update($data);
    }

    /**
     * @throws Exception
     */
    public function delete($id): bool
    {
        $team = $this->model::find($id);

        if (!$team) throw new Exception('Time não encontrado');

        return (bool)$team->delete();
    }
}
