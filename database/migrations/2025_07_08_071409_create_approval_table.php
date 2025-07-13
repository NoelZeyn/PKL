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
        Schema::create('approval', function (Blueprint $table) {
            $table->id('id_approval');
            $table->unsignedBigInteger('id_request_fk');
            $table->unsignedBigInteger('id_admin_fk');
            $table->string('level_approval'); 
            $table->enum('status', ['approved', 'rejected', 'pending', 'purchasing', 'on_the_way','done']);
            $table->date('tanggal');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->foreign('id_request_fk')->references('id_request')->on('request');
            $table->foreign('id_admin_fk')->references('id')->on('admin');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval');
    }
};
