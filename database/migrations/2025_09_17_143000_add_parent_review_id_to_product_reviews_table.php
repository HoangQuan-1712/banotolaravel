<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_review_id')->nullable()->after('status');
            $table->foreign('parent_review_id')->references('id')->on('product_reviews')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('product_reviews', function (Blueprint $table) {
            $table->dropForeign(['parent_review_id']);
            $table->dropColumn('parent_review_id');
        });
    }
};
