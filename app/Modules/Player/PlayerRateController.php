<?php

namespace App\Modules\Player;

use App\Http\Controllers\Controller;
use App\Http\Requests\Player\CreatePlayerRateRequest;
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

            return $this->responseCreated();
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
