<?php

namespace App\Modules\Championship;

use App\Contracts\ChampionshipContract;
use App\Models\Championship;
use App\Models\Fixture;
use App\Services\BaseService;
use Exception;

/**
 * Serviço responsável pela lógica de negócio de Campeonatos.
 * 
 * Gerencia operações CRUD de campeonatos, incluindo:
 * - Criação de campeonatos com geração automática de fixtures
 * - Consulta e listagem de campeonatos
 * - Atualização e exclusão (com cascade para dados relacionados)
 */
class ChampionshipService extends BaseService implements ChampionshipContract
{
    /**
     * Relacionamentos a serem carregados por padrão.
     * 
     * @var array
     */
    protected array $with = ['teams', 'fixtures'];

    /**
     * Construtor do serviço.
     * Inicializa com o model Championship.
     */
    public function __construct()
    {
        parent::__construct(new Championship());
    }

    /**
     * Cria um novo campeonato e gera automaticamente as fixtures.
     * 
     * Utiliza o RoundRobinScheduler para criar o calendário de jogos
     * baseado no número de rodadas configurado.
     * 
     * @param array $data Dados do campeonato (name, rounds, teams[], etc)
     * @return bool Sucesso da operação
     * @throws Exception Em caso de erro na criação
     */
    public function create($data): bool
    {
        $data['started_at'] = now();
        $champ = $this->model::create($data);
        self::createFixtures($data['teams'], $champ);
        return true;
    }

    /**
     * Busca um campeonato pelo ID.
     * 
     * @param int $championship_id ID do campeonato
     * @return Championship|null Campeonato encontrado ou null
     */
    public function find(int $championship_id)
    {
        $championship = $this->model::query()
            ->where('id', $championship_id)
            ->first();

        return $championship;

    }

    /**
     * Busca um campeonato pelo nome (formato slug).
     * 
     * Converte hífens para espaços para match com o banco.
     * Exemplo: "copa-do-mundo" -> "copa do mundo"
     * 
     * @param string $name Nome do campeonato em formato slug
     * @return Championship Campeonato encontrado
     * @throws Exception Quando o campeonato não é encontrado
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
     * Retorna todos os campeonatos com seus prêmios.
     * 
     * Inclui o relacionamento com awards e o time campeão
     * para exibição na listagem.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Lista de campeonatos
     * @throws Exception Quando nenhum campeonato é encontrado
     */
    public
    function all()
    {
        $championship = $this->model::query()
            ->with(['awards.getFirstPlace']) // Inclui o relacionamento com awards e o time campeão
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
     * Remove um campeonato e todos os dados relacionados.
     * 
     * Operação em cascade:
     * 1. Deleta gols das partidas
     * 2. Deleta avaliações de jogadores
     * 3. Deleta premiações
     * 4. Deleta partidas (fixtures)
     * 5. Deleta o campeonato
     * 
     * @param int $championship_id ID do campeonato
     * @return bool Sucesso da operação
     * @throws Exception Quando o campeonato não é encontrado
     */
    public
    function delete(int $championship_id): bool
    {
        $championship = $this->model::find($championship_id);

        if (!$championship) throw new Exception('Campeonato não encontrado');

        // Deletar todos os gols das partidas deste campeonato
        $fixtureIds = Fixture::where('championship_id', $championship_id)->pluck('id');
        \App\Models\Goal::whereIn('fixture_id', $fixtureIds)->delete();
        
        // Deletar todas as avaliações de jogadores das partidas deste campeonato
        \App\Models\PlayerRate::whereIn('fixture_id', $fixtureIds)->delete();
        
        // Deletar todos os prêmios deste campeonato
        \App\Models\Award::where('championship_id', $championship_id)->delete();
        
        // Deletar todas as partidas (fixtures)
        Fixture::where('championship_id', $championship_id)->delete();

        // Deletar o campeonato
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
