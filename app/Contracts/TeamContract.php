<?php

namespace App\Contracts;

interface TeamContract
{
    public function find (int $id);
    public function create ($data);
    public function all();
    public function update ($data, $id);
    public function delete ($id);
}
