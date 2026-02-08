<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Classe base abstrata para todos os models do sistema.
 * 
 * Fornece funcionalidades comuns como:
 * - Controle de appends (atributos computados)
 * - Registro automático de usuário que criou/atualizou o registro
 * 
 * Todos os models do domínio devem estender esta classe.
 */
abstract class BaseModel extends Model
{
    /**
     * Flag para controlar se os appends devem ser incluídos.
     * 
     * Quando true, os atributos computados (appends) não serão
     * incluídos na serialização do model.
     * 
     * @var bool
     */
    public static bool $withoutAppends = false;

    /**
     * Scope para desabilitar os appends em uma query.
     * 
     * Uso: Model::withoutAppends()->get()
     * 
     * @param mixed $query Query builder
     * @return mixed
     */
    public function scopeWithoutAppends($query)
    {
        self::$withoutAppends = true;
        return $query;
    }

    /**
     * Boot do model - registra eventos de criação e atualização.
     * 
     * Automaticamente preenche created_by_user_id e updated_by_user_id
     * com o ID do usuário autenticado, se esses campos existirem no fillable.
     * 
     * @return void
     */
    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (in_array('created_by_user_id', $model->getFillable())) {
                $model->created_by_user_id = auth()->id() ?: $model->created_by_user_id;
            }
        });
        static::updating(function ($model) {
            if (in_array('updated_by_user_id', $model->getFillable())) {
                $model->updated_by_user_id = auth()->id() ?: $model->updated_by_user_id;
            }
        });
    }

    /**
     * Retorna os appends que devem ser incluídos na serialização.
     * 
     * Se $withoutAppends for true, retorna array vazio.
     * 
     * @return array Lista de appends
     */
    protected function getArrayableAppends(): array
    {
        if (self::$withoutAppends) {
            return [];
        }
        return parent::getArrayableAppends();
    }
}
