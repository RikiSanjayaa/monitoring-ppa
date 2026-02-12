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
        Schema::table('kasus', function (Blueprint $table): void {
            $table->text('identitas_korban')->nullable()->after('proses_pidana');
            $table->text('identitas_pelaku')->nullable()->after('identitas_korban');
            $table->text('identitas_saksi')->nullable()->after('identitas_pelaku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasus', function (Blueprint $table): void {
            $table->dropColumn([
                'identitas_korban',
                'identitas_pelaku',
                'identitas_saksi',
            ]);
        });
    }
};
