<?php

namespace App\Modules\Championship;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ChampionshipAnalyticController extends Controller
{
    public function __construct(protected ChampionshipAnalyticService $service)
    {
    }

    public function getStandings(int $champ_id): JsonResponse
    {
        try {
            $data = $this->service->getStanding($champ_id);

            return $this->responseOk($data);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function getPlayersStats(int $champ_id)
    {
        try {
            $data = $this->service->getPlayersStats($champ_id);

            return $this->responseOk($data);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
