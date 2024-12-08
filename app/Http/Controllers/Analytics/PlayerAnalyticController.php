<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Services\Analytics\PlayerAnalyticService;

class PlayerAnalyticController extends Controller
{
    public function __construct(protected PlayerAnalyticService $service)
    {
    }

    public function getPlsyr()
    {
        
    }
}
