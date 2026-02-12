<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kasus_korbans', function (Blueprint $table): void {
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

        Schema::create('kasus_pelakus', function (Blueprint $table): void {
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

        DB::table('kasus')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                $now = now();
                $korbans = [];
                $pelakus = [];

                foreach ($rows as $row) {
                    if ((string) $row->nama_korban !== '') {
                        $korbans[] = [
                            'kasus_id' => $row->id,
                            'nama' => $row->nama_korban,
                            'tempat_lahir' => $row->tempat_lahir_korban,
                            'tanggal_lahir' => $row->tanggal_lahir_korban,
                            'alamat' => $row->alamat_korban,
                            'hp' => $row->hp_korban,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }

                    if ((string) $row->nama_pelaku !== '') {
                        $pelakus[] = [
                            'kasus_id' => $row->id,
                            'nama' => $row->nama_pelaku,
                            'tempat_lahir' => $row->tempat_lahir_pelaku,
                            'tanggal_lahir' => $row->tanggal_lahir_pelaku,
                            'alamat' => $row->alamat_pelaku,
                            'hp' => $row->hp_pelaku,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }

                if ($korbans !== []) {
                    DB::table('kasus_korbans')->insert($korbans);
                }

                if ($pelakus !== []) {
                    DB::table('kasus_pelakus')->insert($pelakus);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kasus_pelakus');
        Schema::dropIfExists('kasus_korbans');
    }
};
