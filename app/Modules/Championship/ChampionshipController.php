<?php

namespace App\Modules\Championship;

use App\Http\Controllers\Controller;
use App\Http\Requests\Championship\CreateChampionshipRequest;
use App\Http\Requests\Championship\UpdateChampionshipRequest;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Controller responsável pelas operações de Campeonato.
 * 
 * Gerencia requisições HTTP para:
 * - Listagem e visualização de campeonatos (Inertia)
 * - API RESTful para CRUD de campeonatos
 * - Consulta de fixtures por campeonato
 */
class ChampionshipController extends Controller
{
    /**
     * Construtor com injeção de dependência do serviço.
     * 
     * @param ChampionshipService $service Serviço de campeonatos
     */
    public function __construct(protected ChampionshipService $service)
    {
    }

    /**
     * Exibe a página de listagem de campeonatos.
     * 
     * Renderiza via Inertia.js a view Championships/Index
     * com todos os campeonatos disponíveis.
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        try {
            $championships = $this->service->all();

            return inertia('Championships/Index', [
                'championships' => $championships,
            ]);
        } catch (Exception $e) {
            return inertia('Error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Cria um novo campeonato.
     * 
     * Recebe dados validados e cria o campeonato com suas fixtures.
     * 
     * @param CreateChampionshipRequest $request Request validado
     * @return JsonResponse Resposta com status 201 ou erro
     */
    public function store(CreateChampionshipRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->create($data);

            return $this->responseCreated('Campeonato adicionado');
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    /**
     * Exibe a página de detalhes de um campeonato.
     * 
     * Busca o campeonato pelo nome (slug) e renderiza via Inertia.
     * 
     * @param string $name Nome do campeonato em formato slug
     * @return \Inertia\Response
     */
    public function show(string $name)
    {
        try {
            $championship = $this->service->findByName($name);

            return inertia('Championships/List', [
                'championship' => $championship,
            ]);
        } catch (Exception $e) {
            return inertia('Error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Retorna os detalhes de um campeonato via API.
     * 
     * @param int $id ID do campeonato
     * @return JsonResponse Dados do campeonato
     */
    public function detail(int $id)
    {
        try {
            $championship = $this->service->find($id);

            return $this->responseOk($championship);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    /**
     * Retorna as fixtures de um campeonato via API.
     * 
     * Lista todas as partidas ordenadas por rodada e número do jogo.
     * 
     * @param int $id ID do campeonato
     * @return JsonResponse Lista de fixtures
     */
    public function getFixtures(int $id): JsonResponse
    {
        try {
            $fixtures = $this->service->getFixtures($id);

            return $this->responseOk($fixtures);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    /**
     * Atualiza um campeonato existente.
     * 
     * @param UpdateChampionshipRequest $request Request validado
     * @param int $id ID do campeonato
     * @return JsonResponse Resposta com status 201 ou erro
     */
    public function update(UpdateChampionshipRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->update($data, $id);

            return $this->responseCreated('Campeonato atualizado');
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $this->service->delete($id);

            return $this->responseAccepted();
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
    
    public function generateFinalRound(int $id): JsonResponse
    {
        try {
            FinalRoundGeneratorService::generateFinalRound($id);
            
            return $this->responseCreated('Rodada final gerada com sucesso');
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
    
    public function getAwards(int $championship_id): JsonResponse
    {
        try {
            $award = \App\Models\Award::where('championship_id', $championship_id)
                ->with(['bestPlayer', 'goldenBoot', 'playmaker'])
                ->first();
            
            if (!$award) {
                return $this->responseOk([]);
            }
            
            $awards = [];
            
            // The Best (melhor jogador)
            if ($award->best_player && $award->bestPlayer) {
                $awards[] = [
                    'id' => $award->id . '_best',
                    'type' => 'the_best',
                    'player_name' => $award->bestPlayer->name,
                ];
            }
            
            // Artilheiro
            if ($award->golden_boot && $award->goldenBoot) {
                $awards[] = [
                    'id' => $award->id . '_scorer',
                    'type' => 'top_scorer',
                    'player_name' => $award->goldenBoot->name,
                ];
            }
            
            // Playmaker (melhor assistente)
            if ($award->playmaker && $award->playmaker) {
                $awards[] = [
                    'id' => $award->id . '_playmaker',
                    'type' => 'playmaker',
                    'player_name' => $award->playmaker->name,
                ];
            }
            
            return $this->responseOk($awards);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
