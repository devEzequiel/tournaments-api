<?php

namespace App\Modules\Championship;

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
        self::createFixtures($data['teams'], $champ);
        return true;
    }

    public function find(int $championship_id)
    {
        $championship = $this->model::query()
            ->where('id', $championship_id)
            ->first();

        return $championship;

    }

    /**
     * @throws Exception
     */
    public function findByName(string $name)
    {
        $name = str_replace('-', ' ', $name); // Converte o slug para o formato do campo 'name' no banco de dados
        $championship = $this->model::query()
            ->where('name', $name)
            ->first();

        if (!$championship) {
            throw new Exception('Campeonato não encontrado');
        }

        return $championship;
    }

    /**
     * @throws Exception
     */
    public
    function all()
    {
        $championship = $this->model::query()
            ->get();

        if (!$championship) throw new Exception('Nenhum campeonato encontrado');

        return $championship;
    }

    /**
     * @throws Exception
     */
    public
    function update($data, $championship_id): bool
    {
        $championship = $this->model::find((int)$championship_id);

        if (!$championship) throw new Exception('Campeonato não encontrado');

        return (bool)$championship->update($data);
    }

    /**
     * @throws Exception
     */
    public
    function delete(int $championship_id): bool
    {
        $championship = $this->model::find($championship_id);

        if (!$championship) throw new Exception('Campeonato não encontrado');

        return (bool)$championship->delete();
    }

    /**
     * @throws Exception
     */
    public
    function getFixtures(int $championship_id)
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

    private
    static function createFixtures(array $teams, Championship $champ)
    {
        $scheduler = new RoundRobinScheduler();
        $scheduler->setTeams($teams)
            ->shuffle()
            ->setRounds($champ->rounds);

        $schedule = $scheduler->build();

        $gameNumber = 1;

        foreach ($schedule as $roundNumber => $matches) {
            foreach ($matches as $match) {
                $data = [
                    'championship_id' => $champ->id,
                    'round_number' => $roundNumber,
                    'game_number' => $gameNumber,
                    'home_team_id' => $match[0],
                    'away_team_id' => $match[1],
                ];
                
                Fixture::create($data);
                $gameNumber++;
            }
        }
    }
}
