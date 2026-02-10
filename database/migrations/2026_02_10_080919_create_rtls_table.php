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
        Schema::create('rtls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kasus_id')->constrained('kasus')->cascadeOnDelete();
            $table->date('tanggal');
            $table->text('keterangan');
            $table->timestamps();

            $table->index(['kasus_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rtls');
    }
};
