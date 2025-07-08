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
        Schema::create('pengadaan', function (Blueprint $table) {
            $table->id('id_pengadaan');
            $table->unsignedBigInteger('id_request_fk');
            $table->enum('status', ['pending', 'progress', 'completed', 'cancelled']);
            $table->date('tanggal_estimasi')->nullable();
            $table->date('tanggal_pengadaan')->nullable();
            $table->timestamps();

            $table->foreign('id_request_fk')->references('id_request')->on('request');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengadaan');
    }
};
