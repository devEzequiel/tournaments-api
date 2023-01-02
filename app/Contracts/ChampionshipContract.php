<?php

namespace App\Contracts;

interface ChampionshipContract
{
    public function find (int $id);
    public function create ($data);
    public function all();
    public function update ($data, $id);
    public function delete ($id);
}
