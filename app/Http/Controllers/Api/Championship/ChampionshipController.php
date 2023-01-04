<?php

namespace App\Http\Controllers\Api\Championship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Championship\CreateChampionshipRequest;
use App\Http\Requests\Championship\UpdateChampionshipRequest;
use App\Services\Championship\ChampionshipService;
use Illuminate\Http\JsonResponse;
use Exception;
class ChampionshipController extends Controller
{
    public function __construct(protected ChampionshipService $service)
    {
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->service->all();

            return $this->responseOk($data);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function store(CreateChampionshipRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->create($data);

            return $this->responseCreated('Campeonato adicionado');
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $championship = $this->service->find($id);

            return $this->responseOk($championship);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function showFixture(int $id): JsonResponse
    {
        try {
            $championship = $this->service->findFixture($id);

            return $this->responseOk($championship);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function getFixtures(int $id): JsonResponse
    {
        try {
            $fixtures = $this->service->getFixtures($id);

            return $this->responseOk($fixtures);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function update(UpdateChampionshipRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->update($data, $id);

            return $this->responseCreated('Campeonato atualizado');
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->responseAccepted();
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
