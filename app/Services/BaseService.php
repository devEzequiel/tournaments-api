<?php

namespace App\Services;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;

class BaseService
{
    public function __construct(protected BaseModel $model)
    {
    }
}
