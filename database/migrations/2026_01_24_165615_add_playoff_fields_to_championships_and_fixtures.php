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
        // Adicionar campos de playoff em championships
        Schema::table('championships', function (Blueprint $table) {
            $table->enum('playoff_type', ['final', 'semifinal'])->nullable()->after('playoffs');
        });

        // Adicionar campos de playoff em fixtures
        Schema::table('fixtures', function (Blueprint $table) {
            $table->boolean('is_playoff')->default(false)->after('is_played');
            $table->string('playoff_stage')->nullable()->after('is_playoff'); // 'semifinal', 'final'
            $table->integer('playoff_game_number')->nullable()->after('playoff_stage'); // 1, 2, 3
            $table->boolean('decided_by_penalty')->default(false)->after('playoff_game_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('championships', function (Blueprint $table) {
            $table->dropColumn('playoff_type');
        });

        Schema::table('fixtures', function (Blueprint $table) {
            $table->dropColumn(['is_playoff', 'playoff_stage', 'playoff_game_number', 'decided_by_penalty']);
        });
    }
};
