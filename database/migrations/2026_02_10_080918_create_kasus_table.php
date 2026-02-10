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
        Schema::create('kasus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('satker_id')->constrained('satkers')->cascadeOnDelete();
            $table->string('nomor_lp');
            $table->date('tanggal_lp');
            $table->string('nama_korban');
            $table->string('tempat_lahir_korban')->nullable();
            $table->date('tanggal_lahir_korban')->nullable();
            $table->text('alamat_korban')->nullable();
            $table->string('hp_korban')->nullable();
            $table->foreignId('perkara_id')->constrained('perkaras')->restrictOnDelete();
            $table->enum('dokumen_status', ['lidik', 'sidik']);
            $table->foreignId('penyelesaian_id')->nullable()->constrained('penyelesaians')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['satker_id', 'nomor_lp']);
            $table->index(['satker_id', 'tanggal_lp']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasus');
    }
};
