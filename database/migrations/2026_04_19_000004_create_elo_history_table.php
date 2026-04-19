<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('elo_history', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->uuid('player_id');
            $table->foreign('player_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->uuid('match_id');
            $table->foreign('match_id')->references('id')->on('matches')->cascadeOnDelete();
            $table->integer('elo_before')->unsigned();
            $table->integer('elo_after')->unsigned();
            $table->integer('elo_change');
            $table->timestampTz('recorded_at')->useCurrent();

            $table->index(['player_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('elo_history');
    }
};
