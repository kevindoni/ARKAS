<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bku_master_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal')->nullable();
            $table->string('kode_kegiatan', 50)->nullable();
            $table->string('kode_rekening', 50)->nullable();
            $table->string('no_bukti', 50)->nullable();
            $table->text('uraian')->nullable();
            $table->decimal('penerimaan', 20, 2)->nullable()->default(0);
            $table->decimal('pengeluaran', 20, 2)->nullable()->default(0);
            $table->decimal('saldo', 20, 2)->nullable()->default(0);
            $table->timestamps();

            $table->index(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bku_master_entries');
    }
};
