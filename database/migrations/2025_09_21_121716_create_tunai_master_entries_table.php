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
        Schema::create('tunai_master_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal')->nullable();
            $table->string('kode_kegiatan', 50)->nullable();
            $table->string('kode_rekening', 50)->nullable();
            $table->string('no_bukti', 50)->nullable();
            $table->text('uraian')->nullable();
            $table->decimal('penerimaan', 15, 3)->nullable()->default(0);
            $table->decimal('pengeluaran', 15, 3)->nullable()->default(0);
            $table->decimal('saldo', 15, 3)->nullable()->default(0);
            $table->timestamps();

            $table->index(['user_id', 'tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tunai_master_entries');
    }
};
