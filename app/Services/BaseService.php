<?php

namespace App\Services;

use App\Models\ModelBase;
use Illuminate\Database\Eloquent\Model;

class BaseService
{
    public function __construct(protected Model $model)
    {
    }
}
