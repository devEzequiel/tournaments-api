<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller as BaseController;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Controller base do sistema.
 * 
 * Fornece métodos helper para respostas HTTP padronizadas,
 * facilitando a criação de APIs RESTful consistentes.
 * 
 * Todos os controllers devem estender esta classe.
 */
class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param array $data
     * @param string $message
     * @return JsonResponse
     */
    public function responseOk($data, $message = 'success'): JsonResponse
    {
        return response()->json(['message' => $message, 'data' => $data], Response::HTTP_OK);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function responseCreated(string $message = ''): JsonResponse
    {
        return response()->json($message ? ['message' => $message] : null, Response::HTTP_CREATED);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function responseAccepted($message = 'success'): JsonResponse
    {
        return response()->json(['message' => $message], ResponseAlias::HTTP_ACCEPTED);
    }

    /**
     * @return JsonResponse
     */
    public function responseUnauthorized(): JsonResponse
    {
        return response()->json(null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @return JsonResponse
     */
    public function responseForbidden(): JsonResponse
    {
        return response()->json(null, Response::HTTP_FORBIDDEN);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function responseNotFound(string $message = ''): JsonResponse
    {
        return response()->json($message ? ['message' => $message] : null, Response::HTTP_NOT_FOUND);
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function responseUnprocessableEntity(string $message = 'error'): JsonResponse
    {
        return response()->json(
            $message ? ['message' => $message] : null,
            Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }

    /**
     * @param string $message
     * @return JsonResponse
     */
    public function responseInternalServerError(string $message = 'error'): JsonResponse
    {
        return response()->json(
            $message ? ['message' => $message] : null,
            Response::HTTP_INTERNAL_SERVER_ERROR
        );
    }
}
