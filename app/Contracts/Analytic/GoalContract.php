<?php

namespace App\Contracts\Analytic;

interface GoalContract
{
    public function getByChampionship(int $championshipId);

    public function getAll();

    public function getByTeam(int $teamId);

    public function getByCurrentTeam(int $teamId);

    public function getByPreviousTeam(int $teamId);
}
