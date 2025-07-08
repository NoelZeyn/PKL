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
        Schema::create('data_diri', function (Blueprint $table) {
            $table->unsignedBigInteger('id_admin_user_fk')->primary();
            $table->string('nama_lengkap');
            $table->string('jabatan');
            $table->boolean('bpjs');
            $table->timestamps();

            $table->foreign('id_admin_user_fk')->references('id')->on('admin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data_diri');
    }
};
