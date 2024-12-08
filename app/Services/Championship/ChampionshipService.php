<?php

namespace App\Services\Championship;

use App\Contracts\ChampionshipContract;
use App\Models\Championship;
use App\Models\Fixture;
use App\Services\BaseService;
use Exception;

class ChampionshipService extends BaseService implements ChampionshipContract
{

    protected array $with = ['teams', 'fixtures'];

    public function __construct()
    {
        parent::__construct(new Championship());
    }

    /**
     * @throws Exception
     */
    public function create($data): bool
    {
        $champ = $this->model::create($data);
        self::createFixtures($data['teams'], $champ->id);
        return true;
    }

    /**
     * @throws Exception
     */
    public function find(int $championship_id)
    {
        $championship = $this->model::query()
            ->where('id', $championship_id)
            ->get();

        if (!$championship) {
            throw new Exception('Campeonato não encontrado');
        }

        return $championship;
    }

    /**
     * @throws Exception
     */
    public function all()
    {
        $championship = $this->model::query()
            ->get();

        if (!$championship) throw new Exception('Nenhum campeonato encontrado');

        return $championship;
    }

    /**
     * @throws Exception
     */
    public function update($data, $championship_id): bool
    {
        $championship = $this->model::find((int)$championship_id);

        if (!$championship) throw new Exception('Campeonato não encontrado');

        return (bool)$championship->update($data);
    }

    /**
     * @throws Exception
     */
    public function delete(int $championship_id): bool
    {
        $championship = $this->model::find($championship_id);

        if (!$championship) throw new Exception('Campeonato não encontrado');

        return (bool)$championship->delete();
    }

    /**
     * @throws Exception
     */
    public function getFixtures(int $championship_id)
    {
        $fixtures = Fixture::query()
            ->where('championship_id', $championship_id)
            ->orderBy('round_number', 'ASC')
            ->orderBy('game_number', 'ASC')
            ->with('awayTeam', 'homeTeam')
            ->get()->toArray();

        if (!$fixtures) throw new Exception('Nenhum confronto encontrado');

        return $fixtures;
    }

    private static function createFixtures(array $teams, int $championship_id): bool
    {
        $scheduleBuilder = new \ScheduleBuilder();
        $scheduleBuilder->setTeams($teams);
        $scheduleBuilder->setRounds(10);
        $scheduleBuilder->shuffle(14);
        $schedule = $scheduleBuilder->build();

        foreach ($schedule as $round => $teams) {
            $data = [];
            $data['championship_id'] = $championship_id;
            $data['round_number'] = $round;
            foreach ($teams as $game => $team) {
                $data['game_number'] = $game;
                $data['home_team_id'] = $team[0];
                $data['away_team_id'] = $team[1];
                Fixture::create($data);
            }
        }

        return true;
    }
}
