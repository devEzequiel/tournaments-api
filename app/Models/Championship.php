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

    public function fixtures(): HasMany
    {
        return $this->hasMany(Fixture::class);
    }

    public function getUnplayedFixtures()
    {
        return $this->fixtures()->where('is_played', false)->get();
    }

    public function getPlayedFixtures()
    {
        return $this->fixtures()->where('is_played', true)->get();
    }
}
