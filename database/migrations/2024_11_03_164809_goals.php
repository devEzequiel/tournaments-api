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
        Schema::create('fixtures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixture_id');
            $table->unsignedBigInteger('championship_id');
            $table->unsignedBigInteger('scorer_id');
            $table->unsignedBigInteger('assist_id');

            $table->foreign('championship_id')->references('id')->on('championships');
            $table->foreign('fixture_id')->references('id')->on('fixtures');
            $table->foreign('scorer_id')->references('id')->on('players');
            $table->foreign('assist_id')->references('id')->on('players');
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
