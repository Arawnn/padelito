<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->timestampTz('match_date')->nullable();
            $table->string('court_name', 100)->nullable();
            $table->string('match_type', 20)->default('friendly');
            $table->string('match_format', 20)->default('doubles');
            $table->string('status', 20)->default('pending');

            $table->uuid('team_a_player1_id');
            $table->foreign('team_a_player1_id')->references('id')->on('profiles');
            $table->uuid('team_a_player2_id')->nullable();
            $table->foreign('team_a_player2_id')->references('id')->on('profiles')->nullOnDelete();
            $table->uuid('team_b_player1_id')->nullable();
            $table->foreign('team_b_player1_id')->references('id')->on('profiles')->nullOnDelete();
            $table->uuid('team_b_player2_id')->nullable();
            $table->foreign('team_b_player2_id')->references('id')->on('profiles')->nullOnDelete();

            $table->json('sets_detail')->nullable();
            $table->unsignedTinyInteger('sets_to_win')->default(2);
            $table->integer('team_a_score')->nullable();
            $table->integer('team_b_score')->nullable();

            $table->text('notes')->nullable();

            $table->integer('team_a_elo_before')->nullable();
            $table->integer('team_b_elo_before')->nullable();
            $table->integer('elo_change')->nullable();

            $table->uuid('created_by');
            $table->foreign('created_by')->references('id')->on('profiles');

            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
