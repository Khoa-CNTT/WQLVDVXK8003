// database/migrations/xxxx_xx_xx_create_seats_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('seats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained();
            $table->string('seat_number');
            $table->string('position'); // window, aisle, etc.
            $table->string('type')->default('standard'); // standard, vip, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['vehicle_id', 'seat_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('seats');
    }
};
