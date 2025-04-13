// database/migrations/xxxx_xx_xx_create_routes_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('routes', function (Blueprint $table) {
            $table->id();
            $table->string('departure_location');
            $table->string('arrival_location');
            $table->integer('distance');
            $table->integer('base_price');
            $table->integer('estimated_time'); // minutes
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('routes');
    }
};
