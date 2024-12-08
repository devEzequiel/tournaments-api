<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    use HasFactory;

    protected $fillable = [
        'championship_id',
        'first_place',
        'second_place',
        'third_place',
        'best_player',
        'golden_boot',
        'golden_glove',
        'playmaker',
    ];

    public function getBestPlayer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Player::class, 'best_player', 'id');
    }

    public function getGoldenBoot(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Player::class, 'golden_boot', 'id');
    }

    public function getGoldenGlove(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Player::class, 'golden_glove', 'id');
    }

    public function getPlaymaker(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Player::class, 'playmaker', 'id');
    }

    public function getChampionship()
    {
        return $this->belongsTo(Championship::class);
    }

    public function getFirstPlace()
    {
        return $this->belongsTo(Team::class, 'first_place', 'id');
    }

    public function getSecondPlace()
    {
        return $this->belongsTo(Team::class, 'second_place', 'id');
    }

    public function getThirdPlace()
    {
        return $this->belongsTo(Team::class, 'third_place', 'id');
    }
}
