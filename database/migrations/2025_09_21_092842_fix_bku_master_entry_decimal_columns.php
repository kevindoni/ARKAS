<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bku_master_entries', function (Blueprint $table) {
            // Convert decimal columns to integer (values are in thousands)
            $table->bigInteger('penerimaan')->change();
            $table->bigInteger('pengeluaran')->change(); 
            $table->bigInteger('saldo')->change();
        });
        
        // Round any existing decimal values to integers
        DB::statement('UPDATE bku_master_entries SET penerimaan = ROUND(penerimaan), pengeluaran = ROUND(pengeluaran), saldo = ROUND(saldo)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bku_master_entries', function (Blueprint $table) {
            // Revert back to decimal if needed
            $table->decimal('penerimaan', 15, 2)->change();
            $table->decimal('pengeluaran', 15, 2)->change();
            $table->decimal('saldo', 15, 2)->change();
        });
    }
};
