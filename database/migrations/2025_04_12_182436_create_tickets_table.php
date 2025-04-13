// database/migrations/xxxx_xx_xx_create_tickets_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ticket_number')->unique();
            $table->foreignId('trip_id')->constrained();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('seat_id')->constrained();
            $table->integer('price');
            $table->string('passenger_name');
            $table->string('passenger_phone');
            $table->string('passenger_email')->nullable();
            $table->string('status')->default('pending'); // pending, confirmed, cancelled, refunded
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('tickets');
    }
};
