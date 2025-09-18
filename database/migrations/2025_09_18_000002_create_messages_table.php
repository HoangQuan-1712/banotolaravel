<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_id')->constrained('chats')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('sender_is_admin')->default(false);
            $table->text('body')->nullable(); // có thể null nếu chỉ là file
            $table->boolean('is_read')->default(false);
            $table->timestamps();

            $table->index(['chat_id','created_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('messages');
    }
};
