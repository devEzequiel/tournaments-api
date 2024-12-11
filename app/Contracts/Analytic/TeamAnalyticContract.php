<?php

namespace App\Contracts\Analytic;

interface TeamAnalyticContract
{
    public function getCurrentPlayersData(int $id);

    public  function getPlayersData(int $id);
}
