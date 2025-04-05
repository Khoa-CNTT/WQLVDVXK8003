<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bus_id')->constrained()->onDelete('cascade'); // ID xe
            $table->foreignId('route_id')->constrained()->onDelete('cascade'); // ID tuyến
            $table->string('customer_name'); // Tên khách hàng
            $table->string('customer_phone'); // Số điện thoại
            $table->integer('seat_number'); // Số ghế
            $table->enum('status', ['pending', 'paid', 'canceled'])->default('pending'); // Trạng thái thanh toán
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};

