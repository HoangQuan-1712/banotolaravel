<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('type', ['tiered_choice', 'random_gift', 'vip_tier', 'discount', 'service_voucher']);
            $table->decimal('value', 12, 2)->nullable(); // USD value for car business
            $table->string('name'); // Tên voucher: "Premium Car Care Package"
            $table->text('description'); // Mô tả chi tiết
            $table->string('group_code')->nullable(); // Nhóm cho Tiered Choice
            $table->decimal('min_order_value', 12, 2)->nullable(); // Giá trị đơn hàng tối thiểu
            $table->decimal('max_order_value', 12, 2)->nullable(); // Giá trị đơn hàng tối đa
            $table->json('applicable_categories')->nullable(); // Loại xe áp dụng
            $table->integer('usage_limit')->nullable(); // Giới hạn sử dụng tổng
            $table->integer('usage_limit_per_user')->default(1); // Mỗi khách chỉ dùng 1 lần
            $table->integer('used_count')->default(0);
            $table->integer('stock')->nullable(); // Số lượng có sẵn
            $table->integer('weight')->default(1); // Trọng số cho random
            $table->enum('tier_level', ['bronze', 'silver', 'gold', 'platinum'])->nullable(); // VIP tier
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->boolean('active')->default(true);
            $table->json('metadata')->nullable(); // Thông tin bổ sung
            $table->timestamps();
        });
    }
    
    public function down(): void {
        Schema::dropIfExists('vouchers');
    }
};
