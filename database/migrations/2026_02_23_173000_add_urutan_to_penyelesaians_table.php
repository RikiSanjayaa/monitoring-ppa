<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penyelesaians', function (Blueprint $table) {
            $table->unsignedInteger('urutan')->nullable()->after('nama');
        });

        $orderedItems = DB::table('penyelesaians')
            ->select(['id'])
            ->orderBy('id')
            ->get();

        foreach ($orderedItems as $index => $item) {
            DB::table('penyelesaians')
                ->where('id', $item->id)
                ->update(['urutan' => $index + 1]);
        }
    }

    public function down(): void
    {
        Schema::table('penyelesaians', function (Blueprint $table) {
            $table->dropColumn('urutan');
        });
    }
};
