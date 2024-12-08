<?php

namespace App\Services\Analytics;

interface TeamAnalyticContract
{
    public function getCurrentPlayersData(int $id);

    public  function getAllPlayersData(int $id);
}
