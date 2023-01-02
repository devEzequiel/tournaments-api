<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\CreatePlayerRequest;
use App\Http\Requests\Player\UpdatePlayerRequest;
use App\Services\Player\PlayerService;
use Illuminate\Http\JsonResponse;
use Exception;

class PlayerController extends Controller
{
    public function __construct(protected PlayerService $service)
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

    public function store(CreatePlayerRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->create($data);

            return $this->responseCreated('Jogador adicionado');
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function find(int $id): JsonResponse
    {
        try {
            $player = $this->service->find($id);

            return $this->responseOk($player);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function update(UpdatePlayerRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->update($data, $id);

            return $this->responseCreated('Jogador atualizado');
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->responseAccepted();
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
