<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerRate extends Model
{
    use HasFactory;

    protected $table = 'player_rates';
    public $timestamps = false;
    protected $fillable = [
        'fixture_id',
        'player_id',
        'rate'
    ];

    public function player()
    {
        $this->belongsTo(Player::class);
    }

    public function fixture()
    {
        $this->belongsTo(Fixture::class);
    }

}
