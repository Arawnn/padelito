<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_invitations', function (Blueprint $table) {
            $table->dropUnique(['match_id', 'team', 'position']);
            $table->dropColumn('position');
            $table->string('type', 20)->after('team');
        });
    }

    public function down(): void
    {
        Schema::table('match_invitations', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->unsignedTinyInteger('position')->after('team');
            $table->unique(['match_id', 'team', 'position']);
        });
    }
};
