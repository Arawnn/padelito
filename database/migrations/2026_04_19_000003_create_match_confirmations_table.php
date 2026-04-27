<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_confirmations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('match_id');
            $table->foreign('match_id')->references('id')->on('matches')->cascadeOnDelete();
            $table->uuid('player_id');
            $table->foreign('player_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->timestamp('confirmed_at')->useCurrent();

            $table->unique(['match_id', 'player_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_confirmations');
    }
};
