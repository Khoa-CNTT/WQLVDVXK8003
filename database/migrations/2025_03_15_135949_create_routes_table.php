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
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('start_location'); // Điểm xuất phát
            $table->string('end_location'); // Điểm đến
            $table->decimal('price', 10, 2); // Giá vé
            $table->time('departure_time'); // Thời gian khởi hành
            $table->time('arrival_time'); // Thời gian đến
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('routes');
    }
};
