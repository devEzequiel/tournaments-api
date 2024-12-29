<?php

namespace App\Modules\Player;

use App\Http\Controllers\Controller;

class PlayerAnalyticController extends Controller
{
    public function __construct(protected PlayerAnalyticService $service)
    {
    }

    public function getAnalytics()
    {
        try {
            $data = $this->service->getPlayersAnalytics();
            return $this->responseOk($data);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
