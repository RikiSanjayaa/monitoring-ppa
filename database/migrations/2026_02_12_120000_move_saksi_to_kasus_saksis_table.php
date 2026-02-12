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
        Schema::create('kasus_saksis', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('kasus_id')->constrained('kasus')->cascadeOnDelete();
            $table->string('nama');
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->text('alamat')->nullable();
            $table->string('hp')->nullable();
            $table->timestamps();

            $table->index(['kasus_id', 'nama']);
        });

        Schema::table('kasus', function (Blueprint $table): void {
            $table->dropColumn([
                'nama_saksi',
                'tempat_lahir_saksi',
                'tanggal_lahir_saksi',
                'alamat_saksi',
                'hp_saksi',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasus', function (Blueprint $table): void {
            $table->string('nama_saksi')->nullable()->after('hp_pelaku');
            $table->string('tempat_lahir_saksi')->nullable()->after('nama_saksi');
            $table->date('tanggal_lahir_saksi')->nullable()->after('tempat_lahir_saksi');
            $table->text('alamat_saksi')->nullable()->after('tanggal_lahir_saksi');
            $table->string('hp_saksi')->nullable()->after('alamat_saksi');
        });

        Schema::dropIfExists('kasus_saksis');
    }
};
