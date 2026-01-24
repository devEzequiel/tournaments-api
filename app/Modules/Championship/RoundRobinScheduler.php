<?php

namespace App\Modules\Championship;
class RoundRobinScheduler
{
    private array $teams = [];
    private int $rounds = 1;

    /**
     * Define os times participantes.
     */
    public function setTeams(array $teams): self
    {
        $this->teams = $teams;
        return $this;
    }

    /**
     * Embaralha os times de forma aleatória.
     */
    public function shuffle(): self
    {
        shuffle($this->teams);
        return $this;
    }

    /**
     * Define o número de rodadas.
     */
    public function setRounds(int $rounds): self
    {
        $this->rounds = $rounds;
        return $this;
    }

    /**
     * Gera os jogos conforme as regras especificadas:
     * - Cada par de times se enfrenta em todas as rodadas
     * - Nas rodadas pares, inverte mando de campo
     * - Na última rodada (se ímpar e rounds > 1), NÃO gera fixtures (será gerado depois baseado em resultados)
     */
    public function build(): array
    {
        $schedule = [];
        $teams = $this->teams;
        $numTeams = count($teams);
        
        // Gera todos os pares de times possíveis
        $allMatchups = [];
        for ($i = 0; $i < $numTeams; $i++) {
            for ($j = $i + 1; $j < $numTeams; $j++) {
                $allMatchups[] = [$teams[$i], $teams[$j]];
            }
        }
        
        // Embaralha os confrontos para aleatoriedade
        shuffle($allMatchups);
        
        // Para cada confronto, define aleatoriamente quem começa em casa
        $matchupHomeFirst = [];
        foreach ($allMatchups as $matchup) {
            // 50% de chance de inverter o mando inicial
            if (rand(0, 1) === 1) {
                $matchupHomeFirst[] = [$matchup[1], $matchup[0]]; // Time B joga em casa primeiro
            } else {
                $matchupHomeFirst[] = $matchup; // Time A joga em casa primeiro
            }
        }
        
        // Determina quantas rodadas serão geradas agora
        $roundsToGenerate = $this->rounds;
        
        // Se for número ímpar de rodadas E maior que 1, NÃO gera a última rodada
        if ($this->rounds % 2 === 1 && $this->rounds > 1) {
            $roundsToGenerate = $this->rounds - 1;
        }
        
        // Gera as rodadas
        for ($round = 1; $round <= $roundsToGenerate; $round++) {
            $roundMatches = [];
            
            foreach ($matchupHomeFirst as $match) {
                $teamA = $match[0];
                $teamB = $match[1];
                
                // Alterna mando de campo conforme a rodada
                if ($round % 2 === 1) {
                    // Rodada ímpar: mantém ordem original
                    $roundMatches[] = [$teamA, $teamB];
                } else {
                    // Rodada par: inverte mando
                    $roundMatches[] = [$teamB, $teamA];
                }
            }
            
            // Embaralha a ordem das partidas dentro da rodada
            shuffle($roundMatches);
            
            $schedule[$round] = $roundMatches;
        }
        
        return $schedule;
    }
    
    /**
     * Gera uma chave única para um confronto entre dois times
     */
    private function getMatchupKey($teamA, $teamB): string
    {
        $teams = [$teamA, $teamB];
        sort($teams);
        return implode('_vs_', $teams);
    }
}
