<?php

namespace App\Contracts;

/**
 * Interface que define o contrato para serviços de Campeonato.
 * 
 * Estabelece os métodos obrigatórios para manipulação de campeonatos,
 * garantindo consistência entre diferentes implementações.
 */
interface ChampionshipContract
{
    /**
     * Busca um campeonato pelo ID.
     * 
     * @param int $id ID do campeonato
     * @return mixed Campeonato encontrado ou null
     */
    public function find (int $id);

    /**
     * Busca um campeonato pelo nome.
     * 
     * @param string $name Nome do campeonato
     * @return mixed Campeonato encontrado
     * @throws \Exception Quando não encontrado
     */
    public function findByName (string $name);

    /**
     * Cria um novo campeonato.
     * 
     * @param array $data Dados do campeonato (name, rounds, teams, etc)
     * @return bool Sucesso da operação
     */
    public function create (array $data);

    /**
     * Retorna todos os campeonatos.
     * 
     * @return mixed Coleção de campeonatos
     */
    public function all();

    /**
     * Retorna as partidas de um campeonato.
     * 
     * @param int $championshipId ID do campeonato
     * @return mixed Lista de fixtures
     */
    public function getFixtures(int $championshipId);

    /**
     * Atualiza um campeonato existente.
     * 
     * @param int $data Dados para atualização
     * @param int $championship_id ID do campeonato
     * @return bool Sucesso da operação
     */
    public function update (int $data, int $championship_id);

    /**
     * Remove um campeonato e todos os dados relacionados.
     * 
     * @param int $championship_id ID do campeonato
     * @return bool Sucesso da operação
     */
    public function delete (int $championship_id);

}
