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
        Schema::create('pengajuan_alat', function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('id_kategori_fk');
            $table->unsignedBigInteger('id_user_fk');  // siapa yang mengajukan
            $table->string('nama_barang');
            $table->string('satuan');
            $table->integer('harga_estimasi')->nullable();
            $table->string('keterangan')->nullable();
            $table->string('catatan')->nullable();
            $table->string('status_by')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamps();

            $table->foreign('id_kategori_fk')->references('id_kategori')->on('kategori_pengadaan');
            $table->foreign('id_user_fk')->references('id')->on('admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengajuan_atk');
    }
};
