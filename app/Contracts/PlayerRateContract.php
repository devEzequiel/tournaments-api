<?php

namespace App\Contracts;

/**
 * Interface que define o contrato para serviços de Avaliação de Jogador.
 * 
 * Estabelece os métodos obrigatórios para registrar avaliações
 * de desempenho dos jogadores após as partidas.
 */
interface PlayerRateContract
{
    /**
     * Cria uma nova avaliação para um jogador.
     * 
     * @param array $data Dados da avaliação (fixture_id, player_id, rate)
     * @return bool Sucesso da operação
     */
    public function create(array $data): bool;
}
