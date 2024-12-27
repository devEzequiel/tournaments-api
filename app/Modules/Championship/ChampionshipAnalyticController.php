<?php

namespace App\Modules\Championship;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function getTeamStats(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            $stats = $this->service->getTeamStats($data);

            return $this->responseOk($stats);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function getTableData(int $champ): JsonResponse
    {
        try {
            $stats = $this->service->getCrossedResults($champ);

            return $this->responseOk($stats);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function getClashes(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            dd($data);
            $clashes = $this->service->getHead2Head($data);

            return $this->responseOk($clashes);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
