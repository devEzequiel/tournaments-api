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
        Schema::create('goals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('fixture_id')->nullable();
            $table->unsignedBigInteger('scorer_id')->nullable();
            $table->unsignedBigInteger('assist_id')->nullable();
            $table->boolean('own_goal')->default(false);

            $table->boolean('pk')->default(false);

            $table->foreign('fixture_id')->references('id')->on('fixtures')
            ->onDelete('cascade');
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
