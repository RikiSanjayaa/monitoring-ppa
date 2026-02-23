<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Hapus file lampiran kronologi yang tersimpan di storage
        $files = DB::table('kasus')
            ->whereNotNull('kronologi_kejadian_file')
            ->where('kronologi_kejadian_file', '!=', '')
            ->pluck('kronologi_kejadian_file');

        foreach ($files as $file) {
            Storage::disk('public')->delete($file);
        }

        // Hapus data kolom laporan_polisi (teks) terlebih dahulu
        DB::table('kasus')->update([
            'kronologi_kejadian_file' => null,
            'laporan_polisi' => null,
        ]);

        // Drop kolom dari tabel
        Schema::table('kasus', function (Blueprint $table) {
            $table->dropColumn(['kronologi_kejadian_file', 'laporan_polisi']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasus', function (Blueprint $table) {
            $table->string('kronologi_kejadian_file')->nullable()->after('kronologi_kejadian');
            $table->text('laporan_polisi')->nullable()->after('kronologi_kejadian_file');
        });
    }
};
