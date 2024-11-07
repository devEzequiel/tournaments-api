<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamPlayer extends Model
{
    use HasFactory;

    protected $table = "team_player";

    public $timestamps = false;
    protected $fillable = [
        'player_id',
        'team_id',
        'current_team',
        'joined_at',
        'left_at'
    ];




}
