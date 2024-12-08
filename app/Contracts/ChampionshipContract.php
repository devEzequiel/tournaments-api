<?php

namespace App\Contracts;

interface ChampionshipContract
{
    public function find (int $championship_id);
    public function create (array $data);
    public function all();
    public function getFixtures(int $championshipId);
    public function update (int $data, int $championship_id);
    public function delete (int $championship_id);
}
