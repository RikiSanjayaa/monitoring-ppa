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
        DB::table('kasus')
            ->select([
                'id',
                'nama_korban',
                'tempat_lahir_korban',
                'tanggal_lahir_korban',
                'alamat_korban',
                'hp_korban',
                'nama_pelaku',
                'tempat_lahir_pelaku',
                'tanggal_lahir_pelaku',
                'alamat_pelaku',
                'hp_pelaku',
            ])
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                $now = now();
                $korbans = [];
                $pelakus = [];

                foreach ($rows as $row) {
                    $existingKorban = DB::table('kasus_korbans')
                        ->where('kasus_id', $row->id)
                        ->exists();

                    if (! $existingKorban && trim((string) $row->nama_korban) !== '') {
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

                    $existingPelaku = DB::table('kasus_pelakus')
                        ->where('kasus_id', $row->id)
                        ->exists();

                    if (! $existingPelaku && trim((string) $row->nama_pelaku) !== '') {
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

        Schema::table('kasus', function (Blueprint $table): void {
            $table->dropColumn([
                'nama_korban',
                'tempat_lahir_korban',
                'tanggal_lahir_korban',
                'alamat_korban',
                'hp_korban',
                'nama_pelaku',
                'tempat_lahir_pelaku',
                'tanggal_lahir_pelaku',
                'alamat_pelaku',
                'hp_pelaku',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kasus', function (Blueprint $table): void {
            $table->string('nama_korban')->nullable()->after('tanggal_lp');
            $table->string('tempat_lahir_korban')->nullable()->after('nama_korban');
            $table->date('tanggal_lahir_korban')->nullable()->after('tempat_lahir_korban');
            $table->text('alamat_korban')->nullable()->after('tanggal_lahir_korban');
            $table->string('hp_korban')->nullable()->after('alamat_korban');

            $table->string('nama_pelaku')->nullable()->after('proses_pidana');
            $table->string('tempat_lahir_pelaku')->nullable()->after('nama_pelaku');
            $table->date('tanggal_lahir_pelaku')->nullable()->after('tempat_lahir_pelaku');
            $table->text('alamat_pelaku')->nullable()->after('tanggal_lahir_pelaku');
            $table->string('hp_pelaku')->nullable()->after('alamat_pelaku');
        });
    }
};
