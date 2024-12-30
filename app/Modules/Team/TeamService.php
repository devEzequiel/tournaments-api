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
//        dd($team->toArray());
        return $team;
    }

    private function getTeamStats(int $teamId, ?string $orderBy = null): array
    {
        // Buscar jogadores do time com período (joined_at e left_at)
        $players = DB::table('team_player')
            ->where('team_id', $teamId)
            ->join('players', 'team_player.player_id', '=', 'players.id') // Relacionar com nomes dos jogadores
            ->select(
                'players.id as player_id',
                'players.name as player_name',
                'team_player.joined_at',
                'team_player.left_at'
            )
            ->get();

        $playerIds = $players->pluck('player_id'); // IDs dos jogadores

        // Mapear os períodos dos jogadores
        $playerPeriods = $players->keyBy('player_id')->map(function ($player) {
            return [
                'joined_at' => $player->joined_at,
                'left_at' => $player->left_at,
            ];
        });

        // Filtrar gols pelo período em que o jogador esteve no time
        $goals = Goal::whereIn('scorer_id', $playerIds)
            ->join('fixtures', 'goals.fixture_id', '=', 'fixtures.id') // Relacionar com fixtures.played_at
            ->where(function ($query) use ($playerPeriods) {
                foreach ($playerPeriods as $playerId => $period) {
                    $query->orWhere(function ($query) use ($playerId, $period) {
                        $query->where('scorer_id', $playerId)
                            ->where('fixtures.played_at', '>=', $period['joined_at'])
                            ->when($period['left_at'], function ($query, $leftAt) {
                                $query->where('fixtures.played_at', '<=', $leftAt);
                            });
                    });
                }
            })
            ->select('scorer_id', DB::raw('COUNT(*) as total_goals'))
            ->groupBy('scorer_id')
            ->pluck('total_goals', 'scorer_id');

        // Filtrar assistências pelo período do jogador no time
        $assists = Goal::whereIn('assist_id', $playerIds)
            ->join('fixtures', 'goals.fixture_id', '=', 'fixtures.id')
            ->where(function ($query) use ($playerPeriods) {
                foreach ($playerPeriods as $playerId => $period) {
                    $query->orWhere(function ($query) use ($playerId, $period) {
                        $query->where('assist_id', $playerId)
                            ->where('fixtures.played_at', '>=', $period['joined_at'])
                            ->when($period['left_at'], function ($query, $leftAt) {
                                $query->where('fixtures.played_at', '<=', $leftAt);
                            });
                    });
                }
            })
            ->select('assist_id', DB::raw('COUNT(*) as total_assists'))
            ->groupBy('assist_id')
            ->pluck('total_assists', 'assist_id');

        // Buscar taxas médias (rate) dos jogadores dentro do período
        $rates = PlayerRate::whereIn('player_id', $playerIds)
            ->join('fixtures', 'player_rates.fixture_id', '=', 'fixtures.id') // Relacionar fixtures.played_at
            ->where(function ($query) use ($playerPeriods) {
                foreach ($playerPeriods as $playerId => $period) {
                    $query->orWhere(function ($query) use ($playerId, $period) {
                        $query->where('player_id', $playerId)
                            ->where('fixtures.played_at', '>=', $period['joined_at'])
                            ->when($period['left_at'], function ($query, $leftAt) {
                                $query->where('fixtures.played_at', '<=', $leftAt);
                            });
                    });
                }
            })
            ->select('player_id', DB::raw('AVG(rate) as average_rate'))
            ->groupBy('player_id')
            ->pluck('average_rate', 'player_id');

        // Contar jogos jogados no período
        $gamesPlayed = PlayerRate::whereIn('player_id', $playerIds)
            ->join('fixtures', 'player_rates.fixture_id', '=', 'fixtures.id')
            ->where(function ($query) use ($playerPeriods) {
                foreach ($playerPeriods as $playerId => $period) {
                    $query->orWhere(function ($query) use ($playerId, $period) {
                        $query->where('player_id', $playerId)
                            ->where('fixtures.played_at', '>=', $period['joined_at'])
                            ->when($period['left_at'], function ($query, $leftAt) {
                                $query->where('fixtures.played_at', '<=', $leftAt);
                            });
                    });
                }
            })
            ->select('player_id', DB::raw('COUNT(fixture_id) as games_played'))
            ->groupBy('player_id')
            ->pluck('games_played', 'player_id');

        // Premiações individuais separadas por tipo (relacionadas a jogadores ativos no período)
        $awards = DB::table('awards')
            ->whereIn('best_player', $playerIds)
            ->orWhereIn('golden_boot', $playerIds)
            ->orWhereIn('playmaker', $playerIds)
            ->select(
                DB::raw('COUNT(CASE WHEN best_player IN (' . implode(',', $playerIds->toArray()) . ') THEN 1 END) as best_player_awards'),
                DB::raw('COUNT(CASE WHEN golden_boot IN (' . implode(',', $playerIds->toArray()) . ') THEN 1 END) as golden_boot_awards'),
                DB::raw('COUNT(CASE WHEN playmaker IN (' . implode(',', $playerIds->toArray()) . ') THEN 1 END) as playmaker_awards')
            )
            ->first();

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
