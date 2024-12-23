<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('player_rates', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('fixture_id')->comment('id fixture');
            $table->unsignedBigInteger('player_id')->comment('id player');
            $table->float('rate');

            $table->foreign('player_id')->references('id')->on('players');
            $table->foreign('fixture_id')->references('id')->on('fixtures');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rates');
    }
};
