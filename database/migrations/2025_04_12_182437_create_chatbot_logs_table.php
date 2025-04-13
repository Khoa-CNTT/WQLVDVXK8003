<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chatbot_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained();
            $table->text('user_query');
            $table->text('bot_response');
            $table->string('session_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('chatbot_logs');
    }
};
