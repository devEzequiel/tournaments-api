<?php

namespace App\Modules\Fixture;

use App\Http\Controllers\Controller;
use App\Http\Requests\Goal\PlayMatchRequest;
use Illuminate\Http\JsonResponse;

class FixtureController extends Controller
{
    public function __construct(protected FixtureService $service)
    {
    }

    public function getFixtures(int $championship_id)
    {
        try {
            $fixtures = $this->service->getAllFixtures($championship_id);

            return $this->responseOk($fixtures);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function getFixturesWithBasicInfo(int $championship_id): JsonResponse
    {
        try {
            $fixtures = $this->service->getFixturesWithBasicInfo($championship_id);

            return $this->responseOk($fixtures);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function getUnplayedFixtures(int $championship_id)
    {
        try {
            $fixtures = $this->service->getUnplayedFixtures($championship_id);

            return $this->responseOk($fixtures);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function playMatch(PlayMatchRequest $request)
    {
        $data = $request->validated();
        try {
            $this->service->playMatch($data);

            return $this->responseOk((array)'Partida jogada com sucesso');
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
