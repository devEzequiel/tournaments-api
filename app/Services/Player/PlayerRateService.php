<?php

namespace App\Services\Player;

use App\Contracts\PlayerRateContract;
use App\Models\PlayerRate;
use App\Models\TeamPlayer;
use App\Services\BaseService;

class PlayerRateService extends BaseService implements PlayerRateContract
{

    public function __construct()
    {
        parent::__construct(model: new PlayerRate());
    }

    public function create(array $data): bool
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
}
