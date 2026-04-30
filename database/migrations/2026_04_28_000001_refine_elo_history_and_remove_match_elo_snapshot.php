<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elo_history', function (Blueprint $table) {
            $table->string('team', 1)->after('match_id');
            $table->boolean('won')->after('team');
            $table->unique(['player_id', 'match_id']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn(['team_a_elo_before', 'team_b_elo_before', 'elo_change']);
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->integer('team_a_elo_before')->nullable();
            $table->integer('team_b_elo_before')->nullable();
            $table->integer('elo_change')->nullable();
        });

        Schema::table('elo_history', function (Blueprint $table) {
            $table->dropUnique(['player_id', 'match_id']);
            $table->dropColumn(['team', 'won']);
        });
    }
};
