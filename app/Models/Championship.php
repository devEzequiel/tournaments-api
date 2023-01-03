<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Championship extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
        'rounds',
        'playoffs'
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function teams(): HasMany
    {
        return $this->hasMany(Team::class);
    }

    public function fixture(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }
}
