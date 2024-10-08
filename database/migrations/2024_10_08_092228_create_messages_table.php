<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained('conversations')->onDelete('cascade');
            $table->enum('sender', ['farmer', 'system']);
            $table->enum('responded_by', ['system', 'expert'])->nullable();
            $table->foreignId('expert_id')->nullable()->constrained('users')->onDelete('cascade'); // Nullable foreign key for expert (if any)
            $table->text('message');
            $table->enum('message_type', ['text', 'image', 'video'])->default('text');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('messages');
    }
}
