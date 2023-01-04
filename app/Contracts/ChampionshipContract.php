<?php

namespace App\Contracts;

interface ChampionshipContract
{
    public function find (int $id);
    public function findFixture (int $fixture_id);
    public function create ($data);
    public function all();
    public function getFixtures(int $id);
    public function update ($data, $id);
    public function delete ($id);
}
