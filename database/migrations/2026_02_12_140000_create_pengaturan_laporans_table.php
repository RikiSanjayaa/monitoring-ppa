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
        Schema::create('pengaturan_laporans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('satker_id')->constrained('satkers')->cascadeOnDelete();
            $table->string('kop_baris_1');
            $table->string('kop_baris_2');
            $table->string('kop_baris_3');
            $table->string('judul_utama');
            $table->string('judul_rekap');
            $table->string('ttd_baris_1');
            $table->string('ttd_baris_2');
            $table->string('ttd_nama');
            $table->string('ttd_pangkat_nrp');
            $table->timestamps();

            $table->unique('satker_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_laporans');
    }
};
