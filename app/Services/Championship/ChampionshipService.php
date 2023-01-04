<?php

namespace App\Services\Championship;

use App\Contracts\ChampionshipContract;
use App\Models\Championship;
use App\Services\BaseService;
use Exception;

class ChampionshipService extends BaseService implements ChampionshipContract
{
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
    public function find(int $id)
    {
        $championship = $this->model::query()
            ->where('id', $id)
            ->with('teams', 'players')
            ->get()->map(fn($championship) => [
                'id' => $championship->id,
                'name' => $championship->name,
                'team_name' => $championship->teams->pluck('name') ?? null,
                'player_name' => $championship->players->pluck('name') ?? null,
            ]);

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
            ->with('team')
            ->get()->map(fn($championship) => [
                'id' => $championship->id,
                'name' => $championship->name,
                'team_name' => $championship->team->name ?? null
            ]);

        if (!$championship) throw new Exception('Nenhum campeonato encontrado');

        return $championship;
    }

    /**
     * @throws Exception
     */
    public function update($data, $id): bool
    {
        $championship = $this->model::find((int)$id);

        if (!$championship) throw new Exception('Campeonato não encontrado');

        return (bool)$championship->update($data);
    }

    /**
     * @throws Exception
     */
    public function delete($id): bool
    {
        $championship = $this->model::find($id);

        if (!$championship) throw new Exception('Campeonato não encontrado');

        return (bool)$championship->delete();
    }

    /**
     * @throws Exception
     */
    public function getFixtures(int $id)
    {
        $fixtures = $this->model::query()
            ->where('championship_id', $id)
            ->with('awayTeam', 'homeTeam')
            ->get()->map(fn($fixture) => [
                'id' => $fixture->id,
                'name' => $fixture->name,
                'away_team' => $fixture->awayTeam->name ?? null,
                'home_team' => $fixture->homeTeam->name ?? null
            ]);

        if (!$fixtures) throw new Exception('Nenhum confronto encontrado');

        return $fixtures;
    }

    /**
     * @throws Exception
     */
    public function findFixture(int $fixture_id)
    {
        $fixtures = $this->model::query()
            ->where('id', $fixture_id)
            ->with('awayTeam', 'homeTeam')
            ->get()->map(fn($fixture) => [
                'id' => $fixture->id,
                'name' => $fixture->name,
                'away_team' => $fixture->awayTeam->name ?? null,
                'home_team' => $fixture->homeTeam->name ?? null
            ]);

        if (!$fixtures) {
            throw new Exception('Confronto não encontrado');
        }

        return $fixtures;
    }

    private static function createFixtures(array $teams, int $champ_id)
    {
//        pesquisar um algoritmo pra array x array
//        foreach ($teams as ) {
//
//        }
    }
}
