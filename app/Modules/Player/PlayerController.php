<?php

namespace App\Modules\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\ChangePlayerTeamRequest;
use App\Http\Requests\Player\CreatePlayerRequest;
use App\Http\Requests\Player\UpdatePlayerRequest;
use Exception;
use Illuminate\Http\JsonResponse;

class PlayerController extends Controller
{
    public function __construct(protected PlayerService $service)
    {
    }

    public function index()
    {
        try {
            $data = $this->service->all();

            return inertia('Players/Index', [
                'players' => $data,
            ]);
        } catch (Exception $e) {
            return inertia('Error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    public function all(): JsonResponse
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

    public function show(int $id): JsonResponse
    {
        try {
            $player = $this->service->find($id);

            return $this->responseOk($player);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function getStatsByTeam(array $data)
    {
        try {
            $player = $this->service->getStatsByTeam($data);

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

    public function changeTeam(ChangePlayerTeamRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->changeTeam($data);

            return $this->responseCreated('Time alterado');
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
