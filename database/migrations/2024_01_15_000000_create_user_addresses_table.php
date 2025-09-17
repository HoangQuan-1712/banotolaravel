<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('user_addresses')) {
            Schema::create('user_addresses', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('name'); // Tên người nhận
                $table->string('phone'); // Số điện thoại
                $table->string('address_line_1'); // Địa chỉ chi tiết
                $table->string('address_line_2')->nullable(); // Địa chỉ bổ sung
                $table->string('city'); // Thành phố
                $table->string('district'); // Quận/Huyện
                $table->string('ward'); // Phường/Xã
                $table->string('postal_code')->nullable(); // Mã bưu điện
                $table->boolean('is_default')->default(false); // Địa chỉ mặc định
                $table->timestamps();
                
                // Index để tìm kiếm nhanh
                $table->index(['user_id', 'is_default']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
