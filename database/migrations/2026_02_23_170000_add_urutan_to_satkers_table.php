<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('satkers', function (Blueprint $table) {
            $table->unsignedInteger('urutan')->nullable()->after('kode');
        });

        $orderByKode = [
            'SUBDIT-1' => 1,
            'SUBDIT-2' => 2,
            'SUBDIT-3' => 3,
            'POLRES-MATARAM' => 4,
            'POLRES-LBAR' => 5,
            'POLRES-LUTARA' => 6,
            'POLRES-LTENGAH' => 7,
            'POLRES-LTIMUR' => 8,
            'POLRES-SUMBAWA-BARAT' => 9,
            'POLRES-SUMBAWA' => 10,
            'POLRES-DOMPU' => 11,
            'POLRES-BIMA' => 12,
            'POLRES-BIMAKOTA' => 13,
        ];

        foreach ($orderByKode as $kode => $urutan) {
            DB::table('satkers')->where('kode', $kode)->update(['urutan' => $urutan]);
        }
    }

    public function down(): void
    {
        Schema::table('satkers', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
