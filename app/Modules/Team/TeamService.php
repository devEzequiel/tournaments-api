<?php

namespace App\Modules\Team;

use App\Contracts\TeamContract;
use App\Models\Goal;
use App\Models\Player;
use App\Models\PlayerRate;
use App\Models\Team;
use App\Models\TeamPlayer;
use App\Services\BaseService;
use Exception;
use Illuminate\Support\Facades\DB;

class TeamService extends BaseService implements TeamContract
{
    public function __construct()
    {
        parent::__construct(new Team());
    }

    public function create($data): bool
    {
        return (bool)$this->model::create($data);
    }

    /**
     * @throws Exception
     */
    public function find(int $id)
    {
        $team = $this->model::query()
            ->where('id', $id)
            ->first();

        if (!$team) {
            throw new Exception('Time não encontrado');
        }

        return $team;
    }

    /**
     * @throws Exception
     */
    public function getCurrentPlayers(int $id)
    {
        $teamPlayers = TeamPlayer::query()
            ->where('team_id', $id) // Relaciona com o time pelo ID
            ->where('current_team', true) // Apenas jogadores atuais
            ->join('players', 'team_player.player_id', '=', 'players.id') // Junta com a tabela "players" para obter detalhes dos jogadores
            ->select('players.id as player_id', 'players.name as player_name') // Seleciona os dados necessários
            ->get();

        return $teamPlayers;
    }

    /**
     * @throws Exception
     */
    public function all()
    {
        $team = $this->model::query()
            ->with('players')
            ->get();

        if (!$team)
            throw new Exception('Nenhum time encontrado');

        return $team;
    }

    /**
     * @throws Exception
     */
    public function update($data, $id): bool
    {
        $team = $this->model::find((int)$id);

        if (!$team)
            throw new Exception('Time não encontrado');

        return (bool)$team->update($data);
    }

    /**
     * @throws Exception
     */
    public function delete($id): bool
    {
        $team = $this->model::find($id);

        if (!$team)
            throw new Exception('Time não encontrado');

        return (bool)$team->delete();
    }

    public function findByName(string $name)
    {
        $name = str_replace('-', ' ', $name);
        $team = $this->model::query()
            ->where('name', $name)
            ->select(
                'id',
                'name',
                'first_color',
                'second_color',
                'created_at'
            )
            ->first();

        if (!$team) {
            return null;
        }

        $players =  TeamPlayer::query()
            ->where('team_id', $team->id)
            ->where('current_team', true)
            ->first();

        if (empty($players)) {
            return $team;
        }

        $team->players = $this->getTeamStats($team->id, 'goals');


        return $team;
    }

    private function getTeamStats(int $teamId, ?string $orderBy = null): array
    {
        // Buscar jogadores atuais do time
        $players = DB::table('team_player')
            ->where('team_id', $teamId)
            ->where('current_team', true)
            ->join('players', 'team_player.player_id', '=', 'players.id') // Relacionar com nomes dos jogadores
            ->select('players.id as player_id', 'players.name as player_name')
            ->get();

        $playerIds = $players->pluck('player_id'); // Apenas os IDs dos jogadores

        // Contar os gols
        $goals = Goal::whereIn('scorer_id', $playerIds)
            ->select('scorer_id', DB::raw('COUNT(*) as total_goals'))
            ->groupBy('scorer_id')
            ->pluck('total_goals', 'scorer_id'); // Mapear por jogador ID

        // Contar as assistências
        $assists = Goal::whereIn('assist_id', $playerIds)
            ->select('assist_id', DB::raw('COUNT(*) as total_assists'))
            ->groupBy('assist_id')
            ->pluck('total_assists', 'assist_id'); // Mapear por jogador ID

        // Buscar taxas médias (rate) dos jogadores
        $rates = PlayerRate::whereIn('player_id', $playerIds)
            ->select('player_id', DB::raw('AVG(rate) as average_rate'))
            ->groupBy('player_id')
            ->pluck('average_rate', 'player_id'); // Mapear por jogador ID

        // Premiações individuais separadas por tipo
        $awards = DB::table('awards')
            ->whereIn('best_player', $playerIds)  // Melhor jogador
            ->orWhereIn('golden_boot', $playerIds) // Artilheiro
            ->orWhereIn('playmaker', $playerIds) // Melhor assistente
            ->select(
                DB::raw('COUNT(CASE WHEN best_player IN (' . implode(',', $playerIds->toArray()) . ') THEN 1 END) as best_player_awards'),
                DB::raw('COUNT(CASE WHEN golden_boot IN (' . implode(',', $playerIds->toArray()) . ') THEN 1 END) as golden_boot_awards'),
                DB::raw('COUNT(CASE WHEN playmaker IN (' . implode(',', $playerIds->toArray()) . ') THEN 1 END) as playmaker_awards')
            )
            ->first();

        // Quantidade de jogos registrados na tabela player_rates
        $gamesPlayed = PlayerRate::whereIn('player_id', $playerIds)
            ->select('player_id', DB::raw('COUNT(fixture_id) as games_played'))
            ->groupBy('player_id')
            ->pluck('games_played', 'player_id'); // Mapear por jogador ID

        // Combinar os dados de estatísticas
        $stats = [];
        foreach ($players as $player) {
            $playerId = $player->player_id;
            $stats[$playerId] = [
                'name' => $player->player_name,
                'goals' => $goals[$playerId] ?? 0,
                'assists' => $assists[$playerId] ?? 0,
                'games_played' => $gamesPlayed[$playerId] ?? 0,
                'average_rate' => $rates[$playerId] ?? 0,
                'best_player' => $awards->best_player_awards ?? 0,
                'golden_boot' => $awards->golden_boot_awards ?? 0,
                'playmaker' => $awards->playmaker_awards ?? 0,

            ];
        }

        // Ordenar caso o critério seja fornecido
        if ($orderBy) {
            $stats = collect($stats)->sortByDesc($orderBy)->toArray();
        }

        return $stats;
    }
}
