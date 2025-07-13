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
        Schema::create('pengaturan_pengajuan', function (Blueprint $table) {
            $table->id();
            $table->dateTime('mulai')->nullable();
            $table->dateTime('berakhir')->nullable();
            $table->boolean('is_active')->default(true); // bisa aktif/tidak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pengaturan_pengajuan');
    }
};
