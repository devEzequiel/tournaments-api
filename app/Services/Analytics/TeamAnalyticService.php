<?php

namespace App\Services\Analytics;

use App\Models\Team;
use App\Services\BaseService;

class TeamAnalyticService extends BaseService implements TeamAnalyticContract
{
    public function __construct()
    {
        parent::__construct(new Team());
    }
    public function getCurrentPlayersData(int $id)
    {
        $team_players_data = $this->model::query()->where('team_id', $id)
            ->get();

        if (!$team_players) {
            throw new Exception('Nenhum jogador encontrado');
        }

        return $team_players;
    }
}
