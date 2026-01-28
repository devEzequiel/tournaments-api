<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->unsignedBigInteger('first_place')->nullable()->after('championship_id');
            $table->unsignedBigInteger('second_place')->nullable()->after('first_place');
            $table->unsignedBigInteger('third_place')->nullable()->after('second_place');
            $table->unsignedBigInteger('golden_glove')->nullable()->after('golden_boot');

            $table->foreign('first_place')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('second_place')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('third_place')->references('id')->on('teams')->onDelete('set null');
            $table->foreign('golden_glove')->references('id')->on('players')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('awards', function (Blueprint $table) {
            $table->dropForeign(['first_place']);
            $table->dropForeign(['second_place']);
            $table->dropForeign(['third_place']);
            $table->dropForeign(['golden_glove']);
            
            $table->dropColumn(['first_place', 'second_place', 'third_place', 'golden_glove']);
        });
    }
};
