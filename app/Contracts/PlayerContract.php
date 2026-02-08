<?php

namespace App\Contracts;

/**
 * Interface que define o contrato para serviços de Jogador.
 * 
 * Estabelece os métodos obrigatórios para manipulação de jogadores,
 * garantindo consistência entre diferentes implementações.
 */
interface PlayerContract
{
    /**
     * Busca um jogador pelo ID.
     * 
     * @param int $id ID do jogador
     * @return mixed Jogador encontrado com suas estatísticas
     * @throws \Exception Quando não encontrado
     */
    public function find (int $id);

    /**
     * Cria um novo jogador e o associa a um time.
     * 
     * @param mixed $data Dados do jogador (name, team_id)
     * @return bool Sucesso da operação
     */
    public function create ($data);

    /**
     * Retorna todos os jogadores com suas estatísticas.
     * 
     * @return mixed Coleção de jogadores
     */
    public function all();

    /**
     * Atualiza os dados de um jogador.
     * 
     * @param mixed $data Dados para atualização
     * @param mixed $id ID do jogador
     * @return bool Sucesso da operação
     */
    public function update ($data, $id);

    /**
     * Remove um jogador.
     * 
     * @param mixed $id ID do jogador
     * @return bool Sucesso da operação
     */
    public function delete ($id);
}
