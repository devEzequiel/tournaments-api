<?php

namespace App\Services\Player;

use App\Contracts\PlayerContract;
use App\Models\Player;
use App\Services\BaseService;
use Exception;

class PlayerService extends BaseService implements PlayerContract
{
    public function __construct()
    {
        parent::__construct(new Player());
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
        $player = $this->model::query()
            ->with('team')
            ->get()->map(fn($player) => [
                'id' => $player->id,
                'name' => $player->name,
                'team_name' => $player->team->name ?? null,
//                    'team_name' => $player->team->name,
            ]);

        if (!$player) throw new Exception('Nenhum jogador encontrado');

        return $player;
    }

    /**
     * @throws Exception
     */
    public function update($data, $id): bool
    {
        $player = $this->model::find((int)$data['id']);

        if (!$player) throw new Exception('Jogador não encontrado');

        return (bool)$player->update($data);
    }

    /**
     * @throws Exception
     */
    public function delete($id): bool
    {
        $player = $this->model::find($id);

        if (!$player) throw new Exception('Jogador não encontrado');

        return (bool)$player->delete();
    }
}
