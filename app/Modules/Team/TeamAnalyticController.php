<?php

namespace App\Modules\Team;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TeamAnalyticController extends Controller
{

    public function __construct(protected TeamAnalyticService $service)
    {
    }

    /**
     * Fetches current player data for a specified team ID.
     *
     * This function aims to retrieve the data of current team players using the given
     * team ID. Upon success, it returns a successful response with the relevant
     * players data. If an exception is encountered, it manages the error by returning
     * an unprocessable entity response along with the exception message.
     *
     * @param int $id The team ID for which current player data is to be fetched.
     * @return JsonResponse The response containing current player data or an error message.
     */
    public function currentPlayersData(int $id)
    {
        try {
            $playersData = $this->service->getCurrentPlayersData($id);

            return $this->responseOk($playersData);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }

    /**
     * Retrieves player data for a given team ID.
     *
     * This function attempts to fetch all the team players data using the provided
     * player ID. If successful, it returns a successful response with the
     * players data. In case of an exception, it handles the error by returning
     * an unprocessable entity response with the exception message.
     *
     * @param int $id The ID of the team whose data is to be retrieved.
     * @return JsonResponse The response containing player data or an error message.
     */
    public function playersData(int $id)
    {
        try {
            $playersData = $this->service->getPlayersData($id);

            return $this->responseOk($playersData);
        } catch (\Exception $e) {
            return $this->responseUnprocessableEntity($e->getMessage());
        }
    }
}
