<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Championship extends BaseModel
{

    public $timestamps = false;
    protected $fillable = [
        'name',
        'description',
        'rounds',
        'playoffs',
        'playoff_rounds',
        'started_at',
        'finished_at',
    ];

    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }
}
