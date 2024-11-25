<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Services\Player\PlayerRateService;

class PlayerRateController extends Controller
{
    public function __construct(protected PlayerRateService $service)
    {
    }

    public function store(CreatePlayerRateRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->create($data);

            return $this->responseCreated('Nota adicionada');
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
