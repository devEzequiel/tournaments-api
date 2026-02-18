<?php

namespace App\Modules\Team;

use App\Http\Controllers\Controller;
use App\Http\Requests\Team\CreateTeamRequest;
use App\Http\Requests\Team\UpdateTeamRequest;
use Exception;
use Illuminate\Http\JsonResponse;

/**
 * Controller responsável pelas operações de Time.
 * 
 * Gerencia requisições HTTP para:
 * - Listagem e visualização de times (Inertia + API)
 * - CRUD completo de times
 * - Consulta de jogadores por time
 */
class TeamController extends Controller
{
    /**
     * Construtor com injeção de dependência do serviço.
     * 
     * @param TeamService $service Serviço de times
     */
    public function __construct(protected TeamService $service)
    {
    }

    /**
     * Exibe a página de listagem de times.
     * 
     * Renderiza via Inertia.js a view Teams/Index.
     * 
     * @return \Inertia\Response
     */
    public function index()
    {
        try {
            $data = $this->service->all();

            return inertia('Teams/Index', [
                'teams' => $data,
            ]);
        } catch (Exception $e) {
            return inertia('Error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Retorna a lista de times via API.
     * 
     * @return JsonResponse Lista de times
     */
    public function list()
    {
        try {
            $data = $this->service->all();

            return $this->responseOk($data);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    /**
     * Cria um novo time.
     * 
     * @param CreateTeamRequest $request Request validado
     * @return JsonResponse Resposta com status 201 ou erro
     */
    public function store(CreateTeamRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->create($data);

            return $this->responseCreated('Time adicionado');
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    /**
     * Exibe a página de detalhes de um time.
     * 
     * @param string $name Nome do time em formato slug
     * @return \Inertia\Response
     */
    public function show(string $name)
    {
        try {
            $team = $this->service->findByName($name);

            return inertia('Teams/List', [
                'team' => $team,
            ]);
        } catch (Exception $e) {
            return inertia('Error', [
                'message' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Retorna os detalhes de um time via API.
     * 
     * @param int $id ID do time
     * @return JsonResponse Dados do time
     */
    public function detail(int $id): JsonResponse
    {
        try {
            $team = $this->service->find($id);

            return $this->responseOk($team);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    /**
     * Retorna os jogadores atuais de um time.
     * 
     * @param int $id ID do time
     * @return JsonResponse Lista de jogadores
     */
    public function currentPlayers(int $id): JsonResponse
    {
        try {
            $team = $this->service->getCurrentPlayers($id);

            return $this->responseOk($team);
        } catch (Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    /**
     * Atualiza um time existente.
     * 
     * @param UpdateTeamRequest $request Request validado
     * @param int $id ID do time
     * @return JsonResponse Resposta com status 201 ou erro
     */
    public function update(UpdateTeamRequest $request, int $id): JsonResponse
    {
        try {
            $data = $request->validated();

            $this->service->update($data, $id);

            return $this->responseCreated('Time atualizado');
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
}
