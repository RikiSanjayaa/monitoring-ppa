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
            $table->dropColumn([
                'identitas_korban',
                'identitas_pelaku',
                'identitas_saksi',
            ]);

            $table->string('nama_pelaku')->nullable()->after('proses_pidana');
            $table->string('tempat_lahir_pelaku')->nullable()->after('nama_pelaku');
            $table->date('tanggal_lahir_pelaku')->nullable()->after('tempat_lahir_pelaku');
            $table->text('alamat_pelaku')->nullable()->after('tanggal_lahir_pelaku');
            $table->string('hp_pelaku')->nullable()->after('alamat_pelaku');

            $table->string('nama_saksi')->nullable()->after('hp_pelaku');
            $table->string('tempat_lahir_saksi')->nullable()->after('nama_saksi');
            $table->date('tanggal_lahir_saksi')->nullable()->after('tempat_lahir_saksi');
            $table->text('alamat_saksi')->nullable()->after('tanggal_lahir_saksi');
            $table->string('hp_saksi')->nullable()->after('alamat_saksi');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasus', function (Blueprint $table): void {
            $table->dropColumn([
                'nama_pelaku',
                'tempat_lahir_pelaku',
                'tanggal_lahir_pelaku',
                'alamat_pelaku',
                'hp_pelaku',
                'nama_saksi',
                'tempat_lahir_saksi',
                'tanggal_lahir_saksi',
                'alamat_saksi',
                'hp_saksi',
            ]);

            $table->text('identitas_korban')->nullable()->after('proses_pidana');
            $table->text('identitas_pelaku')->nullable()->after('identitas_korban');
            $table->text('identitas_saksi')->nullable()->after('identitas_pelaku');
        });
    }
};
