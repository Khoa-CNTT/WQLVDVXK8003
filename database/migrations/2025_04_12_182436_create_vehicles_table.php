// database/migrations/xxxx_xx_xx_create_vehicles_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('license_plate')->unique();
            $table->string('type'); // standard, vip, limousine
            $table->integer('capacity');
            $table->date('last_maintenance');
            $table->boolean('is_active')->default(true);
            $table->text('amenities')->nullable(); // JSON array of amenities
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicles');
    }
};