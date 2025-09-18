<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('customer_tiers', function (Blueprint $table) {
            $table->id();
            $table->enum('level', ['bronze', 'silver', 'gold', 'platinum']);
            $table->string('name'); // Bronze Member, Silver VIP, Gold Elite, Platinum Exclusive
            $table->decimal('min_spent', 12, 2); // Ngưỡng USD cho ngành ô tô
            $table->text('benefits'); // Mô tả quyền lợi chi tiết
            $table->string('color')->default('#6c757d'); // Màu hiển thị
            $table->integer('priority_support')->default(0); // Độ ưu tiên hỗ trợ
            $table->decimal('discount_percentage', 5, 2)->default(0); // % giảm giá dịch vụ
            $table->timestamps();
        });

        // Thêm cột vào bảng users
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tier_id')->nullable()->constrained('customer_tiers');
            $table->decimal('lifetime_spent', 12, 2)->default(0); // Tổng chi tiêu
            $table->integer('total_cars_bought')->default(0); // Số xe đã mua
            $table->timestamp('tier_updated_at')->nullable(); // Lần cuối cập nhật tier
        });
    }
    
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tier_id');
            $table->dropColumn(['lifetime_spent', 'total_cars_bought', 'tier_updated_at']);
        });
        Schema::dropIfExists('customer_tiers');
    }
};
