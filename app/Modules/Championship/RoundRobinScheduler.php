<?php

namespace App\Modules\Championship;

/**
 * Gerador de calendário de jogos no formato Round Robin.
 * 
 * O algoritmo Round Robin garante que todos os times joguem
 * entre si o número de vezes especificado (rodadas/turnos).
 * 
 * Características:
 * - Alternância de mando de campo entre rodadas pares e ímpares
 * - Randomização dos confrontos e ordem dos jogos
 * - Suporte a múltiplas rodadas (turnos e returno)
 * 
 * Uso:
 * ```php
 * $scheduler = new RoundRobinScheduler();
 * $schedule = $scheduler
 *     ->setTeams([1, 2, 3, 4])
 *     ->shuffle()
 *     ->setRounds(2)
 *     ->build();
 * ```
 */
class RoundRobinScheduler
{
    /**
     * Lista de IDs dos times participantes.
     * 
     * @var array
     */
    private array $teams = [];

    /**
     * Número de rodadas/turnos do campeonato.
     * 
     * @var int
     */
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
     * Gera o calendário completo de jogos.
     * 
     * Regras:
     * - Cada par de times se enfrenta em todas as rodadas
     * - Nas rodadas pares, inverte o mando de campo
     * - Na última rodada (ímpar e rounds > 1), NÃO gera fixtures
     *   (será gerado depois baseado em resultados para playoffs)
     * 
     * @return array Array associativo [rodada => [[home_id, away_id], ...]]
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
     * Gera uma chave única para um confronto entre dois times.
     * 
     * A chave é ordenada para garantir que A vs B = B vs A.
     * 
     * @param int $teamA ID do primeiro time
     * @param int $teamB ID do segundo time
     * @return string Chave no formato "id_vs_id"
     */
    private function getMatchupKey($teamA, $teamB): string
    {
        $teams = [$teamA, $teamB];
        sort($teams);
        return implode('_vs_', $teams);
    }
}
