<?php

namespace App\Modules\Championship;
class RoundRobinScheduler
{
    private array $teams = [];
    private int $turns = 1;

    /**
     * Define os times participantes.
     */
    public function setTeams(array $teams): self
    {
        $this->teams = $teams;

        // O número de equipes deve ser par; caso contrário, adiciona um "bye" (folga).
        if (count($this->teams) % 2 !== 0) {
            $this->teams[] = null; // Representa a "bye" (time que fica de folga).
        }

        return $this;
    }

    /**
     * Embaralha os times de forma aleatória.
     */
    public function shuffle(): self
    {
        // Randomiza a ordem dos times
        shuffle($this->teams);
        return $this;
    }

    /**
     * Define o número de turnos (representados diretamente pelos rounds).
     */
    public function setRounds(int $rounds): self
    {
        $this->turns = $rounds; // Cada round será considerado um "turno completo".
        return $this;
    }

    /**
     * Gera os jogos no formato Round Robin com turnos.
     */
    public function build(): array
    {
        $schedule = [];
        $teams = $this->teams;
        $numTeams = count($teams);
        $numRoundsPerTurn = $numTeams - 1; // Um turno completo tem "N-1 rodadas" para N times.

        for ($turn = 1; $turn <= $this->turns; $turn++) {
            for ($round = 0; $round < $numRoundsPerTurn; $round++) {
                $matches = [];

                // Cria as partidas ("matches") da rodada.
                for ($i = 0; $i < $numTeams / 2; $i++) {
                    $home = $teams[$i];
                    $away = $teams[$numTeams - 1 - $i];

                    // Se existir "bye", pula a partida.
                    if ($home !== null && $away !== null) {
                        if ($turn % 2 === 0) {
                            // Turnos pares invertem "casa" e "fora".
                            $matches[] = [$away, $home];
                        } else {
                            // Turnos ímpares mantêm "casa" e "fora".
                            $matches[] = [$home, $away];
                        }
                    }
                }

                // Adiciona as partidas ao agendamento.
                $schedule[($turn - 1) * $numRoundsPerTurn + $round + 1] = $matches;

                // Rotaciona os times (mantendo o primeiro fixo).
                $last = array_pop($teams);
                array_splice($teams, 1, 0, [$last]);
            }
        }

        return $schedule;
    }
}
