<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Goal extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'fixture_id',
        'scorer_id',
        'assist_id',
        'pk',
        'own_goal'
    ];
}
