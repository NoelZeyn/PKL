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
        Schema::create('alat', function (Blueprint $table) {
            $table->unsignedBigInteger('id_kategori_fk');
            $table->id('id_alat');
            $table->string('nama_barang');
            $table->integer('stock_min')->nullable();
            $table->integer('stock_max')->nullable();
            $table->integer('stock')->nullable();
            $table->string('satuan');
            $table->integer('harga_satuan')->nullable();
            $table->integer('harga_estimasi')->nullable();
            $table->integer('order')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_kategori_fk')->references('id_kategori')->on('kategori_pengadaan');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alat');
    }
};
