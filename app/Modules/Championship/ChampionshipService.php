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
        $scheduler->setTeams($teams) // Define os times
        ->shuffle() // Embaralha os times
        ->setRounds($champ->rounds); // Define o número de turnos (rounds)

        $schedule = $scheduler->build(); // Cria o cronograma (rounds com matches)

        $numTeams = count($teams);
        $numRoundsPerTurn = $numTeams - 1; // Número de rounds por turno completo
        $turn = 1; // Começa no turno 1


        $gameNumber = 1; // Inicializa o número de jogo dentro do turno

        foreach ($schedule as $round => $matches) {
            // Calcula o turno correspondente com base no número do round
            if (($round - 1) % $numRoundsPerTurn === 0 && $round !== 1) {
                $turn++; // Incrementa o turno quando completamos um turno inteiro
            }

            $data = [];
            $data['championship_id'] = $champ->id;
            $data['round_number'] = $turn; // Salva o número do turno (turn)

            foreach ($matches as $match) {
                $data['game_number'] = $gameNumber;      // Número do jogo
                $data['home_team_id'] = $match[0];       // Time da casa
                $data['away_team_id'] = $match[1];       // Time visitante
                Fixture::create($data);                 // Salva no banco de dados

                $gameNumber++; // Incrementa o número do jogo
            }
        }
    }
}
