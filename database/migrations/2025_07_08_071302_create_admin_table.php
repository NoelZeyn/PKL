<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('admin', function (Blueprint $table) {
            $table->id();
            $table->string('NID')->unique();
            $table->string('password');
            $table->unsignedBigInteger('id_penempatan_fk')->nullable();
            $table->unsignedBigInteger('id_bidang_fk')->nullable(); // Dibuat nullable agar role lain tidak error
            $table->enum('tingkatan_otoritas', [
                'user', 
                'user_review', 
                'admin', 
                'superadmin', 
                'anggaran', 
                'asman', 
                'manajer'
            ]);
            $table->enum('access', ['pending', 'active', 'inactive'])->default('pending');
            $table->timestamp('password_changed_at')->nullable();
            $table->timestamps();

            $table->foreign('id_penempatan_fk')->references('id')->on('penempatan')->onDelete('set null');
            $table->foreign('id_bidang_fk')->references('id')->on('bidang')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
