<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSchoolsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Data Sekolah
            $table->string('nama_sekolah');
            $table->enum('status_sekolah', ['negeri', 'swasta']);
            $table->text('alamat_sekolah')->nullable();
            $table->string('npsn', 20)->nullable()->unique();
            $table->string('kecamatan')->nullable();
            $table->string('kabupaten')->nullable();
            $table->string('provinsi')->nullable();

            // Data Kepala Sekolah
            $table->string('kepala_nama');
            $table->string('kepala_nip', 30)->nullable();
            $table->string('kepala_sk')->nullable();

            // Data Bendahara
            $table->string('bendahara_nama');
            $table->string('bendahara_nip', 30)->nullable();
            $table->string('bendahara_sk')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('schools');
    }
}
