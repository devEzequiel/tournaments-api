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
            $table->unsignedBigInteger('championship_id');

            $table->unsignedBigInteger('best_player'); //player of the championship: biggest rate
            $table->unsignedBigInteger('golden_boot'); //most goals
            $table->unsignedBigInteger('playmaker'); //most assists

            $table->foreign('championship_id')->references('id')->on('championships')
            ->onDelete('cascade');

            $table->foreign('best_player')->references('id')->on('players');
            $table->foreign('golden_boot')->references('id')->on('players');
            $table->foreign('playmaker')->references('id')->on('players');
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
