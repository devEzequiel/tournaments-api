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
    public function up()
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('championship_id'); //

            $table->unsignedBigInteger('first_place_id'); //champion
            $table->unsignedBigInteger('second_place_id'); //vice
            $table->unsignedBigInteger('third_place_id'); //bronze

            $table->unsignedBigInteger('best_player'); //player of the championship: biggest rate
            $table->unsignedBigInteger('golden_boot'); //most goals
            $table->unsignedBigInteger('playmaker'); //most assists
            $table->unsignedBigInteger('golden_glove'); //fewer goals conceded

            $table->foreign('championship_id')->references('id')->on('championships');

            $table->foreign('first_place_id')->references('id')->on('teams');
            $table->foreign('second_place_id')->references('id')->on('teams');
            $table->foreign('third_place_id')->references('id')->on('teams');

            $table->foreign('best_player')->references('id')->on('players');
            $table->foreign('golden_boot')->references('id')->on('players');
            $table->foreign('playmaker')->references('id')->on('players');
            $table->foreign('golden_glove')->references('id')->on('players');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
