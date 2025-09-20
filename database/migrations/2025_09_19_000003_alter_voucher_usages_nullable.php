<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Safely alter without requiring doctrine/dbal
        // 1) Drop FK
        DB::statement('ALTER TABLE voucher_usages DROP FOREIGN KEY voucher_usages_order_id_foreign');
        // 2) Make columns nullable
        DB::statement('ALTER TABLE voucher_usages MODIFY order_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE voucher_usages MODIFY used_at TIMESTAMP NULL');
        // 3) Recreate FK with ON DELETE CASCADE
        DB::statement('ALTER TABLE voucher_usages ADD CONSTRAINT voucher_usages_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');
    }

    public function down(): void
    {
        // Revert to NOT NULL (may fail if null values present)
        DB::statement('ALTER TABLE voucher_usages DROP FOREIGN KEY voucher_usages_order_id_foreign');
        DB::statement('UPDATE voucher_usages SET order_id = 0 WHERE order_id IS NULL');
        DB::statement('ALTER TABLE voucher_usages MODIFY order_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE voucher_usages MODIFY used_at TIMESTAMP NOT NULL');
        DB::statement('ALTER TABLE voucher_usages ADD CONSTRAINT voucher_usages_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE');
    }
};
