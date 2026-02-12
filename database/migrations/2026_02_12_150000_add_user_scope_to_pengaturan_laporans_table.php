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
        Schema::table('pengaturan_laporans', function (Blueprint $table): void {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->unique('user_id');
        });

        Schema::table('pengaturan_laporans', function (Blueprint $table): void {
            $table->foreignId('satker_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengaturan_laporans', function (Blueprint $table): void {
            $table->dropUnique(['user_id']);
            $table->dropConstrainedForeignId('user_id');
        });

        Schema::table('pengaturan_laporans', function (Blueprint $table): void {
            $table->foreignId('satker_id')->nullable(false)->change();
        });
    }
};
