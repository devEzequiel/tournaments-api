<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class UtilsController extends Controller
{
    public function healthCheck()
    {
        echo "ok";
    }
}
