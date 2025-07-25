<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('penempatan', function (Blueprint $table) {
            $table->id();
            $table->string('nama_penempatan')->nullable();
            $table->unsignedBigInteger('id_bidang_fk');

            $table->foreign('id_bidang_fk')->references('id')->on('bidang');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penempatan');
    }
};
