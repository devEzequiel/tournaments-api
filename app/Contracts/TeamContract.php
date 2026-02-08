<?php

namespace App\Contracts;

/**
 * Interface que define o contrato para serviços de Time.
 * 
 * Estabelece os métodos obrigatórios para manipulação de times,
 * garantindo consistência entre diferentes implementações.
 */
interface TeamContract
{
    /**
     * Busca um time pelo ID.
     * 
     * @param int $id ID do time
     * @return mixed Time encontrado
     * @throws \Exception Quando não encontrado
     */
    public function find (int $id);

    /**
     * Cria um novo time.
     * 
     * @param mixed $data Dados do time (name, colors)
     * @return bool Sucesso da operação
     */
    public function create ($data);

    /**
     * Retorna todos os times cadastrados.
     * 
     * @return mixed Coleção de times
     */
    public function all();

    /**
     * Atualiza um time existente.
     * 
     * @param mixed $data Dados para atualização
     * @param mixed $id ID do time
     * @return bool Sucesso da operação
     */
    public function update ($data, $id);

    /**
     * Remove um time.
     * 
     * @param mixed $id ID do time
     * @return bool Sucesso da operação
     */
    public function delete ($id);
}
