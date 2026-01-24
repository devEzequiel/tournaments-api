<?php

namespace App\Modules\Championship;

use App\Models\Championship;
use App\Models\Fixture;
use Illuminate\Support\Facades\DB;

class FinalRoundGeneratorService
{
    /**
     * Gera a última rodada do campeonato baseado nos resultados das rodadas anteriores
     * Deve ser chamado quando todas as rodadas anteriores forem jogadas
     */
    public static function generateFinalRound(int $championshipId): bool
    {
        $championship = Championship::find($championshipId);
        
        if (!$championship) {
            throw new \Exception('Campeonato não encontrado');
        }
        
        if ($championship->rounds % 2 === 0 || $championship->rounds === 1) {
            throw new \Exception('Este campeonato não precisa de geração de rodada final');
        }
        
        $finalRound = $championship->rounds;
        
        $existingFinalRound = Fixture::where('championship_id', $championshipId)
            ->where('round_number', $finalRound)
            ->exists();
            
        if ($existingFinalRound) {
            throw new \Exception('A rodada final já foi gerada');
        }
        
        $previousRoundsPlayed = Fixture::where('championship_id', $championshipId)
            ->where('round_number', '<', $finalRound)
            ->where('is_played', false)
            ->count();
            
        if ($previousRoundsPlayed > 0) {
            throw new \Exception('Ainda existem partidas não jogadas nas rodadas anteriores');
        }
        
        $teams = Fixture::where('championship_id', $championshipId)
            ->select('home_team_id')
            ->distinct()
            ->pluck('home_team_id')
            ->toArray();
        
        $matchups = [];
        for ($i = 0; $i < count($teams); $i++) {
            for ($j = $i + 1; $j < count($teams); $j++) {
                $matchups[] = [$teams[$i], $teams[$j]];
            }
        }
        
        $finalMatches = [];
        foreach ($matchups as $matchup) {
            $teamA = $matchup[0];
            $teamB = $matchup[1];
            
            $previousMatches = Fixture::where('championship_id', $championshipId)
                ->where('round_number', '<', $finalRound)
                ->where(function ($query) use ($teamA, $teamB) {
                    $query->where(function ($q) use ($teamA, $teamB) {
                        $q->where('home_team_id', $teamA)
                          ->where('away_team_id', $teamB);
                    })->orWhere(function ($q) use ($teamA, $teamB) {
                        $q->where('home_team_id', $teamB)
                          ->where('away_team_id', $teamA);
                    });
                })
                ->get();
            
            $teamAWins = 0;
            $teamBWins = 0;
            $teamAGoals = 0;
            $teamBGoals = 0;
            $teamAAwayGoals = 0;
            $teamBAwayGoals = 0;
            
            foreach ($previousMatches as $match) {
                if ($match->home_team_id === $teamA) {
                    $teamAGoals += $match->home_goals;
                    $teamBGoals += $match->away_goals;
                    $teamBAwayGoals += $match->away_goals;
                    
                    if ($match->home_goals > $match->away_goals) {
                        $teamAWins++;
                    } elseif ($match->home_goals < $match->away_goals) {
                        $teamBWins++;
                    }
                } else {
                    $teamBGoals += $match->home_goals;
                    $teamAGoals += $match->away_goals;
                    $teamAAwayGoals += $match->away_goals;
                    
                    if ($match->home_goals > $match->away_goals) {
                        $teamBWins++;
                    } elseif ($match->home_goals < $match->away_goals) {
                        $teamAWins++;
                    }
                }
            }
            
            if ($teamAWins > $teamBWins) {
                $finalMatches[] = [$teamA, $teamB];
            } elseif ($teamBWins > $teamAWins) {
                $finalMatches[] = [$teamB, $teamA]; // Team B joga em casa
            } elseif ($teamAGoals > $teamBGoals) {
                $finalMatches[] = [$teamA, $teamB];
            } elseif ($teamBGoals > $teamAGoals) {
                $finalMatches[] = [$teamB, $teamA];
            } elseif ($teamAAwayGoals > $teamBAwayGoals) {
                $finalMatches[] = [$teamA, $teamB];
            } elseif ($teamBAwayGoals > $teamAAwayGoals) {
                $finalMatches[] = [$teamB, $teamA];
            } else {
                $finalMatches[] = rand(0, 1) === 1 ? [$teamB, $teamA] : [$teamA, $teamB];
            }
        }
        
        shuffle($finalMatches);
        
        $lastGameNumber = Fixture::where('championship_id', $championshipId)
            ->max('game_number');
        
        $gameNumber = $lastGameNumber + 1;
        
        foreach ($finalMatches as $match) {
            Fixture::create([
                'championship_id' => $championshipId,
                'round_number' => $finalRound,
                'game_number' => $gameNumber,
                'home_team_id' => $match[0],
                'away_team_id' => $match[1],
            ]);
            
            $gameNumber++;
        }
        
        return true;
    }
}
