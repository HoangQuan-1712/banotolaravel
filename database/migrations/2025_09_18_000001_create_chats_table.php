<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('chats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();   // chủ chat (khách)
            $table->foreignId('assigned_admin_id')->nullable()->constrained('users')->nullOnDelete(); // admin đang xử lý
            $table->enum('status', ['open','closed'])->default('open');
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index(['status','last_message_at']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('chats');
    }
};
