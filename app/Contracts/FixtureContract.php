<?php

namespace App\Contracts;

/**
 * Interface que define o contrato para serviços de Partidas (Fixtures).
 * 
 * Estabelece os métodos obrigatórios para manipulação de partidas,
 * incluindo consultas e registro de resultados.
 */
interface FixtureContract
{
    /**
     * Retorna as partidas ainda não realizadas de um campeonato.
     * 
     * @param int $championship_id ID do campeonato
     * @return mixed Lista de fixtures pendentes
     */
    public function getUnplayedFixtures(int $championship_id);

    /**
     * Retorna todas as partidas de um campeonato.
     * 
     * @param int $championship_id ID do campeonato
     * @return mixed Lista completa de fixtures
     */
    public function getAllFixtures(int $championship_id);

    /**
     * Retorna as partidas com informações básicas dos times.
     * 
     * Inclui nomes e cores dos times para exibição no frontend.
     * 
     * @param int $championship_id ID do campeonato
     * @return array Fixtures com dados dos times
     */
    public function getFixturesWithBasicInfo(int $championship_id);

    /**
     * Registra o resultado de uma partida.
     * 
     * @param array $data Dados do resultado (fixture_id, home_goals, away_goals, goals[])
     * @return mixed Resultado da operação
     */
    public function playMatch(array $data);
}
