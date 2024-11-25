<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\CreatePlayerRateRequest;
use App\Services\Player\PlayerRateService;
use Exception;

class PlayerRateController extends Controller
{
    public function __construct(protected PlayerRateService $service)
    {
    }

    public function store(CreatePlayerRateRequest $request): \Illuminate\Http\JsonResponse
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
