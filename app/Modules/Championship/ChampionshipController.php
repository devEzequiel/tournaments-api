<?php

namespace App\Modules\Championship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Championship\CreateChampionshipRequest;
use App\Http\Requests\Championship\UpdateChampionshipRequest;
use Exception;
use Illuminate\Http\JsonResponse;

class ChampionshipController extends Controller
{
    public function __construct(protected ChampionshipService $service)
    {
    }

    public function index()
    {
        try {
            $championships = $this->service->all();

            return inertia('Championships/Index', [
                'championships' => $championships,
            ]);
        } catch (Exception $e) {
            return inertia('Error', [
                'message' => $e->getMessage(),
            ]);
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

    public function show(string $name)
    {
        try {
            $championship = $this->service->findByName($name);

            return inertia('Championships/List', [
                'championship' => $championship,
            ]);
        } catch (Exception $e) {
            return inertia('Error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function detail(int $id)
    {
        try {
            $championship = $this->service->find($id);

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
