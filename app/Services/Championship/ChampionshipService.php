<?php

namespace App\Services\Championship;

use App\Contracts\ChampionshipContract;
use App\Models\Championship;
use App\Services\BaseService;
use Exception;

class ChampionshipService extends BaseService implements ChampionshipContract
{
    public function __construct()
    {
        parent::__construct(new Championship());
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
        $championship = $this->model::query()
            ->where('id', $id)
            ->with('teams', 'players')
            ->get()->map(fn($championship) => [
                'id' => $championship->id,
                'name' => $championship->name,
                'team_name' => $championship->teams->pluck('name') ?? null,
                'player_name' => $championship->players->pluck('name') ?? null,
            ]);

        if (!$championship) {
            throw new Exception('Jogador não encontrado');
        }

        return $championship;
    }

    /**
     * @throws Exception
     */
    public function all()
    {
        $championship = $this->model::query()
            ->with('team')
            ->get()->map(fn($championship) => [
                'id' => $championship->id,
                'name' => $championship->name,
                'team_name' => $championship->team->name ?? null,
//                    'team_name' => $championship->team->name,
            ]);

        if (!$championship) throw new Exception('Nenhum jogador encontrado');

        return $championship;
    }

    /**
     * @throws Exception
     */
    public function update($data, $id): bool
    {
        $championship = $this->model::find((int)$id);

        if (!$championship) throw new Exception('Jogador não encontrado');

        return (bool)$championship->update($data);
    }

    /**
     * @throws Exception
     */
    public function delete($id): bool
    {
        $championship = $this->model::find($id);

        if (!$championship) throw new Exception('Jogador não encontrado');

        return (bool)$championship->delete();
    }
}
