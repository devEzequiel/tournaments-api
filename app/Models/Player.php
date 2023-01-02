<?php

namespace App\Models;

class Player extends BaseModel
{
    protected $fillable = [
        'name',
        'team_id'
    ];

    public function team(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
