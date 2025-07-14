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
        Schema::create('alat_penempatan', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('id_alat_fk');
            $table->unsignedBigInteger('id_penempatan_fk');

            $table->integer('stock')->nullable()->default(0);
            $table->integer('stock_min')->nullable()->default(0);
            $table->integer('stock_max')->nullable()->default(0);

            $table->timestamps();

            // Foreign keys
            $table->foreign('id_alat_fk')->references('id_alat')->on('alat')->onDelete('cascade');
            $table->foreign('id_penempatan_fk')->references('id')->on('penempatan')->onDelete('cascade');

            $table->unique(['id_alat_fk', 'id_penempatan_fk']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alat_penempatan');
    }
};
