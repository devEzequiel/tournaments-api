<?php

namespace App\Contracts;

interface FixtureContract
{
    public function getUnplayedFixtures(int $championship_id);
    public function getAllFixtures(int $championship_id);

    public function playMatch(array $data);
}
