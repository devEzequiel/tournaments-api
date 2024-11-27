<?php

namespace App\Contracts;

interface FixtureContract
{
    public function processPlayoffs();
    private function getTopTeams();
    private function createPlayoffFixtures();
    public function processNextPlayoffRound();
    private function getWinnersFromRound();
    private function getLosersFromRound();
}
