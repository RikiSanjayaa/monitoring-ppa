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
            $table->text('kronologi_kejadian')->nullable()->after('dokumen_status');
            $table->string('kronologi_kejadian_file')->nullable()->after('kronologi_kejadian');
            $table->text('laporan_polisi')->nullable()->after('kronologi_kejadian_file');
            $table->string('laporan_polisi_file')->nullable()->after('laporan_polisi');
            $table->string('tindak_pidana_pasal')->nullable()->after('laporan_polisi_file');
            $table->string('hubungan_pelaku_dengan_korban')->nullable()->after('tindak_pidana_pasal');
            $table->text('proses_pidana')->nullable()->after('hubungan_pelaku_dengan_korban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasus', function (Blueprint $table): void {
            $table->dropColumn([
                'kronologi_kejadian',
                'kronologi_kejadian_file',
                'laporan_polisi',
                'laporan_polisi_file',
                'tindak_pidana_pasal',
                'hubungan_pelaku_dengan_korban',
                'proses_pidana',
            ]);
        });
    }
};
