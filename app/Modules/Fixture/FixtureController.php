<?php

namespace App\Modules\Fixture;

use App\Http\Controllers\Controller;
use App\Http\Requests\Fixture\PlayMatchRequest;
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
        $request->validated();
        $data = $request->all();
        try {
            $result = $this->service->playMatch($data);

            $message = 'Partida jogada com sucesso';
            if (isset($result['final_round_generated']) && $result['final_round_generated']) {
                $message .= '. Rodada final gerada automaticamente!';
            }

            return $this->responseOk($result, $message);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
