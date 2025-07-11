<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('history_atk', function (Blueprint $table) {
            $table->id('id_history');
            $table->unsignedBigInteger('id_admin_fk');
            $table->unsignedBigInteger('id_alat_fk')->nullable();
            $table->text('jenis_aksi');
            $table->text('deskripsi')->nullable();
            $table->integer('jumlah')->nullable();
            $table->date('tanggal');
            $table->timestamps();

            $table->foreign('id_admin_fk')->references('id')->on('admin');
            $table->foreign('id_alat_fk')->references('id_alat')->on('alat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('history_atk');
    }
};
