<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreviousPlayerTeam extends Model
{
    use HasFactory;

    protected $table = 'previous_players_team';

    protected $fillable = [
        'player_id',
        'team_id'
    ];
}
