<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_invitations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('match_id');
            $table->foreign('match_id')->references('id')->on('matches')->cascadeOnDelete();
            $table->uuid('invitee_id');
            $table->foreign('invitee_id')->references('id')->on('profiles')->cascadeOnDelete();
            $table->string('type', 10);
            $table->string('status', 20)->default('pending');
            $table->timestamp('invited_at')->useCurrent();
            $table->timestamp('responded_at')->nullable();

            $table->unique(['match_id', 'invitee_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_invitations');
    }
};
