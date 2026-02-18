<?php

namespace App\Services;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Classe base abstrata para todos os serviços do sistema.
 * 
 * Fornece uma estrutura comum para serviços que operam sobre models,
 * injetando o model como dependência.
 * 
 * Padrão: Service Layer Pattern
 * Responsabilidade: Lógica de negócio entre Controller e Model
 */
class BaseService
{
    /**
     * Construtor do serviço.
     * 
     * @param BaseModel $model Model que o serviço irá manipular
     */
    public function __construct(protected BaseModel $model)
    {
    }
}
