<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('home_team_id');
            $table->unsignedBigInteger('away_team_id');
            $table->unsignedBigInteger('championship_id');

            $table->integer('round_number');
            $table->integer('game_number');
            $table->enum('playoff_round', ['1', '2', '3', '4'])->nullable(); //1 final, 2 semi, 3 terceiro lugar, 4 quartas
            $table->timestamps();

            $table->foreign('home_team_id')->references('id')
                ->on('teams');
            $table->foreign('away_team_id')->references('id')
                ->on('teams');
            $table->foreign('championship_id')->references('id')
                ->on('championships');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('fixtures');
    }
};
