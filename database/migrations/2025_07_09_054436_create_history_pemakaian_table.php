<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHistoryPemakaianTable extends Migration
{
    public function up()
    {
        Schema::create('history_pemakaian', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_alat');
            $table->unsignedBigInteger('id_user');
            $table->integer('jumlah');
            $table->text('keterangan')->nullable();
            $table->timestamp('tanggal_pemakaian')->useCurrent();
            $table->timestamps();

            $table->foreign('id_alat')->references('id_alat')->on('alat')->onDelete('cascade');
            $table->foreign('id_user')->references('id')->on('admin')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('history_pemakaian');
    }
}
