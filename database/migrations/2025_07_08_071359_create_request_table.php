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
        Schema::create('request', function (Blueprint $table) {
            $table->id('id_request');
            $table->unsignedBigInteger('id_inventoris_fk');
            $table->unsignedBigInteger('id_users_fk');
            $table->date('tanggal_permintaan');
            $table->enum('status', ['draft', 'waiting_approval_1', 'waiting_approval_2', 'waiting_approval_3', 'approved','rejected','purchasing', 'on_the_way', 'done',]);
            $table->integer('jumlah');
            $table->integer('total');
            $table->string('status_by')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('id_inventoris_fk')->references('id_alat')->on('alat');
            $table->foreign('id_users_fk')->references('id')->on('admin');
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request');
    }
};
