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
        Schema::table('bku_master_entries', function (Blueprint $table) {
            // Revert back to decimal for precision, but keep accurate values
            $table->decimal('penerimaan', 15, 3)->change(); // 3 decimal places for precision
            $table->decimal('pengeluaran', 15, 3)->change();
            $table->decimal('saldo', 15, 3)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bku_master_entries', function (Blueprint $table) {
            $table->bigInteger('penerimaan')->change();
            $table->bigInteger('pengeluaran')->change(); 
            $table->bigInteger('saldo')->change();
        });
    }
};
