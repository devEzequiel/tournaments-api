<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;

class TeamAnalyticController extends Controller
{
    public function currentPlayersData(int $id)
    {
        try {
            $team = $this->service->getCurrentPlayersData($id);

            return $this->responseOk($team);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
